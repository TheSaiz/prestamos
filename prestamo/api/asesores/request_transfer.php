<?php
header("Content-Type: application/json");
require_once "../../backend/connection.php";
session_start();

$asesor_origen = $_SESSION['asesor_id'] ?? 0;
$data = json_decode(file_get_contents("php://input"), true);

$chat_id = intval($data['chat_id'] ?? 0);
$asesor_destino = intval($data['asesor_destino'] ?? 0);

if (!$chat_id || !$asesor_destino || !$asesor_origen) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

try {
    // Evitar duplicados
    $check = $pdo->prepare("
        SELECT id FROM chat_transferencias
        WHERE chat_id = ? AND estado = 'pendiente'
    ");
    $check->execute([$chat_id]);

    if ($check->fetch()) {
        echo json_encode(["success" => false, "message" => "Ya hay una transferencia pendiente"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO chat_transferencias (chat_id, asesor_origen, asesor_destino)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$chat_id, $asesor_origen, $asesor_destino]);

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error interno"]);
}
