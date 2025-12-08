<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";

$response = new Response();
$db = new Database();
$conn = $db->connect();

// =============================
// Validar método HTTP
// =============================
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    $response->error("Método no permitido", 405);
}

// =============================
// Leer parámetros
// =============================
$last_id = isset($_GET["last_id"]) ? intval($_GET["last_id"]) : null;

// ===============================================
// 1. Si NO hay last_id → devolver la primera pregunta
// ===============================================
if (!$last_id) {
    $sql = "SELECT * FROM chatbot_flujo ORDER BY id ASC LIMIT 1";
} else {
    $sql = "SELECT * FROM chatbot_flujo WHERE id > $last_id ORDER BY id ASC LIMIT 1";
}

$result = $conn->query($sql);

// Si no hay próxima pregunta → fin del chatbot
if ($result->num_rows === 0) {
    $response->success([
        "finished" => true,
        "message" => "No hay más preguntas"
    ]);
}

$question = $result->fetch_assoc();
$question_id = $question["id"];
$tipo = $question["tipo"];

// ===============================================
// 2. Si la pregunta es de tipo OPCIÓN → obtener opciones
// ===============================================
$options = [];

if ($tipo === "opcion") {
    $stmt = $conn->prepare("
        SELECT id, texto, departamento_id 
        FROM chatbot_opciones 
        WHERE flujo_id = ?
    ");

    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $options_result = $stmt->get_result();

    while ($row = $options_result->fetch_assoc()) {
        $options[] = $row;
    }
}

// ===============================================
// 3. Respuesta final
// ===============================================
$response->success([
    "finished" => false,
    "question" => [
        "id" => intval($question["id"]),
        "pregunta" => $question["pregunta"],
        "tipo" => $question["tipo"],
        "options" => $options
    ]
]);
