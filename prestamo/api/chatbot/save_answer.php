<?php
// =====================================================
// save_answer.php (FINAL REAL - RECONSTRUIDO + FIX TELÉFONO)
// =====================================================

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

// =====================================================
// 🔥 LEER INPUT (FormData O JSON)
// =====================================================
$data = $_POST;

if (empty($data)) {
    $json = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        $data = $json;
    }
}

// =====================================================
// INPUTS
// =====================================================
$chat_id     = intval($data["chat_id"] ?? 0);
$question_id = intval($data["question_id"] ?? 0);
$answer      = trim((string)($data["answer"] ?? ""));
$option_id   = intval($data["option_id"] ?? 0);

// opcionales
$cuil   = trim((string)($data["cuil"] ?? ""));
$nombre = trim((string)($data["nombre"] ?? ""));
$dni    = trim((string)($data["dni"] ?? ""));

if ($chat_id <= 0 || $question_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parámetros incompletos"]);
    exit;
}

if ($answer === "" && $option_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Respuesta vacía"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // =====================================================
    // 1) OBTENER CHAT
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT id, cliente_id, estado
        FROM chats
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        throw new Exception("Chat no encontrado");
    }

    // 🔒 Si ya está con asesor, ignorar chatbot
    if (in_array($chat["estado"], ["esperando_asesor", "en_conversacion", "cerrado"], true)) {
        $pdo->commit();
        echo json_encode([
            "success" => true,
            "ignored" => true,
            "message" => "Chat fuera del flujo de chatbot"
        ]);
        exit;
    }

    $cliente_id = intval($chat["cliente_id"]);
    if ($cliente_id <= 0) {
        throw new Exception("Chat sin cliente asociado");
    }

    // =====================================================
    // 2) ASEGURAR REGISTRO EN clientes
    // =====================================================
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
    $stmt->execute([$cliente_id]);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO clientes (usuario_id, email, telefono)
            VALUES (?, ?, '')
        ");
        $stmt->execute([
            $cliente_id,
            "temp_" . time() . "_" . rand(1000,9999) . "@cliente.com"
        ]);
    }

    // =====================================================
    // 3) GUARDAR RESPUESTA CHATBOT
    // =====================================================
    $stmt = $pdo->prepare("
        INSERT INTO chatbot_respuestas (chat_id, pregunta_id, respuesta, opcion_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $chat_id,
        $question_id,
        $answer,
        $option_id > 0 ? $option_id : null
    ]);

    // =====================================================
    // 4) MENSAJE VISIBLE EN CHAT
    // =====================================================
    if ($answer !== "") {
        $stmt = $pdo->prepare("
            INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
            VALUES (?, 'cliente', ?, ?)
        ");
        $stmt->execute([$chat_id, $cliente_id, $answer]);
    }

    // =====================================================
    // 5) DNI / NOMBRE / CUIL (PREGUNTA ID 2)
    // =====================================================
    if ($question_id === 2) {

        $dni_final = $dni;

        if ($dni_final === "" && preg_match('/^\d{7,9}$/', $answer)) {
            $dni_final = $answer;
        }

        if ($dni_final === "") {
            $stmt = $pdo->prepare("
                SELECT respuesta
                FROM chatbot_respuestas
                WHERE chat_id = ? AND pregunta_id = 2
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([$chat_id]);
            $tmp = trim((string)$stmt->fetchColumn());
            if (preg_match('/^\d{7,9}$/', $tmp)) {
                $dni_final = $tmp;
            }
        }

        if ($dni_final !== "") {
            $stmt = $pdo->prepare("
                INSERT INTO clientes_detalles (usuario_id, dni)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE dni = VALUES(dni)
            ");
            $stmt->execute([$cliente_id, $dni_final]);
        }

        if ($cuil !== "" || $nombre !== "") {
            $stmt = $pdo->prepare("
                UPDATE chats
                SET
                    cuil_validado   = COALESCE(NULLIF(?, ''), cuil_validado),
                    nombre_validado = COALESCE(NULLIF(?, ''), nombre_validado)
                WHERE id = ?
            ");
            $stmt->execute([$cuil, $nombre, $chat_id]);
        }

        if ($nombre !== "") {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
            $stmt->execute([$nombre, $cliente_id]);
        }
    }

    // =====================================================
    // 6) TELÉFONO (PREGUNTA ID 5) ✅ FIX ANTI DUPLICADO
    // =====================================================
    if ($question_id === 5 && $answer !== "") {

        // Código de área (pregunta 4)
        $stmt = $pdo->prepare("
            SELECT respuesta
            FROM chatbot_respuestas
            WHERE chat_id = ? AND pregunta_id = 4
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$chat_id]);
        $codigo_area = preg_replace('/\D+/', '', (string)$stmt->fetchColumn());

        // Teléfono ingresado
        $telefono = preg_replace('/\D+/', '', $answer);

        // 🔒 Evitar duplicar código de área
        if ($codigo_area !== '' && str_starts_with($telefono, $codigo_area)) {
            $telefono_final = $telefono;
        } else {
            $telefono_final = $codigo_area . $telefono;
        }

        if ($telefono_final !== "") {
            $stmt = $pdo->prepare("UPDATE usuarios SET telefono = ? WHERE id = ?");
            $stmt->execute([$telefono_final, $cliente_id]);

            $stmt = $pdo->prepare("UPDATE clientes SET telefono = ? WHERE usuario_id = ?");
            $stmt->execute([$telefono_final, $cliente_id]);
        }
    }

// =====================================================
// 7) EMAIL (PREGUNTA ID 8) ✅ FIX DUPLICADO
// =====================================================
if ($question_id === 8 && filter_var($answer, FILTER_VALIDATE_EMAIL)) {

    // ¿El email ya existe en otro usuario?
    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE email = ?
          AND id <> ?
        LIMIT 1
    ");
    $stmt->execute([$answer, $cliente_id]);

    if ($stmt->fetch()) {
        // Email ya usado → NO romper el flujo
        $pdo->commit();
        echo json_encode([
            "success" => false,
            "message" => "El email ya está registrado"
        ]);
        exit;
    }

    // Guardar email
    $stmt = $pdo->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
    $stmt->execute([$answer, $cliente_id]);

    $stmt = $pdo->prepare("UPDATE clientes SET email = ? WHERE usuario_id = ?");
    $stmt->execute([$answer, $cliente_id]);
}


    // =====================================================
    // 8) DEPARTAMENTO (OPCIÓN)
    // =====================================================
    if ($option_id > 0) {
        $stmt = $pdo->prepare("
            SELECT departamento_id
            FROM chatbot_opciones
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$option_id]);
        $dep = $stmt->fetchColumn();

        if ($dep) {
            $stmt = $pdo->prepare("UPDATE chats SET departamento_id = ? WHERE id = ?");
            $stmt->execute([intval($dep), $chat_id]);
        }
    }

    // =====================================================
    // 9) SITUACIÓN LABORAL (PREGUNTA ID 3)
    // =====================================================
    if ($question_id === 3 && $answer !== "") {
        $map = [
            "recibo" => "RELACION_DEPENDENCIA",
            "jubil"  => "JUBILADO",
            "auh"    => "COBRA_AUH",
            "suaf"   => "COBRA_SUAF",
            "mono"   => "MONOTRIBUTISTA",
            "negro"  => "NO_REGISTRADO"
        ];

        foreach ($map as $k => $v) {
            if (stripos($answer, $k) !== false) {
                $stmt = $pdo->prepare("UPDATE chats SET situacion_laboral = ? WHERE id = ?");
                $stmt->execute([$v, $chat_id]);
                break;
            }
        }
    }

    // =====================================================
    // 10) BANCO (PREGUNTA ID 7)
    // =====================================================
    if ($question_id === 7 && $answer !== "") {
        $stmt = $pdo->prepare("UPDATE chats SET banco = ? WHERE id = ?");
        $stmt->execute([$answer, $chat_id]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "data" => [
            "chat_id" => $chat_id,
            "question_id" => $question_id
        ]
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("❌ save_answer.php ERROR: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar respuesta"
    ]);
}
