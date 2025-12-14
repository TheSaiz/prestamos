<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$chat_id     = intval($_POST["chat_id"] ?? 0);
$question_id = intval($_POST["question_id"] ?? 0);
$answer      = trim($_POST["answer"] ?? "");
$option_id   = intval($_POST["option_id"] ?? 0);

// opcionales
$cuil   = trim($_POST["cuil"] ?? "");
$nombre = trim($_POST["nombre"] ?? "");

if ($chat_id <= 0 || $question_id <= 0 || $answer === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parámetros incompletos"]);
    exit;
}

try {
    // 1) Buscar chat + estado
    $stmt = $pdo->prepare("
        SELECT id, cliente_id, estado 
        FROM chats 
        WHERE id = ?
    ");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    /**
     * 🔥 CORRECCIÓN CLAVE
     * Si el chat ya está en espera de asesor o en conversación,
     * el chatbot NO debe seguir guardando respuestas.
     */
    if (in_array($chat["estado"], ["esperando_asesor", "en_conversacion", "cerrado"])) {
        echo json_encode([
            "success" => true,
            "ignored" => true,
            "message" => "Chat fuera del flujo de chatbot"
        ]);
        exit;
    }

    $cliente_id = intval($chat["cliente_id"] ?? 0);

    // 2) Guardar respuesta interna del chatbot
    $stmt = $pdo->prepare("
        INSERT INTO chatbot_respuestas (chat_id, pregunta_id, respuesta, opcion_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $chat_id,
        $question_id,
        $answer,
        $option_id > 0 ? $option_id : 0
    ]);

    // 3) Guardar mensaje visible en el chat
    if ($cliente_id > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
            VALUES (?, 'cliente', ?, ?)
        ");
        $stmt->execute([$chat_id, $cliente_id, $answer]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO mensajes (chat_id, emisor, mensaje)
            VALUES (?, 'cliente', ?)
        ");
        $stmt->execute([$chat_id, $answer]);
    }

    // 4) Pregunta 2: guardar CUIL + Nombre
    if ($question_id === 2 && $cuil !== "" && $nombre !== "") {
        // Actualizar en chats
        $stmt = $pdo->prepare("
            UPDATE chats
            SET cuil_validado = ?, nombre_validado = ?
            WHERE id = ?
        ");
        $stmt->execute([$cuil, $nombre, $chat_id]);
        
        // Actualizar nombre en usuarios
        if ($cliente_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET nombre = ?
                WHERE id = ?
            ");
            $stmt->execute([$nombre, $cliente_id]);
        }
    }

    // 5) Departamento según opción
    $departamento_id = null;
    if ($option_id > 0) {
        $stmt = $pdo->prepare("SELECT departamento_id FROM chatbot_opciones WHERE id = ?");
        $stmt->execute([$option_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($res["departamento_id"])) {
            $departamento_id = intval($res["departamento_id"]);

            $stmt = $pdo->prepare("
                UPDATE chats 
                SET departamento_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$departamento_id, $chat_id]);
        }
    }

    // 6) Pregunta 3: situación laboral
    if ($question_id === 3) {
        $situacion = null;

        if (stripos($answer, "recibo") !== false) {
            $situacion = "RELACION_DEPENDENCIA";
        } elseif (stripos($answer, "jubil") !== false) {
            $situacion = "JUBILADO";
        } elseif (stripos($answer, "auh") !== false) {
            $situacion = "COBRA_AUH";
        } elseif (stripos($answer, "suaf") !== false) {
            $situacion = "COBRA_SUAF";
        } elseif (stripos($answer, "mono") !== false) {
            $situacion = "MONOTRIBUTISTA";
        } elseif (stripos($answer, "negro") !== false) {
            $situacion = "NO_REGISTRADO";
        }

        if ($situacion) {
            $stmt = $pdo->prepare("
                UPDATE chats 
                SET situacion_laboral = ?
                WHERE id = ?
            ");
            $stmt->execute([$situacion, $chat_id]);
        }
    }

    // 7) Pregunta banco
    if ($question_id === 6) {
        $stmt = $pdo->prepare("
            UPDATE chats 
            SET banco = ?
            WHERE id = ?
        ");
        $stmt->execute([$answer, $chat_id]);
    }

    // 8) ✅ PREGUNTA EMAIL (ID 8)
    if ($question_id === 8 && $answer !== "") {
        // Validar formato de email
        if (filter_var($answer, FILTER_VALIDATE_EMAIL)) {
            // Actualizar email en usuarios
            if ($cliente_id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE usuarios
                    SET email = ?
                    WHERE id = ?
                ");
                $stmt->execute([$answer, $cliente_id]);
                
                error_log("Email actualizado para cliente $cliente_id: $answer");
            }
        } else {
            error_log("Email inválido recibido: $answer");
        }
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "chat_id" => $chat_id,
            "question_id" => $question_id,
            "departamento_detectado" => $departamento_id
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error en save_answer.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar respuesta"
    ]);
}