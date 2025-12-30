<?php
/*************************************************
 * pagos.php (PROD) - Portal Clientes
 *************************************************/

session_start();

/* =========================
   PROD SAFE LOG (SIN UI)
========================= */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$logDir  = __DIR__ . '/logs';
$logFile = $logDir . '/pagos.log';
if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
function dlog_pagos($msg) {
  global $logFile;
  $ts = date('Y-m-d H:i:s');
  @file_put_contents($logFile, "[$ts] $msg\n", FILE_APPEND);
}
set_exception_handler(function($e){
  dlog_pagos("UNCAUGHT EXCEPTION: ".$e->getMessage()." in ".$e->getFile().":".$e->getLine());
  http_response_code(500);
  exit;
});
set_error_handler(function($severity, $message, $file, $line){
  dlog_pagos("PHP ERROR [$severity] $message in $file:$line");
  return false;
});

/* =========================
   DB
========================= */
$connectionPath = __DIR__ . '/backend/connection.php';
if (!file_exists($connectionPath)) {
  dlog_pagos("FATAL: No existe backend/connection.php en: $connectionPath");
  http_response_code(500);
  exit;
}
require_once $connectionPath;

if (!isset($pdo) || !($pdo instanceof PDO)) {
  dlog_pagos("FATAL: \$pdo no existe o no es PDO. Revisar backend/connection.php");
  http_response_code(500);
  exit;
}

/* =========================
   SESIÓN
========================= */
if (!isset($_SESSION['cliente_id'], $_SESSION['cliente_email'])) {
  header('Location: login_clientes.php');
  exit;
}
$cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
if ($cliente_id <= 0) {
  dlog_pagos("cliente_id inválido en sesión. session=" . json_encode($_SESSION));
  header('Location: login_clientes.php');
  exit;
}

/* =========================
   HELPERS
========================= */
function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function money0($n){ return number_format((float)$n, 0, ',', '.'); }
function money2($n){ return number_format((float)$n, 2, ',', '.'); }

/* =========================
   MENSAJES
========================= */
$mensaje_error = null;
$mensaje_exito = null;

/* =========================
   PROCESAR COMPROBANTE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargar_comprobante'])) {

  try {
    $cuota_id        = (int)($_POST['cuota_id'] ?? 0);
    $monto_declarado = (float)($_POST['monto_declarado'] ?? 0);
    $fecha_pago      = $_POST['fecha_pago'] ?? null;
    $comentario      = trim($_POST['comentario'] ?? '');

    if ($cuota_id <= 0) $mensaje_error = 'Cuota inválida.';
    if (!$mensaje_error && $monto_declarado <= 0) $mensaje_error = 'Monto declarado inválido.';

    if (!$mensaje_error) {
      if ($fecha_pago !== null && $fecha_pago !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_pago)) {
          $mensaje_error = 'Fecha de pago inválida.';
        }
      } else {
        $fecha_pago = null;
      }
    }

    // Validar que la cuota pertenece al cliente
    if (!$mensaje_error) {
      $stmt = $pdo->prepare("
        SELECT c.id
        FROM cuotas c
        INNER JOIN prestamos p ON p.id = c.prestamo_id
        WHERE c.id = ? AND p.cliente_id = ?
        LIMIT 1
      ");
      $stmt->execute([$cuota_id, $cliente_id]);
      if (!$stmt->fetch()) $mensaje_error = 'Cuota inválida.';
    }

    // Archivo
    if (!$mensaje_error) {
      if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
        $mensaje_error = 'Seleccioná un archivo.';
      }
    }

    if (!$mensaje_error) {
      $archivo    = $_FILES['comprobante'];
      $extension  = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
      $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];

      if (!in_array($extension, $allowedExt, true)) {
        $mensaje_error = 'Formato de archivo no permitido.';
      }

      if (!$mensaje_error && ($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
        $mensaje_error = 'El archivo supera el tamaño máximo de 5MB.';
      }

      // MIME real (si está)
      if (!$mensaje_error && function_exists('finfo_open')) {
        $mimePermitidos = ['image/jpeg','image/png','application/pdf'];
        $mime = null;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
          $mime = finfo_file($finfo, $archivo['tmp_name']);
          finfo_close($finfo);
        }
        if ($mime !== null && !in_array($mime, $mimePermitidos, true)) {
          $mensaje_error = 'Tipo de archivo no válido.';
        }
      }

      if (!$mensaje_error) {
        $nombre_archivo = 'comprobante_' . $cuota_id . '_' . time() . '.' . $extension;

        $dirFisico = __DIR__ . '/../uploads/comprobantes/';
        $ruta_destino = $dirFisico . $nombre_archivo;
        $ruta_publica = '../uploads/comprobantes/' . $nombre_archivo;

        if (!is_dir($dirFisico)) { @mkdir($dirFisico, 0755, true); }

        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
          $stmt = $pdo->prepare("
            INSERT INTO comprobantes_pago (
              cuota_id, cliente_id, archivo_nombre, archivo_ruta,
              tipo_archivo, monto_declarado, fecha_pago_declarada,
              comentario, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
          ");
          $stmt->execute([
            $cuota_id,
            $cliente_id,
            $nombre_archivo,
            $ruta_publica,
            $extension,
            $monto_declarado,
            $fecha_pago,
            $comentario
          ]);

          $mensaje_exito = 'Comprobante cargado correctamente. Será revisado.';
        } else {
          $mensaje_error = 'Error al subir el archivo.';
        }
      }
    }
  } catch (Throwable $e) {
    dlog_pagos("EXCEPTION upload: ".$e->getMessage());
    $mensaje_error = 'Error interno al procesar el comprobante.';
  }
}

/* =========================
   OBTENER CUOTAS
========================= */
try {
  $stmt = $pdo->prepare("
    SELECT 
      c.*,
      p.monto_aprobado,
      p.cuotas_total,
      p.tasa_interes,
      DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes,
      (
        SELECT COUNT(*)
        FROM comprobantes_pago cp
        WHERE cp.cuota_id = c.id
      ) AS tiene_comprobantes
    FROM cuotas c
    INNER JOIN prestamos p ON p.id = c.prestamo_id
    WHERE p.cliente_id = ?
    ORDER BY c.fecha_vencimiento ASC
  ");
  $stmt->execute([$cliente_id]);
  $cuotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  dlog_pagos("DB cuotas exception: ".$e->getMessage());
  $cuotas = [];
  $mensaje_error = $mensaje_error ?: 'Error al cargar cuotas.';
}

$cuotas_pendientes = array_filter($cuotas, fn($c) => ($c['estado'] ?? '') === 'pendiente');
$cuotas_vencidas   = array_filter($cuotas, fn($c) => ($c['estado'] ?? '') === 'vencida');
$cuotas_pagadas    = array_filter($cuotas, fn($c) => ($c['estado'] ?? '') === 'pagada');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Pagos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Tailwind (modo CDN, como venís usando en el sitio) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Chart.js (SIEMPRE fuera de otros <script>) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
  </style>
</head>

<body>
  <div class="app-layout">

    <!-- SIDEBAR -->
    <?php
      $sidebarPath = __DIR__ . '/sidebar_clientes.php';
      if (file_exists($sidebarPath)) {
        require_once $sidebarPath;
      } else {
        dlog_pagos("WARNING: Falta sidebar_clientes.php en: $sidebarPath");
      }
    ?>

    <!-- CONTENT -->
    <main class="main-content">
      <div class="mb-6">
        <h1 class="text-3xl font-extrabold">Mis Pagos</h1>
        <p class="mt-1" style="color:var(--muted);">Gestioná tus cuotas y cargá tus comprobantes de pago</p>
      </div>

      <?php if ($mensaje_exito): ?>
        <div class="card p-4 mb-6" style="border-left:4px solid var(--accent); background: rgba(34,197,94,.10);">
          <p class="font-semibold" style="color:#bbf7d0;"><?= h($mensaje_exito) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($mensaje_error): ?>
        <div class="card p-4 mb-6" style="border-left:4px solid var(--danger); background: rgba(255,77,109,.10);">
          <p class="font-semibold" style="color:#fecdd3;"><?= h($mensaje_error) ?></p>
        </div>
      <?php endif; ?>

      <!-- TIMELINE VENCIMIENTOS -->
<div class="card p-6 mb-8">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold">Próximos Vencimientos</h2>
    <span class="chip info"><?= count($cuotas_pendientes) ?> activos</span>
  </div>

  <?php if (empty($cuotas_pendientes)): ?>
    <p class="text-center py-10" style="color:var(--muted);">
      No tenés cuotas próximas a vencer 🎉
    </p>
  <?php else: ?>
    <div class="timeline">
      <?php foreach ($cuotas_pendientes as $cuota): ?>
        <?php
          $dias = (int)($cuota['dias_restantes'] ?? 0);

          if ($dias < 0) {
            $estado = 'vencida';
            $color  = 'danger';
            $label  = 'Vencida';
          } elseif ($dias === 0) {
            $estado = 'hoy';
            $color  = 'warn';
            $label  = 'Vence hoy';
          } elseif ($dias <= 3) {
            $estado = 'pronto';
            $color  = 'warn';
            $label  = "En $dias días";
          } else {
            $estado = 'ok';
            $color  = 'ok';
            $label  = "En $dias días";
          }
        ?>

        <div class="timeline-item">
          <div class="timeline-dot <?= $color ?>"></div>

          <div class="timeline-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-extrabold">
                  Cuota <?= (int)$cuota['numero_cuota'] ?>
                </p>
                <p class="text-sm" style="color:var(--muted);">
                  Vence el <?= date('d/m/Y', strtotime($cuota['fecha_vencimiento'])) ?>
                </p>
              </div>

              <div class="text-right">
                <p class="text-xl font-extrabold">
                  $<?= money0($cuota['monto']) ?>
                </p>
                <span class="chip <?= $color ?>"><?= $label ?></span>
              </div>
            </div>

            <!-- barra de urgencia -->
            <div class="urgency-bar">
              <div class="urgency-fill <?= $color ?>" style="width:<?= max(10, min(100, 100 - ($dias * 10))) ?>%"></div>
            </div>
          </div>
        </div>

      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>


      <!-- VENCIDAS -->
      <?php if (!empty($cuotas_vencidas)): ?>
        <div class="card p-6 mb-8" style="border-left:4px solid var(--danger);">
          <h2 class="text-xl font-extrabold mb-4" style="color:#fecdd3;">⚠️ Cuotas Vencidas</h2>
          <div class="space-y-4">
            <?php foreach ($cuotas_vencidas as $cuota): ?>
              <?php
                $mora = abs((int)($cuota['dias_restantes'] ?? 0));
                $recargo = (float)($cuota['recargo_mora'] ?? 0);
                $montoTotal = (float)($cuota['monto'] ?? 0) + $recargo;
              ?>
              <div class="p-4 rounded-xl" style="background: rgba(255,77,109,.08); border:1px solid rgba(255,255,255,.10);">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                  <div>
                    <p class="font-bold">
                      Cuota <?= (int)$cuota['numero_cuota'] ?> de <?= (int)$cuota['cuotas_total'] ?>
                    </p>
                    <p style="color:var(--muted);" class="text-sm">
                      Venció el <?= date('d/m/Y', strtotime($cuota['fecha_vencimiento'])) ?>
                    </p>
                    <div class="mt-2 flex flex-wrap gap-2">
                      <span class="chip bad"><?= $mora ?> días de mora</span>
                      <?php if ($recargo > 0): ?>
                        <span class="chip bad">Recargo: $<?= money2($recargo) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-2xl font-extrabold" style="color:#fecdd3;">
                      $<?= money0($montoTotal) ?>
                    </p>
                    <button
                      type="button"
                      class="btn btn-danger mt-2"
                      onclick="abrirModalComprobante(<?= (int)$cuota['id'] ?>, <?= (float)$montoTotal ?>)"
                    >
                      Cargar Comprobante
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- PENDIENTES -->
      <div class="card p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-extrabold">Cuotas Pendientes</h2>
          <span class="chip info"><?= count($cuotas_pendientes) ?> items</span>
        </div>

        <?php if (empty($cuotas_pendientes)): ?>
          <p class="py-8 text-center" style="color:var(--muted);">No tenés cuotas pendientes</p>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($cuotas_pendientes as $cuota): ?>
              <?php
                $dias = (int)($cuota['dias_restantes'] ?? 0);
                $warn = ($dias <= 3);
              ?>
              <div class="p-4 rounded-xl" style="border:1px solid rgba(255,255,255,.10); background: rgba(255,255,255,.04);">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                  <div>
                    <p class="font-bold">
                      Cuota <?= (int)$cuota['numero_cuota'] ?> de <?= (int)$cuota['cuotas_total'] ?>
                    </p>
                    <p class="text-sm" style="color:var(--muted);">
                      Vence el <?= date('d/m/Y', strtotime($cuota['fecha_vencimiento'])) ?>
                    </p>

                    <div class="mt-2 flex flex-wrap gap-2">
                      <?php if ($dias === 0): ?>
                        <span class="chip warn">Vence HOY</span>
                      <?php elseif ($dias === 1): ?>
                        <span class="chip warn">Vence MAÑANA</span>
                      <?php else: ?>
                        <span class="chip <?= $warn ? 'warn' : 'ok' ?>">Vence en <?= $dias ?> días</span>
                      <?php endif; ?>

                      <?php if ((int)($cuota['tiene_comprobantes'] ?? 0) > 0): ?>
                        <span class="chip info">Comprobante en revisión</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="text-right">
                    <p class="text-2xl font-extrabold">$<?= money0($cuota['monto']) ?></p>
                    <button
                      type="button"
                      class="btn btn-primary mt-2"
                      onclick="abrirModalComprobante(<?= (int)$cuota['id'] ?>, <?= (float)$cuota['monto'] ?>)"
                    >
                      Cargar Comprobante
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- HISTORIAL -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-extrabold">Historial de Pagos</h2>
          <span class="chip ok"><?= count($cuotas_pagadas) ?> pagadas</span>
        </div>

        <?php if (empty($cuotas_pagadas)): ?>
          <p class="py-8 text-center" style="color:var(--muted);">Aún no tenés pagos registrados</p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.10);">
                  <th class="text-left py-3 px-4" style="color:var(--muted);">Cuota</th>
                  <th class="text-left py-3 px-4" style="color:var(--muted);">Monto</th>
                  <th class="text-left py-3 px-4" style="color:var(--muted);">Fecha de Pago</th>
                  <th class="text-left py-3 px-4" style="color:var(--muted);">Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cuotas_pagadas as $cuota): ?>
                  <tr style="border-bottom:1px solid rgba(255,255,255,.08);">
                    <td class="py-3 px-4">Cuota <?= (int)$cuota['numero_cuota'] ?></td>
                    <td class="py-3 px-4 font-bold">$<?= money0($cuota['monto']) ?></td>
                    <td class="py-3 px-4">
                      <?= !empty($cuota['fecha_pago']) ? date('d/m/Y', strtotime($cuota['fecha_pago'])) : '-' ?>
                    </td>
                    <td class="py-3 px-4"><span class="chip ok">Pagada</span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- MODAL -->
      <div id="modal-comprobante" class="fixed inset-0 hidden items-center justify-center z-50 modal-bg">
        <div class="modal-card max-w-md w-full mx-4 p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-extrabold">Cargar Comprobante de Pago</h3>
            <button type="button" onclick="cerrarModalComprobante()" class="btn btn-ghost px-3 py-2">✕</button>
          </div>

          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="cuota_id" id="modal-cuota-id">
            <input type="hidden" name="cargar_comprobante" value="1">

            <div class="mb-4">
              <label class="block text-sm font-semibold mb-2" style="color:var(--muted);">Monto Pagado</label>
              <input type="number" name="monto_declarado" id="modal-monto" step="0.01" required class="input">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-semibold mb-2" style="color:var(--muted);">Fecha de Pago</label>
              <input type="date" name="fecha_pago" required max="<?= date('Y-m-d'); ?>" class="input">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-semibold mb-2" style="color:var(--muted);">Comprobante (JPG, PNG o PDF)</label>
              <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf" required class="input">
              <p class="text-xs mt-2" style="color:var(--muted);">Máximo 5MB</p>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-semibold mb-2" style="color:var(--muted);">Comentario (opcional)</label>
              <textarea name="comentario" rows="3" class="input"></textarea>
            </div>

            <div class="flex gap-3">
              <button type="button" onclick="cerrarModalComprobante()" class="btn btn-ghost flex-1">Cancelar</button>
              <button type="submit" class="btn btn-primary flex-1">Cargar</button>
            </div>
          </form>
        </div>
      </div>

    </main>
  </div>

  <script>
    // Chart data
    const cuotasData = <?php echo json_encode(array_values($cuotas), JSON_UNESCAPED_UNICODE); ?>;

    // Modal
    function abrirModalComprobante(cuotaId, monto) {
      document.getElementById('modal-cuota-id').value = cuotaId;
      document.getElementById('modal-monto').value = monto;
      const m = document.getElementById('modal-comprobante');
      m.classList.remove('hidden');
      m.classList.add('flex');
    }
    function cerrarModalComprobante() {
      const m = document.getElementById('modal-comprobante');
      m.classList.add('hidden');
      m.classList.remove('flex');
    }

    // Chart (no "expande" por wrapper fijo + maintainAspectRatio false)
    const canvas = document.getElementById('vencimientos-chart');
    if (canvas && window.Chart) {
      const labels = cuotasData.map(c => `Cuota ${c.numero_cuota ?? ''}`);
      const montos = cuotasData.map(c => parseFloat(c.monto ?? 0));

      const colores = cuotasData.map(c => {
        if (c.estado === 'pagada') return 'rgba(34, 197, 94, 0.7)';
        if (c.estado === 'vencida') return 'rgba(239, 68, 68, 0.7)';
        if ((c.dias_restantes ?? 999) <= 3) return 'rgba(245, 158, 11, 0.7)';
        return 'rgba(59, 130, 246, 0.7)';
      });

      new Chart(canvas, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Monto',
            data: montos,
            backgroundColor: colores,
            borderColor: colores.map(x => x.replace('0.7','1')),
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { callback: v => '$' + Number(v).toLocaleString('es-AR') }
            }
          }
        }
      });
    }
  </script>
</body>
</html>
