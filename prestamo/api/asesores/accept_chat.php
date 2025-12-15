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

    // Verificar que el chat siga disponible (CRÍTICO: usar FOR UPDATE para evitar race conditions)
    $stmt = $pdo->prepare("
        SELECT id, estado, asesor_id 
        FROM chats 
        WHERE id = ? 
        FOR UPDATE
    ");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch();

    if (!$chat) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    // Verificar que el chat no haya sido tomado por otro asesor
    if ($chat['asesor_id'] !== null || $chat['estado'] !== 'esperando_asesor') {
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

    // NO marcar como no disponible - permitir múltiples chats simultáneos
    // Si quieres limitar a 1 chat por asesor, descomenta esto:
    // $stmt = $pdo->prepare("UPDATE asesores_departamentos SET disponible = 0 WHERE asesor_id = ?");
    // $stmt->execute([$asesor_id]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Chat aceptado correctamente",
        "data" => ["chat_id" => $chat_id]
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error en accept_chat: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al aceptar el chat"]);
}
?>