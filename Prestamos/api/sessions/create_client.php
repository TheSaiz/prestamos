<?php
// /api/sessions/create_client.php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') response(false, "Método no permitido", null, 405);

$nombre = sanitize($_POST['nombre'] ?? '');
$dni = sanitize($_POST['dni'] ?? '');
$telefono = sanitize($_POST['telefono'] ?? '');

if (!$nombre || !$dni || !$telefono) response(false, "Faltan datos", null, 400);

// crear usuario temporal (similar a start_chat)
$fakeEmail = $dni . "@temp.chat";
$pw = password_hash("temp_".time(), PASSWORD_DEFAULT);
$ins = $conn->prepare("INSERT INTO usuarios (nombre, email, telefono, password, rol) VALUES (?, ?, ?, ?, 'cliente')");
$ins->bind_param("ssss", $nombre, $fakeEmail, $telefono, $pw);
if (!$ins->execute()) response(false, "Error creando cliente", null, 500);

$response_id = $ins->insert_id;
response(true, "Cliente creado", ["cliente_id"=>$response_id]);
?>
