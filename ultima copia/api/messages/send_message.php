<?php
// =====================================================
// send_message.php (PROD - BLINDADO)
// - NO inserta mensajes visibles mientras chat = en_flujo
// - Evita notificaciones prematuras al panel asesor
// =====================================================

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);
    exit;
}

$chat_id = (int)($_POST["chat_id"] ?? 0);
$sender  = $_POST["sender"] ?? "";
$message = trim($_POST["message"] ?? "");

if (!$chat_id || !$sender || $message === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Datos inválidos"
    ]);
    exit;
}

if (!in_array($sender, ["cliente", "asesor", "bot"], true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Sender inválido"
    ]);
    exit;
}

try {
    // =====================================================
    // 1) OBTENER ESTADO DEL CHAT
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT estado
        FROM chats
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$chat_id]);
    $estado = $stmt->fetchColumn();

    if (!$estado) {
        throw new Exception("Chat no encontrado");
    }

    // =====================================================
    // 2) BLOQUEAR MENSAJES VISIBLES DURANTE FLUJO
    // =====================================================
    if ($estado === "en_flujo") {
        // 🔒 Durante el chatbot NO se insertan mensajes
        echo json_encode([
            "success" => true,
            "ignored" => true,
            "message" => "Mensaje ignorado durante flujo"
        ]);
        exit;
    }

    // =====================================================
    // 3) INSERTAR MENSAJE (CHAT HUMANO)
    // =====================================================
    $stmt = $pdo->prepare("
        INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
        VALUES (?, ?, NULL, ?)
    ");
    $stmt->execute([
        $chat_id,
        $sender,
        $message
    ]);

    $mensaje_id = (int)$pdo->lastInsertId();

    // =====================================================
    // 4) MARCAR NO LEÍDO PARA ASESOR (SOLO HUMANO)
    // =====================================================
    if ($sender === "cliente") {
        $pdo->prepare("
            UPDATE chats
            SET ultima_lectura_asesor = NULL
            WHERE id = ?
        ")->execute([$chat_id]);
    }

    echo json_encode([
        "success" => true,
        "mensaje_id" => $mensaje_id
    ]);

} catch (Throwable $e) {

    error_log("❌ send_message.php ERROR: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al enviar mensaje"
    ]);
}
