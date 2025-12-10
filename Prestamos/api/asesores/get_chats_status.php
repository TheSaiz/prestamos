<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "../../backend/connection.php";

$asesor_id = intval($_GET['asesor_id'] ?? 0);
if (!$asesor_id) {
    echo json_encode(["success" => false]);
    exit;
}

// Devuelve solo ID + mensajes no leídos → rápido para polling
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        (SELECT COUNT(*) FROM mensajes 
         WHERE chat_id = c.id 
           AND emisor = 'cliente' 
           AND fecha > COALESCE(c.ultima_lectura_asesor, '1970-01-01')
        ) AS mensajes_nuevos
    FROM chats c
    WHERE c.asesor_id = ?
");
$stmt->execute([$asesor_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $rows
]);
