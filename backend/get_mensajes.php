<?php
require "connection.php";

$chat_id = $_GET['chat_id'] ?? null;

if (!$chat_id) {
    echo json_encode(["error" => "chat_id requerido"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM mensajes
    WHERE chat_id = ?
    ORDER BY fecha ASC
");
$stmt->execute([$chat_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
