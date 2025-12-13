<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../backend/connection.php";

$asesor_id = intval($_GET['asesor_id'] ?? 0);

if (!$asesor_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "asesor_id requerido"]);
    exit;
}

try {
    // Obtener departamentos del asesor que están disponibles
    $stmt = $pdo->prepare("
        SELECT departamento_id 
        FROM asesores_departamentos 
        WHERE asesor_id = ? AND disponible = 1
    ");
    $stmt->execute([$asesor_id]);
    $departamentos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($departamentos)) {
        echo json_encode(["success" => true, "data" => ["chats" => []]]);
        exit;
    }

    // Obtener chats en espera de esos departamentos
    $placeholders = implode(',', array_fill(0, count($departamentos), '?'));

    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.cliente_id,
            c.departamento_id,
            c.fecha_inicio,
            u.nombre AS cliente_nombre,
            u.telefono AS cliente_telefono,
            d.nombre AS departamento_nombre
        FROM chats c
        INNER JOIN usuarios u 
            ON c.cliente_id = u.id
        INNER JOIN departamentos d 
            ON c.departamento_id = d.id
        WHERE c.departamento_id IN ($placeholders)
          AND c.estado = 'esperando_asesor'
          AND c.asesor_id IS NULL
        ORDER BY c.fecha_inicio ASC
    ");

    $stmt->execute($departamentos);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => ["chats" => $chats]
    ]);

} catch (PDOException $e) {
    error_log("Error en get_pending_chats: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener chats"
    ]);
}