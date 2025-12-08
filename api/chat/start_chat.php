<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/utils.php";

$response = new Response();
$db = new Database();
$conn = $db->connect();

// =============================
// Validar método HTTP
// =============================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response->error("Método no permitido", 405);
}

// =============================
// Obtener datos enviados
// =============================
$data = json_decode(file_get_contents("php://input"), true);

$nombre = trim($data["nombre"] ?? "");
$dni = trim($data["dni"] ?? "");
$telefono = trim($data["telefono"] ?? "");
$departamento_id = $data["departamento_id"] ?? null;

if (!$nombre || !$dni || !$telefono) {
    $response->error("Todos los datos son obligatorios");
}

if (!$departamento_id) {
    $response->error("departamento_id es obligatorio (viene del chatbot)");
}

// ===================================================
// 1. Crear cliente temporal en tabla usuarios
// ===================================================
$stmt = $conn->prepare("
    INSERT INTO usuarios (nombre, email, telefono, rol)
    VALUES (?, ?, ?, 'cliente')
");

$email_fake = "temp_" . time() . "_" . rand(1000,9999) . "@cliente.com";

$stmt->bind_param("sss", $nombre, $email_fake, $telefono);
$stmt->execute();

$cliente_id = $stmt->insert_id;

// ===================================================
// 2. Guardar DNI y otros datos
// ===================================================
$stmt2 = $conn->prepare("
    INSERT INTO clientes_detalles (usuario_id, dni)
    VALUES (?, ?)
");
$stmt2->bind_param("is", $cliente_id, $dni);
$stmt2->execute();

// ===================================================
// 3. Crear el chat en estado: esperando_asesor
// ===================================================
$stmt3 = $conn->prepare("
    INSERT INTO chats (cliente_id, departamento_id, origen, estado)
    VALUES (?, ?, 'chatbot', 'esperando_asesor')
");

$stmt3->bind_param("ii", $cliente_id, $departamento_id);
$stmt3->execute();

$chat_id = $stmt3->insert_id;

// ===================================================
// 4. Respuesta
// ===================================================
$response->success([
    "message" => "Chat creado correctamente",
    "chat_id" => $chat_id,
    "cliente_id" => $cliente_id,
    "departamento_id" => $departamento_id
]);
