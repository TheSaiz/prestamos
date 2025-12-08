<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/utils.php";

header("Content-Type: application/json");

$db = new Database();
$conn = $db->connect();

// ------------------------------------
// 1. Verificar método POST
// ------------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Método no permitido", 405);
}

$data = Utils::getJsonData();

if (!isset($data["chat_request_id"]) || !isset($data["asesor_id"])) {
    Response::error("Faltan parámetros: chat_request_id, asesor_id");
}

$chat_request_id = $data["chat_request_id"];
$asesor_id       = $data["asesor_id"];

// ------------------------------------
// 2. Verificar solicitud
// ------------------------------------
$stmt = $conn->prepare("
    SELECT cr.*, c.estado AS chat_status
    FROM chat_requests cr
    INNER JOIN chats c ON cr.chat_id = c.id
    WHERE cr.id = ?
");
$stmt->execute([$chat_request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    Response::error("La solicitud no existe");
}

if ($request["status"] !== "pending") {
    Response::error("La solicitud ya fue tomada por otro asesor");
}

$chat_id = $request["chat_id"];

// ------------------------------------
// 3. Aceptar esta solicitud
// ------------------------------------
$stmt = $conn->prepare("
    UPDATE chat_requests
    SET status = 'accepted'
    WHERE id = ?
");
$stmt->execute([$chat_request_id]);

// ------------------------------------
// 4. Rechazar las demás solicitudes del mismo chat
// ------------------------------------
$stmt = $conn->prepare("
    UPDATE chat_requests
    SET status = 'rejected'
    WHERE chat_id = ? AND id != ?
");
$stmt->execute([$chat_id, $chat_request_id]);

// ------------------------------------
// 5. Actualizar el chat
// ------------------------------------
$stmt = $conn->prepare("
    UPDATE chats
    SET asesor_id = ?, estado = 'en_conversacion'
    WHERE id = ?
");
$stmt->execute([$asesor_id, $chat_id]);

// ------------------------------------
// 6. Obtener información final del chat
// ------------------------------------
$stmt = $conn->prepare("
    SELECT c.*, u.nombre AS cliente_nombre, u.email AS cliente_email
    FROM chats c
    INNER JOIN usuarios u ON u.id = c.cliente_id
    WHERE c.id = ?
");
$stmt->execute([$chat_id]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

// ------------------------------------
// 7. Respuesta exitosa
// ------------------------------------
Response::success("Chat aceptado correctamente", [
    "chat_id"      => $chat_id,
    "asesor_id"    => $asesor_id,
    "cliente"      => [
        "id"     => $chat["cliente_id"],
        "nombre" => $chat["cliente_nombre"],
        "email"  => $chat["cliente_email"]
    ],
    "estado_chat"  => "en_conversacion"
]);
