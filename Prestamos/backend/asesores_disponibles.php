<?php
require "connection.php";

$departamento_id = $_GET['departamento_id'] ?? null;

if (!$departamento_id) {
    echo json_encode(["error" => "departamento_id requerido"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.id, u.nombre, u.apellido
    FROM asesores_departamentos ad
    INNER JOIN usuarios u ON ad.asesor_id = u.id
    WHERE ad.departamento_id = ? AND ad.disponible = 1
");
$stmt->execute([$departamento_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
