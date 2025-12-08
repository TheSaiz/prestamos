<?php
// /api/chat/get_chat.php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

$chat_id = intval($_GET['chat_id'] ?? 0);
if (!$chat_id) response(false, "chat_id requerido", null, 400);

$stmt = $conn->prepare("
    SELECT c.*, u.nombre as cliente_nombre, u.telefono as cliente_telefono, d.nombre as departamento
    FROM chats c
    LEFT JOIN usuarios u ON c.cliente_id = u.id
    LEFT JOIN departamentos d ON c.departamento_id = d.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) response(false, "Chat no encontrado", null, 404);

$data = $res->fetch_assoc();
response(true, "Chat encontrado", $data);
?>
