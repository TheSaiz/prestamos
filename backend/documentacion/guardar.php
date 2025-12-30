<?php
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../login_clientes.php');
    exit;
}

require_once __DIR__ . '/../connection.php';

$usuario_id = (int)($_SESSION['cliente_id'] ?? 0);
if ($usuario_id <= 0) {
    header('Location: ../../login_clientes.php');
    exit;
}

/* =========================
   HELPERS
========================= */
function limpiar($v){
    return trim((string)($v ?? ''));
}
function soloNumeros($v){
    return preg_replace('/\D+/', '', (string)$v);
}
function subirArchivo($campo, $usuario_id){
    if (!isset($_FILES[$campo]) || !is_array($_FILES[$campo])) return null;
    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) return null;

    $maxBytes = 8 * 1024 * 1024;
    if ($_FILES[$campo]['size'] > $maxBytes) return null;

    $permitidos = ['image/jpeg','image/png','application/pdf'];

    $tmp = $_FILES[$campo]['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    if (!in_array($mime, $permitidos, true)) return null;

    $baseDir = __DIR__ . '/../../uploads/clientes/' . $usuario_id;
    if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

    $ext = match($mime){
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'application/pdf' => 'pdf',
        default => 'dat'
    };

    $nombre = $campo.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
    $destino = $baseDir.'/'.$nombre;

    if (move_uploaded_file($tmp, $destino)) {
        return 'uploads/clientes/'.$usuario_id.'/'.$nombre;
    }
    return null;
}

/* =========================
   POST
========================= */
$dni   = soloNumeros($_POST['dni'] ?? '');
$cuit  = soloNumeros($_POST['cuit'] ?? '');
$nombre = limpiar($_POST['nombre_completo'] ?? '');
$telefono = soloNumeros($_POST['telefono'] ?? '');
$fecha_nacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');

$banco = limpiar($_POST['banco'] ?? '');

$cbu_raw = soloNumeros($_POST['cbu'] ?? '');
$cbu = (strlen($cbu_raw) === 22) ? $cbu_raw : null;

$tipo_ingreso = limpiar($_POST['tipo_ingreso'] ?? '');
$monto_ingresos = limpiar($_POST['monto_ingresos'] ?? '');

$calle = limpiar($_POST['calle'] ?? '');
$numero = limpiar($_POST['numero'] ?? '');
$localidad = limpiar($_POST['localidad'] ?? '');
$provincia = limpiar($_POST['provincia'] ?? '');
$codigo_postal = limpiar($_POST['codigo_postal'] ?? '');

$direccion = trim($calle.' '.$numero);
if ($direccion === '') $direccion = null;
$ciudad = ($localidad !== '') ? $localidad : null;

/* =========================
   VALIDACIONES
========================= */
$errores = [];

if (strlen($dni) < 7 || strlen($dni) > 8) $errores[] = 'DNI inválido';
if (strlen($cuit) < 10 || strlen($cuit) > 11) $errores[] = 'CUIT inválido';
if ($nombre === '') $errores[] = 'Nombre requerido';
if ($telefono === '') $errores[] = 'Teléfono requerido';
if ($banco === '') $errores[] = 'Banco requerido';
if ($cbu === null) $errores[] = 'CBU inválido';

if (!empty($errores)) {
    die('❌ Error: '.htmlspecialchars(implode(' / ', $errores)));
}

/* =========================
   ARCHIVOS
========================= */
$doc_dni_frente = subirArchivo('doc_dni_frente', $usuario_id);
$doc_dni_dorso  = subirArchivo('doc_dni_dorso', $usuario_id);
$doc_comprobante = subirArchivo('doc_comprobante_ingresos', $usuario_id);
$doc_cbu = subirArchivo('doc_cbu', $usuario_id); // 🔥 ESTE FALTABA

/* =========================
   UPSERT
========================= */
try {
    $pdo->beginTransaction();

    $sql = "
INSERT INTO clientes_detalles SET
    usuario_id = :usuario_id,
    dni = :dni,
    cuit = :cuit,
    nombre_completo = :nombre,
    telefono = :telefono,
    fecha_nacimiento = :fecha_nacimiento,
    direccion = :direccion,
    ciudad = :ciudad,
    provincia = :provincia,
    tipo_ingreso = :tipo_ingreso,
    monto_ingresos = :monto_ingresos,
    banco = :banco,
    cbu = :cbu,
    calle = :calle,
    numero = :numero,
    localidad = :localidad,
    codigo_postal = :codigo_postal,
    doc_dni_frente = COALESCE(:doc_dni_frente, doc_dni_frente),
    doc_dni_dorso = COALESCE(:doc_dni_dorso, doc_dni_dorso),
    doc_comprobante_ingresos = COALESCE(:doc_comprobante, doc_comprobante_ingresos),
    doc_cbu = COALESCE(:doc_cbu, doc_cbu),
    docs_completos = 1,
    estado_validacion = 'en_revision',
    docs_updated_at = NOW()
ON DUPLICATE KEY UPDATE
    dni = VALUES(dni),
    cuit = VALUES(cuit),
    nombre_completo = VALUES(nombre_completo),
    telefono = VALUES(telefono),
    fecha_nacimiento = VALUES(fecha_nacimiento),
    direccion = VALUES(direccion),
    ciudad = VALUES(ciudad),
    provincia = VALUES(provincia),
    tipo_ingreso = VALUES(tipo_ingreso),
    monto_ingresos = VALUES(monto_ingresos),
    banco = VALUES(banco),
    cbu = VALUES(cbu),
    calle = VALUES(calle),
    numero = VALUES(numero),
    localidad = VALUES(localidad),
    codigo_postal = VALUES(codigo_postal),
    doc_dni_frente = COALESCE(VALUES(doc_dni_frente), doc_dni_frente),
    doc_dni_dorso = COALESCE(VALUES(doc_dni_dorso), doc_dni_dorso),
    doc_comprobante_ingresos = COALESCE(VALUES(doc_comprobante_ingresos), doc_comprobante_ingresos),
    doc_cbu = COALESCE(VALUES(doc_cbu), doc_cbu),
    docs_completos = 1,
    estado_validacion = 'en_revision',
    docs_updated_at = NOW()
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':dni' => $dni,
        ':cuit' => $cuit,
        ':nombre' => $nombre,
        ':telefono' => $telefono,
        ':fecha_nacimiento' => $fecha_nacimiento ?: null,
        ':direccion' => $direccion,
        ':ciudad' => $ciudad,
        ':provincia' => $provincia ?: null,
        ':tipo_ingreso' => $tipo_ingreso,
        ':monto_ingresos' => $monto_ingresos ?: null,
        ':banco' => $banco,
        ':cbu' => $cbu,
        ':calle' => $calle ?: null,
        ':numero' => $numero ?: null,
        ':localidad' => $localidad ?: null,
        ':codigo_postal' => $codigo_postal ?: null,
        ':doc_dni_frente' => $doc_dni_frente,
        ':doc_dni_dorso' => $doc_dni_dorso,
        ':doc_comprobante' => $doc_comprobante,
        ':doc_cbu' => $doc_cbu
    ]);

    $pdo->commit();

    $_SESSION['docs_flash'] = 'en_revision';
    header("Location: ../../dashboard_clientes.php?docs=en_revision");
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die('❌ Error al guardar: '.$e->getMessage());
}
