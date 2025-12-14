<?php
// ===================================
// HEADERS CORS (CRÍTICO)
// ===================================
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight OPTIONS
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ===================================
// LOGGING PARA DEBUG
// ===================================
error_log("=== START_CHAT.PHP INICIADO ===");
error_log("REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]);
error_log("RAW INPUT: " . file_get_contents("php://input"));

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

// ===================================
// RECIBIR Y VALIDAR DATOS
// ===================================
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("ERROR JSON: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "JSON inválido"]);
    exit;
}

$nombre          = trim($data["nombre"] ?? "");
$dni             = trim($data["dni"] ?? "");
$telefono        = trim($data["telefono"] ?? "");
$departamento_id = intval($data["departamento_id"] ?? 1);

$ip_cliente      = $data["ip_cliente"] ?? null;
$mac_dispositivo = $data["mac_dispositivo"] ?? null;
$user_agent      = $data["user_agent"] ?? null;
$ciudad          = $data["ciudad"] ?? null;
$pais            = $data["pais"] ?? null;
$latitud         = $data["latitud"] ?? null;
$longitud        = $data["longitud"] ?? null;

if (!$nombre || !$dni || !$telefono) {
    error_log("ERROR: Datos incompletos - Nombre: $nombre, DNI: $dni, Tel: $telefono");
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Todos los datos son obligatorios"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // ===================================
    // VERIFICAR SI EL CLIENTE YA EXISTE
    // ===================================
    $stmt = $pdo->prepare("
        SELECT u.id AS usuario_id
        FROM usuarios u
        INNER JOIN clientes_detalles cd ON cd.usuario_id = u.id
        WHERE cd.dni = ? AND u.rol = 'cliente'
        LIMIT 1
    ");
    $stmt->execute([$dni]);
    $cliente_existente = $stmt->fetch();

    if ($cliente_existente) {
        // Cliente ya existe → actualizamos datos
        $cliente_id = $cliente_existente["usuario_id"];

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET nombre = ?, telefono = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $telefono, $cliente_id]);

        error_log("Cliente existente actualizado: ID $cliente_id");

    } else {
        // Crear nuevo cliente
        $email_fake = "temp_" . time() . "_" . rand(1000, 9999) . "@cliente.com";
        $password_fake = password_hash("temp123", PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, telefono, password, rol)
            VALUES (?, ?, ?, ?, 'cliente')
        ");
        $stmt->execute([$nombre, $email_fake, $telefono, $password_fake]);

        $cliente_id = $pdo->lastInsertId();

        // Guardar DNI
        $stmt = $pdo->prepare("
            INSERT INTO clientes_detalles (usuario_id, dni)
            VALUES (?, ?)
        ");
        $stmt->execute([$cliente_id, $dni]);

        error_log("Nuevo cliente creado: ID $cliente_id");
    }

    // ===================================
    // CREAR CHAT
    // ===================================
    $stmt = $pdo->prepare("
        INSERT INTO chats 
        (cliente_id, departamento_id, origen, estado,
         ip_cliente, mac_dispositivo, user_agent,
         ciudad, pais, latitud, longitud)
        VALUES 
        (?, ?, 'chatbot', 'esperando_asesor',
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

    error_log("Chat creado exitosamente: ID $chat_id");

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
    error_log("ERROR PDO en start_chat: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al crear el chat: " . $e->getMessage()
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("ERROR GENERAL en start_chat: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error inesperado: " . $e->getMessage()
    ]);
}
?>