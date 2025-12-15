<?php
// =====================================================
// mark_waiting_asesor.php
// - Finaliza el chatbot
// - Marca el chat como esperando asesor
// =====================================================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);
    exit;
}

// =====================================================
// INPUT
// =====================================================
$data = json_decode(file_get_contents("php://input"), true);
$chat_id = intval($data["chat_id"] ?? 0);

if ($chat_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "chat_id es obligatorio"
    ]);
    exit;
}

try {

    // =====================================================
    // 1) OBTENER CHAT
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT id, estado
        FROM chats
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Chat no encontrado"
        ]);
        exit;
    }

    // =====================================================
    // 2) SI YA ESTÁ EN ESTADO HUMANO → NO HACER NADA
    // =====================================================
    if (in_array($chat["estado"], ["esperando_asesor", "en_conversacion", "cerrado"], true)) {
        echo json_encode([
            "success" => true,
            "message" => "El chat ya está fuera del chatbot",
            "data" => [
                "chat_id" => $chat_id,
                "estado" => $chat["estado"]
            ]
        ]);
        exit;
    }

    $pdo->beginTransaction();

    // =====================================================
    // 3) CAMBIAR ESTADO A ESPERANDO ASESOR
    // =====================================================
    $stmt = $pdo->prepare("
        UPDATE chats
        SET estado = 'esperando_asesor'
        WHERE id = ?
    ");
    $stmt->execute([$chat_id]);

    // =====================================================
    // 4) MENSAJE DEL BOT (UNA SOLA VEZ)
    // =====================================================
    $stmt = $pdo->prepare("
        INSERT INTO mensajes (chat_id, emisor, mensaje)
        VALUES (?, 'bot', '👨‍💼 Un asesor se comunicará contigo en breve')
    ");
    $stmt->execute([$chat_id]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Chat marcado como esperando asesor",
        "data" => [
            "chat_id" => $chat_id,
            "estado" => "esperando_asesor",
            "chatbot_activo" => false
        ]
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("❌ Error mark_waiting_asesor.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al marcar el chat como esperando asesor"
    ]);
}
