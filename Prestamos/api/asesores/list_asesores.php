<?php
header("Content-Type: application/json");
require_once "../../backend/connection.php";

$asesor_actual = intval($_GET["asesor_id"] ?? 0);

try {
    $stmt = $pdo->prepare("
        SELECT a.id, a.nombre,
               GROUP_CONCAT(d.nombre SEPARATOR ', ') AS departamentos
        FROM asesores a
        LEFT JOIN asesores_departamentos ad ON ad.asesor_id = a.id
        LEFT JOIN departamentos d ON d.id = ad.departamento_id
        WHERE a.id != ?
        GROUP BY a.id
    ");
    $stmt->execute([$asesor_actual]);

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $resultado // importante: si está vacío, igualmente es success:true
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error interno"
    ]);
}
