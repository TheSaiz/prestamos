<?php
// =====================================================
// save_answer.php (PRODUCCIÓN - MODO ESPEJO)
// - Guarda respuestas del chatbot
// - Envía a API cuando se confirma el email
//   (con validación REAL de datos obligatorios)
// =====================================================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

// =====================================================
// LEER INPUT
// =====================================================
$data = $_POST;

if (empty($data)) {
    $json = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        $data = $json;
    }
}

// =====================================================
// INPUTS
// =====================================================
$chat_id     = intval($data["chat_id"] ?? 0);
$question_id = intval($data["question_id"] ?? 0);
$answer      = trim((string)($data["answer"] ?? ""));
$option_id   = intval($data["option_id"] ?? 0);

$cuil   = trim((string)($data["cuil"] ?? ""));
$nombre = trim((string)($data["nombre"] ?? ""));
$dni    = trim((string)($data["dni"] ?? ""));

$confirmado = !empty($data["confirmado"]);

if ($chat_id <= 0 || $question_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parámetros incompletos"]);
    exit;
}

if ($answer === "" && $option_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Respuesta vacía"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $disparar_envio_riesgo = false;

    // =====================================================
    // 1) CHAT
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT id, cliente_id, estado
        FROM chats
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$chat_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        throw new Exception("Chat no encontrado");
    }

    if (in_array($chat["estado"], ["esperando_asesor", "en_conversacion", "cerrado"], true)) {
        $pdo->commit();
        echo json_encode(["success" => true, "ignored" => true]);
        exit;
    }

    $cliente_id = intval($chat["cliente_id"]);
    if ($cliente_id <= 0) {
        throw new Exception("Chat sin cliente asociado");
    }

    // =====================================================
    // 2) ASEGURAR CLIENTE
    // =====================================================
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
    $stmt->execute([$cliente_id]);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO clientes (usuario_id, email, telefono)
            VALUES (?, ?, '')
        ");
        $stmt->execute([
            $cliente_id,
            "temp_" . time() . "_" . rand(1000,9999) . "@cliente.com"
        ]);
    }

    // =====================================================
    // 3) GUARDAR RESPUESTA
    // =====================================================
    $stmt = $pdo->prepare("
        INSERT INTO chatbot_respuestas (chat_id, pregunta_id, respuesta, opcion_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $chat_id,
        $question_id,
        $answer,
        $option_id > 0 ? $option_id : null
    ]);

    // =====================================================
    // 4) DNI / CUIL / NOMBRE (ID 2)
    // =====================================================
    if ($question_id === 2) {

        if ($dni !== "") {
            $stmt = $pdo->prepare("
                INSERT INTO clientes_detalles (usuario_id, dni)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE dni = VALUES(dni)
            ");
            $stmt->execute([$cliente_id, $dni]);
        }

        if ($cuil || $nombre) {
            $stmt = $pdo->prepare("
                UPDATE chats
                SET cuil_validado = COALESCE(NULLIF(?, ''), cuil_validado),
                    nombre_validado = COALESCE(NULLIF(?, ''), nombre_validado)
                WHERE id = ?
            ");
            $stmt->execute([$cuil, $nombre, $chat_id]);
        }

        if ($nombre) {
            $pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?")
                ->execute([$nombre, $cliente_id]);
        }
    }

    // =====================================================
    // 5) TELÉFONO (ID 5)
    // =====================================================
    if ($question_id === 5 && $answer !== "") {

        $stmt = $pdo->prepare("
            SELECT respuesta
            FROM chatbot_respuestas
            WHERE chat_id = ? AND pregunta_id = 4
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$chat_id]);
        $area = preg_replace('/\D+/', '', (string)$stmt->fetchColumn());

        $phone = preg_replace('/\D+/', '', $answer);

        if ($area && $phone) {
            $tel = $area . $phone;
            $pdo->prepare("UPDATE usuarios SET telefono = ? WHERE id = ?")
                ->execute([$tel, $cliente_id]);
            $pdo->prepare("UPDATE clientes SET telefono = ? WHERE usuario_id = ?")
                ->execute([$tel, $cliente_id]);
        }
    }

    // =====================================================
    // 6) EMAIL (ID 8)
    // =====================================================
    if ($question_id === 8 && filter_var($answer, FILTER_VALIDATE_EMAIL)) {

        $stmt = $pdo->prepare("
            SELECT id FROM usuarios
            WHERE email = ? AND id <> ?
            LIMIT 1
        ");
        $stmt->execute([$answer, $cliente_id]);

        if ($stmt->fetch()) {
            $pdo->commit();
            echo json_encode(["success" => false, "message" => "Email duplicado"]);
            exit;
        }

        $pdo->prepare("UPDATE usuarios SET email = ? WHERE id = ?")
            ->execute([$answer, $cliente_id]);
        $pdo->prepare("UPDATE clientes SET email = ? WHERE usuario_id = ?")
            ->execute([$answer, $cliente_id]);

        if ($confirmado) {
            $disparar_envio_riesgo = true;
        }
    }

    // =====================================================
    // 7) SITUACIÓN LABORAL (ID 3)
    // =====================================================
    if ($question_id === 3) {
        $a = mb_strtolower($answer);
        $map = [
            "auh" => "COBRA_AUH",
            "suaf" => "COBRA_SUAF",
            "jubil" => "JUBILADO",
            "depend" => "RELACION_DEPENDENCIA",
            "mono" => "MONO_RESPONSABLE",
            "negro" => "COBRA_NEGRO"
        ];
        foreach ($map as $k => $v) {
            if (str_contains($a, $k)) {
                $pdo->prepare("UPDATE chats SET situacion_laboral = ? WHERE id = ?")
                    ->execute([$v, $chat_id]);
                break;
            }
        }
    }

    // =====================================================
    // 8) BANCO (ID 7)  ✅ ID NUMÉRICO PARA CRM
    // =====================================================
    if ($question_id === 7 && $option_id > 0) {
        $pdo->prepare("UPDATE chats SET banco = ? WHERE id = ?")
            ->execute([$option_id, $chat_id]);
    }

    $pdo->commit();

    // =====================================================
    // 9) ENVÍO API – VALIDACIÓN REAL
    // =====================================================
    if ($disparar_envio_riesgo) {

        $stmt = $pdo->prepare("
            SELECT c.banco, c.situacion_laboral, c.cuil_validado, c.nombre_validado,
                   u.email, u.telefono
            FROM chats c
            JOIN usuarios u ON u.id = c.cliente_id
            WHERE c.id = ?
        ");
        $stmt->execute([$chat_id]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        $faltantes = [];
        foreach (["banco","situacion_laboral","cuil_validado","nombre_validado","email","telefono"] as $k) {
            if (empty($d[$k])) $faltantes[] = $k;
        }

        if (!$faltantes) {
            require_once __DIR__ . "/../send_riesgo_api.php";
            enviarDatosRiesgo($chat_id);
        }
    }

    echo json_encode(["success" => true]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("save_answer ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false]);
}
