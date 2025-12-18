<?php
// =====================================================
// get_pending_chats.php
// - Devuelve SOLO chats listos para asesores
// - Se notifican únicamente cuando el chatbot terminó
// =====================================================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../backend/connection.php";

// =====================================================
// INPUT
// =====================================================
$asesor_id = intval($_GET['asesor_id'] ?? 0);

if ($asesor_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "asesor_id requerido"
    ]);
    exit;
}

try {

    // =====================================================
    // SOLO CHATS QUE TERMINARON EL CHATBOT
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.cliente_id,
            c.departamento_id,
            c.fecha_inicio,
            u.nombre   AS cliente_nombre,
            u.telefono AS cliente_telefono,
            d.nombre   AS departamento_nombre
        FROM chats c
        INNER JOIN usuarios u ON u.id = c.cliente_id
        INNER JOIN departamentos d ON d.id = c.departamento_id
        WHERE
            c.estado = 'esperando_asesor'
            AND c.asesor_id IS NULL
        ORDER BY c.fecha_inicio ASC
    ");

    $stmt->execute();
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "chats" => $chats
        ]
    ]);

} catch (Throwable $e) {

    error_log("❌ get_pending_chats.php ERROR: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener chats pendientes"
    ]);
}
