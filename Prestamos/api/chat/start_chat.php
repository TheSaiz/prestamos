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

$nombre = trim($data["nombre"] ?? "");
$dni = trim($data["dni"] ?? "");
$telefono = trim($data["telefono"] ?? "");
$departamento_id = intval($data["departamento_id"] ?? 1);

if (!$nombre || !$dni || !$telefono) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Todos los datos son obligatorios"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, telefono, password, rol)
        VALUES (?, ?, ?, ?, 'cliente')
    ");

    $email_fake = "temp_" . time() . "_" . rand(1000,9999) . "@cliente.com";
    $password_fake = password_hash("temp123", PASSWORD_DEFAULT);

    $stmt->execute([$nombre, $email_fake, $telefono, $password_fake]);
    $cliente_id = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("
        INSERT INTO clientes_detalles (usuario_id, dni)
        VALUES (?, ?)
    ");
    $stmt2->execute([$cliente_id, $dni]);

    $stmt3 = $pdo->prepare("
        INSERT INTO chats (cliente_id, departamento_id, origen, estado)
        VALUES (?, ?, 'chatbot', 'esperando_asesor')
    ");
    $stmt3->execute([$cliente_id, $departamento_id]);
    $chat_id = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Chat creado correctamente",
        "data" => [
            "chat_id" => $chat_id,
            "cliente_id" => $cliente_id,
            "departamento_id" => $departamento_id
        ]
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Error al crear el chat"
    ]);
}
?>