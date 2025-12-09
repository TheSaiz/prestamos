<?php
require "connection.php";

$cliente_id = $_POST['cliente_id'] ?? null;
$departamento_id = $_POST['departamento_id'] ?? null;

if (!$cliente_id || !$departamento_id) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO chats (cliente_id, departamento_id, origen, estado)
    VALUES (?, ?, 'chatbot', 'esperando_asesor')
");
$stmt->execute([$cliente_id, $departamento_id]);

echo json_encode([
    "success" => true,
    "chat_id" => $pdo->lastInsertId()
]);
?>
