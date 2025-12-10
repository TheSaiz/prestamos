<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
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

// Datos opcionales (solo para pregunta 2)
$cuil        = trim($_POST["cuil"] ?? "");
$nombre      = trim($_POST["nombre"] ?? "");

if (!$chat_id || !$question_id || !$answer) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parámetros incompletos"]);
    exit;
}

try {
    // Obtener chat y cliente
    $stmt = $pdo->prepare("SELECT cliente_id FROM chats WHERE id = ?");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch();

    if (!$chat) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    // Guardar respuesta interna del chatbot
    $stmt = $pdo->prepare("
        INSERT INTO chatbot_respuestas (chat_id, pregunta_id, respuesta, opcion_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$chat_id, $question_id, $answer, $option_id > 0 ? $option_id : null]);

    // Guardar el mensaje visible en el chat
    $stmt = $pdo->prepare("
        INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
        VALUES (?, 'cliente', ?, ?)
    ");
    $stmt->execute([$chat_id, $chat['cliente_id'], $answer]);

    // ===========================================
    // PREGUNTA 2: Guardar CUIL y Nombre Validado
    // ===========================================
    if ($question_id === 2 && $cuil && $nombre) {
        $stmt = $pdo->prepare("
            UPDATE chats
            SET cuil_validado = ?, nombre_validado = ?
            WHERE id = ?
        ");
        $stmt->execute([$cuil, $nombre, $chat_id]);
    }

    // ===========================================
    // Detectar Departamento automáticamente
    // ===========================================
    $departamento_id = null;

    if ($option_id > 0) {
        $stmt = $pdo->prepare("SELECT departamento_id FROM chatbot_opciones WHERE id = ?");
        $stmt->execute([$option_id]);
        $res = $stmt->fetch();

        if ($res) {
            $departamento_id = intval($res["departamento_id"]);

            // Actualizar departamento en el chat
            $stmt = $pdo->prepare("UPDATE chats SET departamento_id = ? WHERE id = ?");
            $stmt->execute([$departamento_id, $chat_id]);
        }
    }

    // ===========================================
    // PREGUNTA 3: Situación laboral
    // ===========================================
    if ($question_id === 3 && $option_id > 0) {
        $map = [
            "Recibo de Sueldo" => "RELACION_DEPENDENCIA",
            "jubilado"         => "JUBILADO",
            "Universal"        => "COBRA_AUH",
            "Familiares"       => "COBRA_SUAF",
            "Monotributista"   => "MONO_RESPONSABLE",
            "registrado"       => "COBRA_NEGRO"
        ];

        $situacion = null;
        foreach ($map as $key => $value) {
            if (stripos($answer, $key) !== false) {
                $situacion = $value;
                break;
            }
        }

        if ($situacion) {
            $stmt = $pdo->prepare("UPDATE chats SET situacion_laboral = ? WHERE id = ?");
            $stmt->execute([$situacion, $chat_id]);
        }
    }

    // ===========================================
    // PREGUNTA 6: Banco
    // ===========================================
    if ($question_id === 6) {
        $stmt = $pdo->prepare("UPDATE chats SET banco = ? WHERE id = ?");
        $stmt->execute([$answer, $chat_id]);
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "saved" => true,
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
?>
