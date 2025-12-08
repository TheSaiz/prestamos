<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$chat_id = intval($_POST["chat_id"] ?? 0);
$sender = trim($_POST["sender"] ?? "");
$message = trim($_POST["message"] ?? "");

if (!$chat_id || !$sender || !$message) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE id = ?");
    $stmt->execute([$chat_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO mensajes (chat_id, emisor, mensaje, fecha)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$chat_id, $sender, $message]);

    echo json_encode([
        "success" => true,
        "message" => "Mensaje enviado"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al enviar mensaje"
    ]);
}
?>