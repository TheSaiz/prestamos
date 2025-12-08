<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/utils.php";

header("Content-Type: application/json");

$db = new Database();
$conn = $db->connect();

// -------------------------------
// 1. Validar método
// -------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Método no permitido", 405);
}

$data = Utils::getJsonData();

if (!isset($data["chat_id"]) || !isset($data["problem_type"])) {
    Response::error("Faltan parámetros obligatorios: chat_id, problem_type");
}

$chat_id = $data["chat_id"];
$problem_type = strtolower(trim($data["problem_type"]));

// -------------------------------
// 2. Determinar el departamento
// -------------------------------
$departments = [
    "pago"       => "cobranzas",
    "cobranza"   => "cobranzas",
    "atraso"     => "cobranzas",
    "cuota"      => "cobranzas",

    "prestamo"   => "finanzas",
    "monto"      => "finanzas",
    "solicitud"  => "finanzas",

    "tecnico"    => "soporte",
    "error"      => "soporte",
    "bug"        => "soporte",

    "otro"       => "general"
];

$department = $departments["$problem_type"] ?? "general";

// -------------------------------
// 3. Actualizar el chat con el departamento
// -------------------------------
$query = $conn->prepare("UPDATE chats SET department = ?, status = 'waiting' WHERE id = ?");
$query->execute([$department, $chat_id]);

// -------------------------------
// 4. Buscar asesores disponibles de ese departamento
// -------------------------------
$q = $conn->prepare("SELECT id, name FROM asesores WHERE department = ? AND status = 'online'");
$q->execute([$department]);

$asesores = $q->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------
// 5. Respuesta final
// -------------------------------
Response::success("Departamento asignado correctamente", [
    "department" => $department,
    "asesores_disponibles" => $asesores
]);
