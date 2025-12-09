<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$chat_id = intval($data["chat_id"] ?? 0);
$asesor_id = intval($data["asesor_id"] ?? 0);

if (!$chat_id || !$asesor_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parámetros incompletos"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar que el chat siga disponible
    $stmt = $pdo->prepare("
        SELECT id, estado 
        FROM chats 
        WHERE id = ? AND estado = 'esperando_asesor' AND asesor_id IS NULL
        FOR UPDATE
    ");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch();

    if (!$chat) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "El chat ya fue tomado por otro asesor"]);
        exit;
    }

    // Asignar el asesor al chat
    $stmt = $pdo->prepare("
        UPDATE chats 
        SET asesor_id = ?, estado = 'en_conversacion'
        WHERE id = ?
    ");
    $stmt->execute([$asesor_id, $chat_id]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Chat aceptado correctamente",
        "data" => ["chat_id" => $chat_id]
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al aceptar el chat"]);
}
?>