<?php
// /api/chat/close_chat.php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') response(false, "Método no permitido", null, 405);

$chat_id = intval($_POST['chat_id'] ?? 0);
if (!$chat_id) response(false, "chat_id requerido", null, 400);

// Obtener asesor asignado para liberarlo
$res = $conn->query("SELECT asesor_id FROM chats WHERE id = $chat_id LIMIT 1");
$row = $res->fetch_assoc();
$asesor_id = $row['asesor_id'] ?? null;

$ts = date('Y-m-d H:i:s');
$upd = $conn->prepare("UPDATE chats SET estado = 'cerrado', fecha_cierre = ? WHERE id = ?");
$upd->bind_param("si", $ts, $chat_id);
$upd->execute();

if ($asesor_id) {
    $conn->query("UPDATE asesores_departamentos SET disponible = 1 WHERE asesor_id = " . intval($asesor_id));
}

response(true, "Chat cerrado");
?>
