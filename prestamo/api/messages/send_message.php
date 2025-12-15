<?php
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
    // Verificar chat
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE id = ?");
    $stmt->execute([$chat_id]);

    if (!$stmt->fetch()) {
        throw new Exception("Chat no encontrado");
    }

    // Insertar mensaje
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

    // ⚠️ SOLO existe ultima_lectura_asesor
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
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}