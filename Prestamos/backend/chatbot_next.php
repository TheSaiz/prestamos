<?php
require "connection.php";

$stmt = $pdo->query("SELECT * FROM chatbot_flujo ORDER BY id ASC");
$preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($preguntas);
?>
