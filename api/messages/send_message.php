<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";

$response = new Response();
$db = new Database();
$conn = $db->connect();

// ===============================
// VALIDAR MÉTODO
// ===============================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response->error("Método no permitido", 405);
}

// ===============================
// RECIBIR PARÁMETROS
// ===============================
$chat_id    = $_POST["chat_id"] ?? null;
$sender     = $_POST["sender"] ?? null; // client | bot | asesor
$message    = $_POST["message"] ?? null;

if (!$chat_id || !$sender || !$message) {
    $response->error("Faltan datos obligatorios");
}

// ===============================
// VALIDAR QUE EL CHAT EXISTA
// ===============================
$stmt = $conn->prepare("SELECT id FROM chats WHERE id = ?");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();

if (!$exists) {
    $response->error("El chat no existe");
}

// ===============================
// GUARDAR MENSAJE
// ===============================
$stmt = $conn->prepare("
    INSERT INTO messages (chat_id, sender, message, timestamp)
    VALUES (?, ?, ?, NOW())
");

$stmt->bind_param("iss", $chat_id, $sender, $message);

if (!$stmt->execute()) {
    $response->error("Error al guardar el mensaje");
}

// ===============================
// ACTUALIZAR LA ACTIVIDAD DEL CHAT
// ===============================
$stmt = $conn->prepare("
    UPDATE chats 
    SET last_activity = NOW() 
    WHERE id = ?
");
$stmt->bind_param("i", $chat_id);
$stmt->execute();

// ===============================
// RESPUESTA
// ===============================
$response->success([
    "message" => "Mensaje enviado correctamente",
    "chat_id" => $chat_id,
    "sender" => $sender
]);
