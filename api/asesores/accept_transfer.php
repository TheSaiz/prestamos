<?php
header("Content-Type: application/json");
require_once "../../backend/connection.php";
session_start();

$asesor_id = $_SESSION['asesor_id'] ?? 0;
$data = json_decode(file_get_contents("php://input"), true);

$transfer_id = intval($data['transfer_id'] ?? 0);

if (!$transfer_id || !$asesor_id) {
    echo json_encode(["success" => false]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT chat_id, asesor_destino
        FROM chat_transferencias
        WHERE id = ? AND estado = 'pendiente'
        FOR UPDATE
    ");
    $stmt->execute([$transfer_id]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$t || $t['asesor_destino'] != $asesor_id) {
        throw new Exception("No autorizado");
    }

    // Aceptar transferencia
    $pdo->prepare("
        UPDATE chat_transferencias
        SET estado = 'aceptada', fecha_respuesta = NOW()
        WHERE id = ?
    ")->execute([$transfer_id]);

    // Mover chat
    $pdo->prepare("
        UPDATE chats
        SET asesor_id = ?, ultima_lectura_asesor = NOW()
        WHERE id = ?
    ")->execute([$asesor_id, $t['chat_id']]);

    $pdo->commit();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Error"]);
}
