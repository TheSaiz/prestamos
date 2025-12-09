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

// ============================
// DATOS PERSONALES DEL CLIENTE
// ============================
$nombre = trim($data["nombre"] ?? "");
$dni = trim($data["dni"] ?? "");
$telefono = trim($data["telefono"] ?? "");
$departamento_id = intval($data["departamento_id"] ?? 1);

// ============================
// DATOS TÉCNICOS DEL CLIENTE
// ============================
$ip_cliente      = $data["ip_cliente"] ?? null;
$mac_dispositivo = $data["mac_dispositivo"] ?? null;
$user_agent      = $data["user_agent"] ?? null;

$ciudad   = $data["ciudad"] ?? null;
$pais     = $data["pais"] ?? null;
$latitud  = $data["latitud"] ?? null;
$longitud = $data["longitud"] ?? null;

if (!$nombre || !$dni || !$telefono) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Todos los datos son obligatorios"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // VERIFICAR SI YA EXISTE UN CLIENTE CON ESE DNI
    $stmt = $pdo->prepare("
        SELECT u.id 
        FROM usuarios u
        INNER JOIN clientes_detalles cd ON cd.usuario_id = u.id
        WHERE cd.dni = ? AND u.rol = 'cliente'
        LIMIT 1
    ");
    $stmt->execute([$dni]);
    $cliente_existente = $stmt->fetch();

    if ($cliente_existente) {
        // Cliente ya existe, usar su ID
        $cliente_id = $cliente_existente['id'];
        
        // Actualizar nombre y teléfono por si cambió
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nombre = ?, telefono = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $telefono, $cliente_id]);
        
    } else {
        // CREAR NUEVO USUARIO CLIENTE
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, telefono, password, rol)
            VALUES (?, ?, ?, ?, 'cliente')
        ");

        $email_fake = "temp_" . time() . "_" . rand(1000,9999) . "@cliente.com";
        $password_fake = password_hash("temp123", PASSWORD_DEFAULT);

        $stmt->execute([$nombre, $email_fake, $telefono, $password_fake]);
        $cliente_id = $pdo->lastInsertId();

        // GUARDAR DNI
        $stmt2 = $pdo->prepare("
            INSERT INTO clientes_detalles (usuario_id, dni)
            VALUES (?, ?)
        ");
        $stmt2->execute([$cliente_id, $dni]);
    }

    // CREAR CHAT + DATOS TÉCNICOS
    $stmt3 = $pdo->prepare("
        INSERT INTO chats 
        (cliente_id, departamento_id, origen, estado,
         ip_cliente, mac_dispositivo, user_agent,
         ciudad, pais, latitud, longitud)
        VALUES 
        (?, ?, 'chatbot', 'esperando_asesor',
         ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt3->execute([
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
        "message" => "Error al crear el chat",
        "error" => $e->getMessage()
    ]);
}
?>
