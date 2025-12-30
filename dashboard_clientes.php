<?php

session_start();

/* =========================
   PROD SAFE DEBUG (NO UI)
========================= */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$logFile = __DIR__ . '/logs/dashboard_clientes.log';
if (!is_dir(dirname($logFile))) {
    @mkdir(dirname($logFile), 0755, true);
}
function dlog($msg) {
    global $logFile;
    $ts = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$ts] $msg\n", FILE_APPEND);
}
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/* =========================
   VALIDACIÓN DE SESIÓN
========================= */
if (!isset($_SESSION['cliente_id']) || !isset($_SESSION['cliente_email'])) {
    header('Location: login_clientes.php');
    exit;
}

$cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
if ($cliente_id <= 0) {
    dlog("ERROR: cliente_id inválido en sesión.");
    header('Location: login_clientes.php');
    exit;
}

/* =========================
   DB
========================= */
try {
    require_once __DIR__ . '/backend/connection.php';
} catch (Throwable $e) {
    dlog("ERROR loading connection.php: " . $e->getMessage());
    header('Location: login_clientes.php');
    exit;
}
if (!isset($pdo)) {
    dlog("ERROR: \$pdo no existe. Revisar backend/connection.php");
    header('Location: login_clientes.php');
    exit;
}

/* =========================
   DATA
========================= */
$cliente_info = null;
$prestamos = [];
$cuotas = [];
$notificaciones = [];

/* 🔒 NO se quita nada del array original */
$docs = [
  'doc_dni_frente' => false,
  'doc_dni_dorso' => false,
  'doc_selfie' => false,
  'doc_comprobante_ingresos' => false,
  'doc_cbu' => false,
];

$docs_completos = false;
$estado_validacion = 'pendiente';

try {
    // Info cliente + documentación
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.nombre,
            u.apellido,
            u.email,
            cd.dni,
            cd.cuit,
            cd.banco,
            cd.cbu,
            cd.doc_dni_frente,
            cd.doc_dni_dorso,
            cd.doc_selfie,
            cd.doc_comprobante_ingresos,
            cd.doc_cbu,
            cd.docs_completos,
            cd.estado_validacion
        FROM usuarios u
        LEFT JOIN clientes_detalles cd ON cd.usuario_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$cliente_id]);
    $cliente_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no existe fila en clientes_detalles, crearla
    if (!$cliente_info || !array_key_exists('docs_completos', $cliente_info)) {
        $chk = $pdo->prepare("SELECT 1 FROM clientes_detalles WHERE usuario_id = ? LIMIT 1");
        $chk->execute([$cliente_id]);
        if (!$chk->fetchColumn()) {
            $ins = $pdo->prepare("INSERT INTO clientes_detalles (usuario_id) VALUES (?)");
            $ins->execute([$cliente_id]);
        }
        $stmt->execute([$cliente_id]);
        $cliente_info = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       CHECK DE DOCUMENTOS (VISUAL)
    ========================= */
    $docs['doc_dni_frente'] = !empty($cliente_info['doc_dni_frente']);
    $docs['doc_dni_dorso']  = !empty($cliente_info['doc_dni_dorso']);
    $docs['doc_selfie']     = !empty($cliente_info['doc_selfie']);
    $docs['doc_comprobante_ingresos'] = !empty($cliente_info['doc_comprobante_ingresos']);
    $docs['doc_cbu']        = !empty($cliente_info['doc_cbu']);

    /* =========================
       REGLA REAL DE COMPLETITUD (FIX)
       ⛔ NO exige doc_selfie / doc_cbu
    ========================= */
    $docs_completos = (
        !empty($cliente_info['doc_dni_frente']) &&
        !empty($cliente_info['doc_dni_dorso']) &&
        !empty($cliente_info['dni']) &&
        !empty($cliente_info['cuit']) &&
        !empty($cliente_info['banco']) &&
        !empty($cliente_info['cbu'])
    );

    $estado_validacion = $cliente_info['estado_validacion'] ?? 'pendiente';

    /* =========================
       SINCRONIZAR FLAG EN BD
    ========================= */
    $flag = (int)($cliente_info['docs_completos'] ?? 0);
    $nuevoFlag = $docs_completos ? 1 : 0;

    if ($flag !== $nuevoFlag) {
        $nuevoEstado = $docs_completos ? 'en_revision' : 'pendiente';
        $up = $pdo->prepare("
            UPDATE clientes_detalles
            SET docs_completos = ?, estado_validacion = ?, docs_updated_at = NOW()
            WHERE usuario_id = ?
        ");
        $up->execute([$nuevoFlag, $nuevoEstado, $cliente_id]);
        $estado_validacion = $nuevoEstado;
    }

    /* =========================
       PRÉSTAMOS
    ========================= */
    $stmt = $pdo->prepare("SELECT * FROM prestamos WHERE usuario_id = ? ORDER BY id DESC");
    $stmt->execute([$cliente_id]);
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       CUOTAS
    ========================= */
    $stmt = $pdo->prepare("
        SELECT c.*
        FROM cuotas c
        INNER JOIN prestamos p ON p.id = c.prestamo_id
        WHERE p.usuario_id = ?
        ORDER BY c.fecha_vencimiento ASC
        LIMIT 20
    ");
    $stmt->execute([$cliente_id]);
    $cuotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       NOTIFICACIONES
    ========================= */
    $stmt = $pdo->prepare("
        SELECT *
        FROM notificaciones_clientes
        WHERE usuario_id = ?
        ORDER BY id DESC
        LIMIT 20
    ");
    $stmt->execute([$cliente_id]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    dlog("EXCEPTION: " . $e->getMessage());
}

/* =========================
   UI VARS
========================= */
$nombreMostrar = trim(($cliente_info['nombre'] ?? '') . ' ' . ($cliente_info['apellido'] ?? ''));
if ($nombreMostrar === '') $nombreMostrar = 'Cliente';

$docs_total = count($docs);
$docs_ok = 0;
foreach ($docs as $v) { if ($v) $docs_ok++; }
$progress = $docs_total > 0 ? (int)round(($docs_ok / $docs_total) * 100) : 0;

/* =========================
   CALCULAR STATS PARA DASHBOARD
========================= */
$prestamos_activos = 0;
$total_adeudado = 0;
$cuotas_vencidas = 0;
$proximos = [];

foreach ($prestamos as $p) {
    $estado = strtolower($p['estado'] ?? '');
    if ($estado === 'activo' || $estado === 'aprobado' || $estado === 'vigente') {
        $prestamos_activos++;
    }
}

foreach ($cuotas as $c) {
    $monto = (float)($c['monto'] ?? 0);
    $estado = strtolower($c['estado'] ?? '');
    
    if ($estado === 'pendiente' || $estado === 'vencida') {
        $total_adeudado += $monto;
    }
    
    if ($estado === 'vencida') {
        $cuotas_vencidas++;
    }
    
    // Próximos vencimientos (próximas 5 cuotas pendientes)
    if ($estado === 'pendiente' && count($proximos) < 5) {
        $proximos[] = $c;
    }
}

/* =========================
   DETERMINAR TIPO DE GATE Y NOTIFICACIONES
========================= */
$mostrar_gate = false;
$gate_tipo = 'pendiente';
$mostrar_notificacion = false;
$notificacion_tipo = '';
$pagina_activa = 'dashboard';

if (!$docs_completos) {
    $mostrar_gate = true;
    $gate_tipo = 'pendiente';
} elseif ($estado_validacion === 'en_revision' || $estado_validacion === 'pendiente') {
    $mostrar_gate = true;
    $gate_tipo = 'en_revision';
} elseif ($estado_validacion === 'aprobado') {
    // Documentos aprobados - mostrar notificación verde
    $mostrar_notificacion = true;
    $notificacion_tipo = 'aprobado';
} elseif ($estado_validacion === 'rechazado') {
    // Documentos rechazados - mostrar notificación roja
    $mostrar_notificacion = true;
    $notificacion_tipo = 'rechazado';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mi Portal - Préstamo Líder</title>
  <link rel="stylesheet" href="style_clientes.css">
  <style>
    /* Animación del reloj de arena */
    .hourglass {
      width: 40px;
      height: 40px;
      position: relative;
      animation: hourglass-rotate 2s infinite ease-in-out;
    }
    
    .hourglass svg {
      width: 100%;
      height: 100%;
      fill: #7c5cff;
    }
    
    @keyframes hourglass-rotate {
      0%, 100% { transform: rotate(0deg); }
      50% { transform: rotate(180deg); }
    }

    /* Badge de estado al lado del nombre */
    .nombre-con-badge {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .estado-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      font-size: 14px;
      font-weight: bold;
    }

    .estado-badge.aprobado {
      background: #10b981;
      color: white;
    }

    .estado-badge.rechazado {
      background: #ef4444;
      color: white;
    }

    /* Notificación flotante */
    .notificacion-flotante {
      position: fixed;
      top: 20px;
      right: 20px;
      max-width: 400px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      padding: 20px;
      z-index: 9999;
      display: none;
      animation: slideInRight 0.4s ease-out;
    }

    .notificacion-flotante.show {
      display: block;
    }

    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .notificacion-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }

    .notificacion-icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      flex-shrink: 0;
    }

    .notificacion-icon.aprobado {
      background: #dcfce7;
      color: #166534;
    }

    .notificacion-icon.rechazado {
      background: #fee2e2;
      color: #991b1b;
    }

    .notificacion-titulo {
      font-size: 18px;
      font-weight: bold;
      color: #1f2937;
    }

    .notificacion-mensaje {
      color: #6b7280;
      font-size: 14px;
      line-height: 1.5;
      margin-bottom: 16px;
    }

    .notificacion-acciones {
      display: flex;
      gap: 8px;
    }

    .notificacion-btn {
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: all 0.2s;
    }

    .notificacion-btn.primary {
      background: #7c5cff;
      color: white;
    }

    .notificacion-btn.primary:hover {
      background: #6a4ae6;
    }

    .notificacion-btn.secondary {
      background: #f3f4f6;
      color: #4b5563;
    }

    .notificacion-btn.secondary:hover {
      background: #e5e7eb;
    }

    .notificacion-close {
      position: absolute;
      top: 12px;
      right: 12px;
      background: none;
      border: none;
      font-size: 20px;
      color: #9ca3af;
      cursor: pointer;
      padding: 4px;
      line-height: 1;
    }

    .notificacion-close:hover {
      color: #4b5563;
    }

    @media (max-width: 768px) {
      .notificacion-flotante {
        left: 20px;
        right: 20px;
        max-width: none;
      }
    }
  </style>
</head>
<body>

<div class="app">

  <?php include __DIR__ . '/sidebar_clientes.php'; ?>

  <main class="main">

    <?php if ($pagina_activa === 'dashboard'): ?>

      <div class="page-header">
        <div class="page-title">Dashboard</div>
        <div class="page-sub nombre-con-badge">
          <span>Bienvenido, <?php echo h($cliente_info['nombre'] ?? ''); ?>!</span>
          <?php if ($estado_validacion === 'aprobado'): ?>
            <span class="estado-badge aprobado" title="Documentos aprobados">✓</span>
          <?php elseif ($estado_validacion === 'rechazado'): ?>
            <span class="estado-badge rechazado" title="Documentos rechazados">✗</span>
          <?php endif; ?>
        </div>
      </div>

      <section class="stats">
        <div class="stat blue">
          <div>
            <div class="label">Préstamos Activos</div>
            <div class="value"><?php echo (int)$prestamos_activos; ?></div>
          </div>
          <div class="ico blue" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
        </div>

        <div class="stat yellow">
          <div>
            <div class="label">Total Adeudado</div>
            <div class="value">$<?php echo number_format((float)$total_adeudado, 0, ',', '.'); ?></div>
          </div>
          <div class="ico yellow" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>

        <div class="stat red">
          <div>
            <div class="label">Cuotas Vencidas</div>
            <div class="value"><?php echo (int)$cuotas_vencidas; ?></div>
          </div>
          <div class="ico red" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
        </div>
      </section>

      <section class="section">
        <h2>Próximos Vencimientos</h2>

        <?php if (empty($proximos)): ?>
          <div class="empty">No tenés pagos pendientes 🎉</div>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Vence</th>
                <th>Monto</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($proximos as $c): ?>
                <tr>
                  <td><?php echo h($c['fecha_vencimiento'] ?? ''); ?></td>
                  <td>$<?php echo number_format((float)($c['monto'] ?? 0), 0, ',', '.'); ?></td>
                  <td><?php echo h($c['estado'] ?? ''); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>

      <section class="section">
        <h2>Mis Préstamos</h2>

        <?php if (empty($prestamos)): ?>
          <div class="empty">No tenés préstamos activos</div>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($prestamos as $p): ?>
                <tr>
                  <td><?php echo (int)($p['id'] ?? 0); ?></td>
                  <td>$<?php echo number_format((float)($p['monto'] ?? $p['monto_aprobado'] ?? 0), 0, ',', '.'); ?></td>
                  <td><?php echo h($p['estado'] ?? ''); ?></td>
                  <td><?php echo h($p['created_at'] ?? $p['fecha_aprobacion'] ?? ''); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>

    <?php endif; ?>

  </main>
</div>

<!-- ================= NOTIFICACIÓN FLOTANTE ================= -->
<?php if ($mostrar_notificacion): ?>
<div class="notificacion-flotante show" id="notificacionFlotante">
  <button class="notificacion-close" onclick="cerrarNotificacion()">×</button>
  
  <div class="notificacion-header">
    <div class="notificacion-icon <?php echo $notificacion_tipo; ?>">
      <?php if ($notificacion_tipo === 'aprobado'): ?>
        ✓
      <?php else: ?>
        ✗
      <?php endif; ?>
    </div>
    <div class="notificacion-titulo">
      <?php if ($notificacion_tipo === 'aprobado'): ?>
        ¡Documentos Aprobados!
      <?php else: ?>
        Documentos Rechazados
      <?php endif; ?>
    </div>
  </div>

  <div class="notificacion-mensaje">
    <?php if ($notificacion_tipo === 'aprobado'): ?>
      Tu documentación ha sido validada exitosamente. Ahora puedes continuar con el proceso de solicitud de préstamo.
    <?php else: ?>
      Lamentablemente, la documentación enviada no pudo ser validada. Por favor, revisa los requisitos y vuelve a cargar tus documentos.
    <?php endif; ?>
  </div>

  <div class="notificacion-acciones">
    <?php if ($notificacion_tipo === 'rechazado'): ?>
      <a href="documentacion.php" class="notificacion-btn primary">
        Volver a cargar documentos
      </a>
    <?php endif; ?>
    <button class="notificacion-btn secondary" onclick="cerrarNotificacion()">
      Entendido
    </button>
  </div>
</div>
<?php endif; ?>

<!-- ================= GATE ================= -->
<div class="overlay-gate <?= $mostrar_gate ? 'show' : '' ?>">
  <div class="gate-box">

<?php if ($gate_tipo === 'pendiente'): ?>

<div class="gate-head">
  <div class="gate-title">Necesitamos validar tu información</div>
  <div class="gate-sub">
    Para continuar con la evaluación de tu solicitud, es necesario que completes
    tu información personal y subas la documentación requerida.
    <br><br>
    Este paso es único y te lleva solo unos minutos.
  </div>
</div>

<div class="gate-actions">
  <a class="btn btn-primary" href="documentacion.php">
    Completar documentación
  </a>
  <a class="btn btn-ghost" href="logout.php">Salir</a>
</div>

<?php else: ?>

<div class="gate-head" style="text-align:center">
  <div class="hourglass" style="margin:0 auto 20px">
    ⏳
  </div>
  <div class="gate-title">Estamos validando tu información</div>
  <div class="gate-sub">
    Tu documentación ha sido recibida y está siendo revisada por nuestro equipo.
    <br><br>
    Te notificaremos cuando la validación esté completa.
  </div>
</div>

<div class="gate-actions">
  <a class="btn btn-ghost" href="logout.php">Salir</a>
</div>

<?php endif; ?>

  </div>
</div>


<script>
  (function(){
    var fill = document.getElementById('kpiFill');
    if (fill) fill.style.width = "<?php echo (int)$progress; ?>%";
  })();

  // Auto-cerrar notificación después de 10 segundos
  <?php if ($mostrar_notificacion): ?>
  setTimeout(function() {
    cerrarNotificacion();
  }, 10000);
  <?php endif; ?>

  function cerrarNotificacion() {
    var notif = document.getElementById('notificacionFlotante');
    if (notif) {
      notif.style.animation = 'slideOutRight 0.3s ease-out';
      setTimeout(function() {
        notif.style.display = 'none';
      }, 300);
    }
  }
</script>

<style>
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
</style>

</body>
</html>