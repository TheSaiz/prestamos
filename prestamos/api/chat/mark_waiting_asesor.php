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
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$chat_id = intval($data["chat_id"] ?? 0);

if (!$chat_id) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "chat_id es obligatorio"
    ]);
    exit;
}

try {
    // 🔎 Verificar chat
    $stmt = $pdo->prepare("
        SELECT id, estado 
        FROM chats 
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Chat no encontrado"
        ]);
        exit;
    }

    // 🛑 Si ya está en modo humano, no hacer nada
    if (in_array($chat['estado'], ['esperando_asesor', 'en_conversacion', 'cerrado'])) {
        echo json_encode([
            "success" => true,
            "message" => "El chat ya está en modo humano",
            "data" => [
                "chat_id" => $chat_id,
                "estado" => $chat['estado']
            ]
        ]);
        exit;
    }

    // 🔥 Cortar chatbot DEFINITIVAMENTE
    $stmt = $pdo->prepare("
        UPDATE chats 
        SET estado = 'esperando_asesor'
        WHERE id = ?
    ");
    $stmt->execute([$chat_id]);

    // 🤖 Mensaje del sistema (solo una vez)
    $stmt = $pdo->prepare("
        INSERT INTO mensajes (chat_id, emisor, mensaje)
        VALUES (?, 'bot', '🔔 Cliente esperando asistencia de un asesor')
    ");
    $stmt->execute([$chat_id]);

    echo json_encode([
        "success" => true,
        "message" => "Chat marcado como esperando asesor",
        "data" => [
            "chat_id" => $chat_id,
            "estado" => "esperando_asesor",
            "chatbot_activo" => false
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error en mark_waiting_asesor.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al actualizar el estado del chat"
    ]);
}