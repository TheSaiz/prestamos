<?php
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_clientes.php');
    exit;
}

require_once __DIR__ . '/backend/connection.php';

$usuario_id = (int)$_SESSION['cliente_id'];

$stmt = $pdo->prepare("SELECT * FROM clientes_detalles WHERE usuario_id = ? LIMIT 1");
$stmt->execute([$usuario_id]);
$cli = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cli) {
    die('Cliente no encontrado');
}

function h($v){
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$enRevision = (
    ($cli['estado_validacion'] ?? '') === 'en_revision'
    || (int)($cli['docs_completos'] ?? 0) === 1
);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Documentación</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style_clientes.css">

<style>
.rev-banner{
  margin:14px 0 18px;
  padding:14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.10);
  background:rgba(124,92,255,.10);
  color:rgba(234,240,255,.92);
  display:flex;
  gap:12px;
  align-items:center;
}
.rev-clock{
  width:18px;height:18px;
  border:2px solid rgba(234,240,255,.45);
  border-top-color:rgba(110,231,255,.95);
  border-radius:50%;
  animation:spin 1s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}

.input-error{ border-color:#ff4d6d !important; }
.error-text{ color:#ff4d6d; font-size:.9rem; margin-top:6px; }
</style>
</head>

<body>
<div class="container">

<a class="back" href="dashboard_clientes.php">← Volver</a>

<h1>📄 Documentación</h1>
<p class="sub">Completá todos los datos. Un asesor validará tu información.</p>

<?php if ($enRevision): ?>
<div class="rev-banner">
  <div class="rev-clock"></div>
  <div>
    <div style="font-weight:800">Tu documentación está en revisión</div>
    <div style="opacity:.85">Ya recibimos tu información.</div>
  </div>
</div>
<?php endif; ?>

<form id="docForm" action="backend/documentacion/guardar.php" method="POST" enctype="multipart/form-data">

<!-- ================= DATOS PERSONALES ================= -->
<div class="section">
<h2>Datos personales</h2>

<input class="input" id="dni" name="dni" placeholder="DNI" required value="<?= h($cli['dni'] ?? '') ?>">
<input class="input" id="nombre_completo" name="nombre_completo" placeholder="Nombre completo" required value="<?= h($cli['nombre_completo'] ?? '') ?>">
<input class="input" id="cuit" name="cuit" placeholder="CUIT" required value="<?= h($cli['cuit'] ?? '') ?>">
<input class="input" name="telefono" placeholder="Teléfono" required value="<?= h($cli['telefono'] ?? '') ?>">

<label>Fecha de nacimiento</label>
<input class="input" type="date" name="fecha_nacimiento" value="<?= h($cli['fecha_nacimiento'] ?? '') ?>">
</div>

<!-- ================= DOCUMENTOS ================= -->
<div class="section">
<h2>Documentación</h2>

<label>DNI Frente</label>
<input type="file" name="doc_dni_frente" <?= empty($cli['doc_dni_frente']) ? 'required' : '' ?>>

<label>DNI Dorso</label>
<input type="file" name="doc_dni_dorso" <?= empty($cli['doc_dni_dorso']) ? 'required' : '' ?>>
</div>

<!-- ================= LABORAL ================= -->
<div class="section">
<h2>Información laboral</h2>

<select name="tipo_ingreso" required>
  <option value="">Tipo de ingreso</option>
  <?php
  $tipos = ['dependencia','monotributo','jubilacion','autonomo','negro'];
  foreach($tipos as $t){
    $sel = (($cli['tipo_ingreso'] ?? '') === $t) ? 'selected' : '';
    echo "<option value=\"$t\" $sel>".ucfirst($t)."</option>";
  }
  ?>
</select>

<input class="input" name="monto_ingresos" placeholder="Monto de ingresos"
       value="<?= h($cli['monto_ingresos'] ?? '') ?>">

<label>Comprobante de ingresos</label>
<input type="file" name="doc_comprobante_ingresos">
</div>

<!-- ================= BANCARIOS ================= -->
<div class="section">
<h2>Datos bancarios</h2>

<input class="input" name="banco" placeholder="Banco" required value="<?= h($cli['banco'] ?? '') ?>">

<input class="input" id="cbu" name="cbu"
       placeholder="CBU (22 dígitos)"
       maxlength="22"
       required
       value="<?= h($cli['cbu'] ?? '') ?>">

<div id="cbuError" class="error-text" style="display:none">
  El CBU debe tener exactamente 22 números
</div>
</div>

<!-- ================= DIRECCIÓN ================= -->
<div class="section">
<h2>Dirección</h2>

<input class="input" name="calle" placeholder="Calle" value="<?= h($cli['calle'] ?? '') ?>">
<input class="input" name="numero" placeholder="Número" value="<?= h($cli['numero'] ?? '') ?>">
<input class="input" name="localidad" placeholder="Localidad" value="<?= h($cli['localidad'] ?? '') ?>">
<input class="input" name="provincia" placeholder="Provincia" value="<?= h($cli['provincia'] ?? '') ?>">
<input class="input" name="codigo_postal" placeholder="Código Postal" value="<?= h($cli['codigo_postal'] ?? '') ?>">
</div>

<button class="btn btn-primary" type="submit">Enviar documentación</button>

</form>
</div>

<!-- ================= MODAL DNI (ORIGINAL) ================= -->
<div id="dniModal" class="overlay-gate">
  <div class="gate-box">
    <div class="gate-head">
      <div class="gate-title">Confirmación de identidad</div>
      <div class="gate-sub" id="dniModalText"></div>
    </div>
    <div class="gate-mid" id="dniOpciones"></div>
    <div class="gate-actions">
      <button type="button" class="btn btn-ghost" onclick="cerrarDniModal()">No soy</button>
      <button type="button" class="btn btn-primary" id="confirmarDniBtn">Sí, soy yo</button>
    </div>
  </div>
</div>

<!-- ================= JS ================= -->
<script>
/* ===== VALIDACIÓN CBU ===== */
const cbuInput = document.getElementById('cbu');
const cbuError = document.getElementById('cbuError');
const form = document.getElementById('docForm');

function validarCBU(){
  let v = (cbuInput.value || '').replace(/\D/g,'');
  cbuInput.value = v;

  if (v.length !== 22) {
    cbuInput.classList.add('input-error');
    cbuError.style.display = 'block';
    return false;
  }
  cbuInput.classList.remove('input-error');
  cbuError.style.display = 'none';
  return true;
}

cbuInput.addEventListener('input', validarCBU);
form.addEventListener('submit', e => {
  if (!validarCBU()) {
    e.preventDefault();
    cbuInput.focus();
  }
});

/* ===== DNI AUTOCOMPLETE (TU LÓGICA ORIGINAL) ===== */
const dniInput = document.getElementById('dni');
const nombreInput = document.getElementById('nombre_completo');
const cuitInput = document.getElementById('cuit');
let dniData = null;

dniInput.addEventListener('blur', () => {
  const dni = (dniInput.value || '').replace(/\D/g,'');
  if (dni.length < 7) return;

  fetch('/system/api/chatbot/validar_dni.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({dni})
  })
  .then(r => r.json())
  .then(data => {
    if (!data || !data.success) return;
    dniData = data;

    let html = '';
    (data.opciones || [{nombre:data.nombre, cuil:data.cuil}]).forEach((o,i)=>{
      html += `<label>
        <input type="radio" name="cuil_sel" value="${o.cuil}" ${i===0?'checked':''}>
        ${o.nombre} (${o.cuil})
      </label>`;
    });

    document.getElementById('dniModalText').innerHTML =
      `¿Sos <strong>${(data.nombre||'esta persona')}</strong>?`;

    document.getElementById('dniOpciones').innerHTML = html;
    document.getElementById('dniModal').classList.add('show');
  });
});

document.getElementById('confirmarDniBtn').onclick = () => {
  if (!dniData) return;
  const sel = document.querySelector('input[name="cuil_sel"]:checked');
  if (sel) cuitInput.value = sel.value;
  if (dniData.nombre) nombreInput.value = dniData.nombre;
  cerrarDniModal();
};

function cerrarDniModal(){
  document.getElementById('dniModal').classList.remove('show');
}
</script>

</body>
</html>
