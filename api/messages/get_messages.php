<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";

$response = new Response();
$db = new Database();
$conn = $db->connect();

// ======================================
// VALIDAR MÉTODO
// ======================================
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    $response->error("Método no permitido", 405);
}

// ======================================
// RECIBIR PARÁMETROS
// ======================================
$chat_id = $_GET["chat_id"] ?? null;
$last_id = $_GET["last_id"] ?? 0; // Para actualización incremental

if (!$chat_id) {
    $response->error("chat_id es obligatorio");
}

// ======================================
// VALIDAR QUE EL CHAT EXISTA
// ======================================
$stmt = $conn->prepare("SELECT id FROM chats WHERE id = ?");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$chat = $stmt->get_result()->fetch_assoc();

if (!$chat) {
    $response->error("El chat no existe");
}

// ======================================
// OBTENER MENSAJES
// ======================================
// Si last_id = 0 => trae todo
// Si last_id > 0 => trae solo los nuevos

$query = "
    SELECT 
        id,
        sender,
        message,
        timestamp
    FROM messages
    WHERE chat_id = ?
    AND id > ?
    ORDER BY id ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $chat_id, $last_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "id" => intval($row["id"]),
        "sender" => $row["sender"],
        "message" => $row["message"],
        "timestamp" => $row["timestamp"]
    ];
}

// ======================================
// RESPUESTA
// ======================================
$response->success([
    "chat_id" => $chat_id,
    "last_id" => count($messages) ? end($messages)["id"] : $last_id,
    "messages" => $messages
]);
