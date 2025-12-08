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
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response->error("Método no permitido", 405);
}

// =============================
// Datos recibidos
// =============================
$chat_id      = $_POST["chat_id"]      ?? null;
$question_id  = $_POST["question_id"]  ?? null;
$answer       = $_POST["answer"]       ?? null;
$option_id    = $_POST["option_id"]    ?? null; // si es opción

if (!$chat_id || !$question_id || !$answer) {
    $response->error("Parámetros incompletos");
}

// =============================
// Validar que el chat exista
// =============================
$stmt = $conn->prepare("SELECT * FROM chats WHERE id = ?");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$chat = $stmt->get_result()->fetch_assoc();

if (!$chat) {
    $response->error("El chat no existe");
}

// =============================
// Guardar la respuesta en mensajes
// (emisor = bot o cliente según implem. futura)
// =============================
$stmt = $conn->prepare("
    INSERT INTO mensajes (chat_id, emisor, usuario_id, mensaje)
    VALUES (?, 'cliente', NULL, ?)
");

$stmt->bind_param("is", $chat_id, $answer);
$stmt->execute();

// =====================================================
// Si ES una opción → determinar departamento
// =====================================================
$departamento_id = null;

if ($option_id) {
    $stmt = $conn->prepare("
        SELECT departamento_id 
        FROM chatbot_opciones 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $option_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $departamento_id = intval($res["departamento_id"]);

        // actualizar chat con el departamento elegido
        $stmt2 = $conn->prepare("
            UPDATE chats SET departamento_id = ? WHERE id = ?
        ");
        $stmt2->bind_param("ii", $departamento_id, $chat_id);
        $stmt2->execute();
    }
}

// =============================
// Respuesta final
// =============================
$response->success([
    "saved" => true,
    "chat_id" => $chat_id,
    "question_id" => $question_id,
    "departamento_detectado" => $departamento_id
]);
