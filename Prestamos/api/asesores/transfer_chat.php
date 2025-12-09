<?php
header("Content-Type: application/json");
require_once "../../backend/connection.php";

$data = json_decode(file_get_contents("php://input"), true);

$chat_id = intval($data["chat_id"] ?? 0);
$asesor_destino = intval($data["asesor_destino"] ?? 0);

if (!$chat_id || !$asesor_destino) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

try {
    // Actualizar asesor asignado
    $stmt = $pdo->prepare("
        UPDATE chats 
        SET asesor_id = ?, ultima_lectura_asesor = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$asesor_destino, $chat_id]);

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
