<?php
// =====================================================
// ACCEPT CHAT - PRODUCCIÓN
// =====================================================

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../backend/connection.php";

try {
    // -------------------------------------------------
    // VALIDAR MÉTODO
    // -------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Método no permitido"
        ]);
        exit;
    }

    // -------------------------------------------------
    // LEER JSON
    // -------------------------------------------------
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "JSON inválido"
        ]);
        exit;
    }

    $chatId   = isset($data['chat_id'])   ? (int)$data['chat_id']   : 0;
    $asesorId = isset($data['asesor_id']) ? (int)$data['asesor_id'] : 0;

    if ($chatId <= 0 || $asesorId <= 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Datos incompletos"
        ]);
        exit;
    }

    // -------------------------------------------------
    // TRANSACCIÓN (LOCK COMPETITIVO)
    // -------------------------------------------------
    $pdo->beginTransaction();

    // Bloquea el chat para evitar doble aceptación
    $stmt = $pdo->prepare("
        SELECT estado, asesor_id
        FROM chats
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->execute([$chatId]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Chat inexistente"
        ]);
        exit;
    }

    // -------------------------------------------------
    // VALIDAR DISPONIBILIDAD (FIX REAL)
    // -------------------------------------------------
    if (
        !in_array($chat['estado'], ['pendiente', 'esperando_asesor'], true)
        || !is_null($chat['asesor_id'])
    ) {
        $pdo->rollBack();
        echo json_encode([
            "success" => false,
            "message" => "El chat ya fue aceptado por otro asesor"
        ]);
        exit;
    }

    // -------------------------------------------------
    // ACEPTAR CHAT
    // -------------------------------------------------
    $stmt = $pdo->prepare("
        UPDATE chats
        SET 
            estado = 'en_conversacion',
            asesor_id = ?,
            fecha_asignacion = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$asesorId, $chatId]);

    if ($stmt->rowCount() !== 1) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "No se pudo asignar el chat"
        ]);
        exit;
    }

    $pdo->commit();

    // -------------------------------------------------
    // RESPUESTA OK
    // -------------------------------------------------
    echo json_encode([
        "success" => true,
        "message" => "Chat aceptado correctamente"
    ]);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("❌ accept_chat.php error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor"
    ]);
    exit;
}
