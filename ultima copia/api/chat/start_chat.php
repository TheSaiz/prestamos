<?php
// ===================================
// HEADERS CORS
// ===================================
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ===================================
// LOG DEBUG
// ===================================
error_log("=== START_CHAT.PHP INICIADO ===");
error_log("REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]);

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);
    exit;
}

// ===================================
// LEER JSON
// ===================================
$rawInput = file_get_contents("php://input");
error_log("RAW INPUT: " . $rawInput);

$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("ERROR JSON: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "JSON inválido"
    ]);
    exit;
}

// ===================================
// DATOS RECIBIDOS
// ===================================
$nombre          = trim($data["nombre"] ?? "");
$departamento_id = intval($data["departamento_id"] ?? 1);

// Datos opcionales / tracking
$ip_cliente      = $data["ip_cliente"] ?? null;
$mac_dispositivo = $data["mac_dispositivo"] ?? null;
$user_agent      = $data["user_agent"] ?? null;
$ciudad          = $data["ciudad"] ?? null;
$pais            = $data["pais"] ?? null;
$latitud         = $data["latitud"] ?? null;
$longitud        = $data["longitud"] ?? null;

// ===================================
// VALIDACIÓN MÍNIMA
// ===================================
if ($nombre === "") {
    error_log("ERROR: Nombre vacío");
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "El nombre es obligatorio"
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    // ===================================
    // CREAR CLIENTE TEMPORAL
    // ===================================
    $email_fake = "temp_" . time() . "_" . rand(1000, 9999) . "@cliente.com";
    $password_fake = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, telefono, password, rol)
        VALUES (?, ?, NULL, ?, 'cliente')
    ");
    $stmt->execute([
        $nombre,
        $email_fake,
        $password_fake
    ]);

    $cliente_id = $pdo->lastInsertId();

    error_log("Cliente temporal creado: ID $cliente_id");

   // ===================================
// CREAR CHAT (FIX ESTADO INICIAL)
// ===================================
$stmt = $pdo->prepare("
    INSERT INTO chats
    (cliente_id, departamento_id, origen, estado,
     ip_cliente, mac_dispositivo, user_agent,
     ciudad, pais, latitud, longitud)
    VALUES
    (?, ?, 'chatbot', 'pendiente',
     ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $cliente_id,
    $departamento_id,
    $ip_cliente,
    $mac_dispositivo,
    $user_agent,
    $ciudad,
    $pais,
    $latitud,
    $longitud
]);

    $chat_id = $pdo->lastInsertId();

    $pdo->commit();

    error_log("Chat creado correctamente: ID $chat_id");

    echo json_encode([
        "success" => true,
        "message" => "Chat iniciado",
        "data" => [
            "chat_id" => $chat_id,
            "cliente_id" => $cliente_id,
            "departamento_id" => $departamento_id
        ]
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("ERROR START_CHAT: " . $e->getMessage());
    error_log($e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error interno al iniciar el chat"
    ]);
}
