<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/utils.php";

header("Content-Type: application/json");

$db = new Database();
$conn = $db->connect();

// ------------------------------------
// 1. Verificar método POST
// ------------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Método no permitido", 405);
}

$data = Utils::getJsonData();

if (!isset($data["chat_id"])) {
    Response::error("Falta el parámetro obligatorio: chat_id");
}

$chat_id = $data["chat_id"];

// ------------------------------------
// 2. Obtener departamento del chat
// ------------------------------------
$q = $conn->prepare("SELECT department FROM chats WHERE id = ?");
$q->execute([$chat_id]);
$chat = $q->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    Response::error("El chat no existe");
}

$department = $chat["department"];

// ------------------------------------
// 3. Obtener asesores disponibles del departamento
// ------------------------------------
$q = $conn->prepare("
    SELECT id, name 
    FROM asesores 
    WHERE department = ? AND status = 'online'
");
$q->execute([$department]);

$asesores = $q->fetchAll(PDO::FETCH_ASSOC);

if (count($asesores) === 0) {
    Response::success("No hay asesores disponibles", [
        "notificados" => [],
        "department" => $department
    ]);
}

// ------------------------------------
// 4. Registrar solicitud de chat para cada asesor
//    (Esto permite luego que el primero que acepte atienda)
// ------------------------------------
foreach ($asesores as $a) {
    $stmt = $conn->prepare("
        INSERT INTO chat_requests (chat_id, asesor_id, status, created_at)
        VALUES (?, ?, 'pending', NOW())
    ");
    $stmt->execute([$chat_id, $a["id"]]);
}

// ------------------------------------
// 5. Respuesta final
// ------------------------------------
Response::success("Asesores notificados correctamente", [
    "department" => $department,
    "notificados" => $asesores
]);
