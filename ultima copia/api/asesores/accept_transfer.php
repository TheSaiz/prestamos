<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "../../backend/connection.php";
session_start();

/**
 * =====================================================
 * ACEPTAR TRANSFERENCIA DE CHAT (VERSIÓN CORRECTA)
 * - Usa usuario_id como ID ÚNICO del asesor
 * - Compatible con widget + panel asesor
 * =====================================================
 */

// 🔐 ID del asesor logueado (UNIFICADO)
$asesor_id = $_SESSION['usuario_id'] ?? 0;

// Datos recibidos
$data = json_decode(file_get_contents("php://input"), true);
$transfer_id = intval($data['transfer_id'] ?? 0);

// Validaciones básicas
if (!$transfer_id || !$asesor_id) {
    echo json_encode([
        "success" => false,
        "message" => "Datos inválidos"
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    // =====================================================
    // 1) VALIDAR TRANSFERENCIA PENDIENTE
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT chat_id, asesor_destino
        FROM chat_transferencias
        WHERE id = ?
          AND estado = 'pendiente'
        FOR UPDATE
    ");
    $stmt->execute([$transfer_id]);
    $transfer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$transfer ||
        intval($transfer['asesor_destino']) !== intval($asesor_id)
    ) {
        throw new Exception("No autorizado para aceptar esta transferencia");
    }

    // =====================================================
    // 2) MARCAR TRANSFERENCIA COMO ACEPTADA
    // =====================================================
    $pdo->prepare("
        UPDATE chat_transferencias
        SET estado = 'aceptada',
            fecha_respuesta = NOW()
        WHERE id = ?
    ")->execute([$transfer_id]);

    // =====================================================
    // 3) ASIGNAR CHAT AL NUEVO ASESOR
    // =====================================================
    $pdo->prepare("
        UPDATE chats
        SET asesor_id = ?,
            ultima_lectura_asesor = NOW()
        WHERE id = ?
    ")->execute([
        $asesor_id,
        $transfer['chat_id']
    ]);

    $pdo->commit();

    echo json_encode([
        "success" => true
    ]);

} catch (Throwable $e) {

    $pdo->rollBack();

    error_log("❌ accept_transfer ERROR: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Error al aceptar la transferencia"
    ]);
}
