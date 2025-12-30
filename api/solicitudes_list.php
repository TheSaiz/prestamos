<?php
session_start();
require_once '../backend/connection.php';

if (!isset($_SESSION['usuario_id'], $_SESSION['usuario_rol'])) {
    http_response_code(401);
    exit;
}

$usuario_id  = (int)$_SESSION['usuario_id'];
$usuario_rol = $_SESSION['usuario_rol'];

/*
 Mostrar SOLO solicitudes con documentación completa
*/

$where = "
    cd.docs_completos = 1
    AND cd.estado_validacion = 'en_revision'
";

/* Si es asesor, solo las asignadas o sin asignar */
if ($usuario_rol === 'asesor') {
    $where .= " AND (c.asesor_id = $usuario_id OR c.asesor_id IS NULL)";
}

$sql = "
SELECT
    COALESCE(c.id, 0) AS chat_id,

    cd.estado_validacion AS estado,
    NULL AS score,

    u.id AS cliente_id,
    cd.nombre_completo AS nombre,
    u.email,
    cd.telefono,

    cd.dni,
    cd.cuit,
    cd.banco,
    cd.cbu,

    cd.doc_dni_frente,
    cd.doc_dni_dorso,
    cd.doc_comprobante_ingresos,

    cd.calle,
    cd.numero,
    cd.localidad,
    cd.provincia,
    cd.codigo_postal,

    cd.tipo_ingreso,
    cd.monto_ingresos,
    cd.fecha_nacimiento,

    cd.docs_updated_at
FROM clientes_detalles cd
INNER JOIN usuarios u 
    ON u.id = cd.usuario_id
LEFT JOIN chats c 
    ON c.cliente_id = u.id
WHERE
    cd.docs_completos = 1
    AND cd.estado_validacion = 'en_revision'
" . (
    $usuario_rol === 'asesor'
        ? " AND (c.asesor_id = $usuario_id OR c.asesor_id IS NULL)"
        : ""
) . "
ORDER BY cd.docs_updated_at DESC
LIMIT 50
";


$stmt = $pdo->query($sql);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
