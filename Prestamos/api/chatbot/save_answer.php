<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$chat_id = intval($_POST["chat_id"] ?? 0);
$question_id = intval($_POST["question_id"] ?? 0);
$answer = trim($_POST["answer"] ?? "");
$option_id = intval($_POST["option_id"] ?? 0);

if (!$chat_id || !$question_id || !$answer) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parámetros incompletos"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT cliente_id FROM chats WHERE id = ?");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch();

    if (!$chat) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
        VALUES (?, 'cliente', ?, ?)
    ");
    $stmt->execute([$chat_id, $chat['cliente_id'], $answer]);

    $departamento_id = null;

    if ($option_id > 0) {
        $stmt = $pdo->prepare("SELECT departamento_id FROM chatbot_opciones WHERE id = ?");
        $stmt->execute([$option_id]);
        $res = $stmt->fetch();

        if ($res) {
            $departamento_id = intval($res["departamento_id"]);
            $stmt2 = $pdo->prepare("UPDATE chats SET departamento_id = ? WHERE id = ?");
            $stmt2->execute([$departamento_id, $chat_id]);
        }
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
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar respuesta"
    ]);
}
?>