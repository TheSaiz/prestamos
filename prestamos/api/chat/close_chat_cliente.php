<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$chat_id = intval($data["chat_id"] ?? 0);

if (!$chat_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "chat_id es obligatorio"]);
    exit;
}

try {
    // Obtener nombre del cliente a partir del chat
    $stmt = $pdo->prepare("
        SELECT u.nombre
        FROM chats c
        INNER JOIN usuarios u ON c.cliente_id = u.id
        WHERE c.id = ?
        LIMIT 1
    ");
    $stmt->execute([$chat_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    $nombre_cliente = $row["nombre"] ?? "cliente";

    // Actualizar estado del chat
    $stmt = $pdo->prepare("
        UPDATE chats 
        SET estado = 'cerrado_cliente'
        WHERE id = ?
    ");
    $stmt->execute([$chat_id]);

    // Insertar mensaje de sistema como si fuera del cliente
    $mensaje_cierre = "Conversación terminada por " . $nombre_cliente;

    $stmt = $pdo->prepare("
        INSERT INTO mensajes (chat_id, emisor, mensaje, fecha)
        VALUES (?, 'cliente', ?, NOW())
    ");
    $stmt->execute([$chat_id, $mensaje_cierre]);

    echo json_encode([
        "success" => true,
        "message" => "Conversación terminada correctamente"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al cerrar la conversación"
        // "error" => $e->getMessage() // descomentar solo para debug
    ]);
}
