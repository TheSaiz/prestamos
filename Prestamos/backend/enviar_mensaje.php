<?php
require "connection.php";

$chat_id = $_POST['chat_id'] ?? null;
$emisor = $_POST['emisor'] ?? null;  // cliente | asesor | bot
$usuario_id = $_POST['usuario_id'] ?? null;
$mensaje = $_POST['mensaje'] ?? '';

if (!$chat_id || !$emisor || !$mensaje) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$chat_id, $emisor, $usuario_id, $mensaje]);

echo json_encode(["success" => true]);
?>
