<?php
require "connection.php";

$chat_id = $_POST['chat_id'] ?? null;
$pregunta_id = $_POST['pregunta_id'] ?? null;
$respuesta = $_POST['respuesta'] ?? '';

if (!$chat_id || !$pregunta_id) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
    VALUES (?, 'bot', NULL, ?)
");
$stmt->execute([$chat_id, "Respuesta: $respuesta"]);

echo json_encode(["success" => true]);
?>
