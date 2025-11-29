<?php
/**
 * =====================================================
 * DASHBOARD DEL CLIENTE
 * dashboard-cliente.php
 * =====================================================
 */

session_start();
require_once 'includes/db.php';
require_once 'includes/funciones-notificaciones.php';

// Verificar que sea un cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'CLIENTE') {
    header('Location: login.php');
    exit;
}

$_SESSION['page_title'] = 'Mi Dashboard';

$usuario_id = $_SESSION['usuario_id'];
$db = getDB();

// Obtener información del cliente
$cliente = $db->selectOne(
    "SELECT c.*, u.email 
     FROM clientes c 
     INNER JOIN usuarios u ON c.usuario_id = u.id 
     WHERE c.usuario_id = ?",
    [$usuario_id]
);

// Obtener préstamos activos
$prestamos_activos = $db->select(
    "SELECT * FROM prestamos 
     WHERE cliente_id = ? AND estado IN ('ACTIVO', 'AL_DIA', 'ATRASADO', 'MORA')
     ORDER BY fecha_creacion DESC",
    [$cliente['id']]
);

// Obtener solicitudes pendientes
$solicitudes_pendientes = $db->select(
    "SELECT * FROM solicitudes_prestamo 
     WHERE cliente_id = ? AND estado NOT IN ('APROBADO', 'RECHAZADO', 'CANCELADO', 'DESEMBOLSADO')
     ORDER BY fecha_solicitud DESC",
    [$cliente['id']]
);

// Obtener próximas cuotas
$proximas_cuotas = $db->select(
    "SELECT c.*, p.numero_prestamo, p.monto_aprobado 
     FROM cuotas c
     INNER JOIN prestamos p ON c.prestamo_id = p.id
     WHERE p.cliente_id = ? AND c.estado = 'PENDIENTE'
     ORDER BY c.fecha_vencimiento ASC
     LIMIT 3",
    [$cliente['id']]
);

// Calcular estadísticas
$total_prestamos = count($prestamos_activos);
$total_solicitudes = count($solicitudes_pendientes);

$deuda_total = 0;
$proximo_vencimiento = null;
$cuotas_vencidas = 0;

foreach ($prestamos_activos as $prestamo) {
    $deuda_total += $prestamo['monto_pendiente'];
    if ($prestamo['dias_mora'] > 0) {
        $cuotas_vencidas++;
    }
    if (!$proximo_vencimiento || $prestamo['proxima_cuota_fecha'] < $proximo_vencimiento) {
        $proximo_vencimiento = $prestamo['proxima_cuota_fecha'];
    }
}

// Obtener historial de pagos recientes
$pagos_recientes = $db->select(
    "SELECT p.*, pr.numero_prestamo 
     FROM pagos p
     INNER JOIN prestamos pr ON p.prestamo_id = pr.id
     WHERE p.cliente_id = ?
     ORDER BY p.fecha_pago DESC
     LIMIT 5",
    [$cliente['id']]
);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Dashboard - Sistema de Préstamos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <!-- Mensaje de Bienvenida -->
            <div class="welcome-section">
                <div class="welcome-content">
                    <h2>¡Bienvenido, <?php echo htmlspecialchars($cliente['nombres']); ?>! 👋</h2>
                    <p>Aquí tienes un resumen de tus préstamos y actividad reciente</p>
                </div>
                <div class="welcome-actions">
                    <a href="solicitar-prestamo.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Solicitar Préstamo
                    </a>
                    <a href="simulador.php" class="btn btn-outline">
                        <i class="fas fa-calculator"></i> Simular
                    </a>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="stats-grid">
                <!-- Préstamos Activos -->
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_prestamos; ?></h3>
                        <p>Préstamos Activos</p>
                    </div>
                    <a href="mis-prestamos.php" class="stat-link">Ver detalles <i class="fas fa-arrow-right"></i></a>
                </div>

                <!-- Deuda Total -->
                <div class="stat-card stat-danger">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($deuda_total, 2); ?></h3>
                        <p>Deuda Total</p>
                    </div>
                    <a href="mis-pagos.php" class="stat-link">Pagar ahora <i class="fas fa-arrow-right"></i></a>
                </div>

                <!-- Solicitudes Pendientes -->
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_solicitudes; ?></h3>
                        <p>Solicitudes Pendientes</p>
                    </div>
                    <a href="mis-prestamos.php?tab=solicitudes" class="stat-link">Ver estado <i class="fas fa-arrow-right"></i></a>
                </div>

                <!-- Próximo Vencimiento -->
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $proximo_vencimiento ? date('d/m/Y', strtotime($proximo_vencimiento)) : 'N/A'; ?></h3>
                        <p>Próximo Vencimiento</p>
                    </div>
                    <a href="mis-pagos.php" class="stat-link">Ver calendario <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Alertas -->
            <?php if ($cuotas_vencidas > 0): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div class="alert-content">
                    <strong>¡Atención!</strong> Tienes <?php echo $cuotas_vencidas; ?> cuota<?php echo $cuotas_vencidas > 1 ? 's' : ''; ?> vencida<?php echo $cuotas_vencidas > 1 ? 's' : ''; ?>.
                    <a href="mis-pagos.php" class="alert-link">Ver detalles y pagar</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <!-- Columna Izquierda -->
                <div class="dashboard-main">
                    
                    <!-- Préstamos Activos -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-money-bill-wave"></i> Mis Préstamos Activos</h3>
                            <a href="mis-prestamos.php" class="card-action">Ver todos</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($prestamos_activos)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No tienes préstamos activos</h4>
                                    <p>Solicita tu primer préstamo y comienza a cumplir tus objetivos</p>
                                    <a href="solicitar-prestamo.php" class="btn btn-primary">Solicitar Préstamo</a>
                                </div>
                            <?php else: ?>
                                <div class="prestamos-list">
                                    <?php foreach ($prestamos_activos as $prestamo): ?>
                                    <div class="prestamo-item">
                                        <div class="prestamo-header">
                                            <div class="prestamo-info">
                                                <h4>#<?php echo htmlspecialchars($prestamo['numero_prestamo']); ?></h4>
                                                <span class="prestamo-badge badge-<?php echo strtolower($prestamo['estado']); ?>">
                                                    <?php echo $prestamo['estado']; ?>
                                                </span>
                                            </div>
                                            <div class="prestamo-monto">
                                                <span class="monto-label">Monto Original</span>
                                                <span class="monto-value">$<?php echo number_format($prestamo['monto_aprobado'], 2); ?></span>
                                            </div>
                                        </div>
                                        <div class="prestamo-progress">
                                            <div class="progress-info">
                                                <span>Pagado: <?php echo $prestamo['cuotas_pagadas']; ?>/<?php echo $prestamo['plazo_meses']; ?> cuotas</span>
                                                <span><?php echo number_format(($prestamo['cuotas_pagadas'] / $prestamo['plazo_meses']) * 100, 1); ?>%</span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo ($prestamo['cuotas_pagadas'] / $prestamo['plazo_meses']) * 100; ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="prestamo-footer">
                                            <div class="prestamo-stat">
                                                <span class="stat-label">Pendiente</span>
                                                <span class="stat-value">$<?php echo number_format($prestamo['monto_pendiente'], 2); ?></span>
                                            </div>
                                            <div class="prestamo-stat">
                                                <span class="stat-label">Próxima cuota</span>
                                                <span class="stat-value"><?php echo date('d/m/Y', strtotime($prestamo['proxima_cuota_fecha'])); ?></span>
                                            </div>
                                            <a href="detalle-prestamo.php?id=<?php echo $prestamo['id']; ?>" class="btn btn-sm btn-outline">Ver detalle</a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Próximas Cuotas -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-calendar-alt"></i> Próximas Cuotas</h3>
                            <a href="mis-pagos.php" class="card-action">Ver calendario completo</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($proximas_cuotas)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-check-circle"></i>
                                    <p>No tienes cuotas pendientes</p>
                                </div>
                            <?php else: ?>
                                <div class="cuotas-list">
                                    <?php foreach ($proximas_cuotas as $cuota): ?>
                                    <?php
                                        $dias_para_vencer = (strtotime($cuota['fecha_vencimiento']) - time()) / (60 * 60 * 24);
                                        $urgente = $dias_para_vencer <= 3;
                                    ?>
                                    <div class="cuota-item <?php echo $urgente ? 'urgente' : ''; ?>">
                                        <div class="cuota-icon">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                        <div class="cuota-info">
                                            <h5>Préstamo #<?php echo htmlspecialchars($cuota['numero_prestamo']); ?></h5>
                                            <p>Cuota <?php echo $cuota['numero_cuota']; ?> - Vence el <?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></p>
                                        </div>
                                        <div class="cuota-monto">
                                            <span class="monto-cuota">$<?php echo number_format($cuota['monto_cuota'], 2); ?></span>
                                            <?php if ($urgente): ?>
                                                <span class="badge badge-danger">Urgente</span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="pagar-cuota.php?id=<?php echo $cuota['id']; ?>" class="btn btn-sm btn-primary">Pagar</a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Columna Derecha (Sidebar de información) -->
                <div class="dashboard-sidebar">
                    
                    <!-- Solicitudes Pendientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-alt"></i> Mis Solicitudes</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($solicitudes_pendientes)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Sin solicitudes pendientes</p>
                                    <a href="solicitar-prestamo.php" class="btn btn-sm btn-primary">Nueva Solicitud</a>
                                </div>
                            <?php else: ?>
                                <div class="solicitudes-list">
                                    <?php foreach ($solicitudes_pendientes as $solicitud): ?>
                                    <div class="solicitud-item">
                                        <div class="solicitud-header">
                                            <span class="solicitud-numero">#<?php echo $solicitud['numero_solicitud']; ?></span>
                                            <span class="badge badge-<?php echo strtolower($solicitud['estado']); ?>">
                                                <?php echo $solicitud['estado']; ?>
                                            </span>
                                        </div>
                                        <p class="solicitud-monto">$<?php echo number_format($solicitud['monto_solicitado'], 2); ?></p>
                                        <p class="solicitud-fecha"><?php echo date('d/m/Y', strtotime($solicitud['fecha_solicitud'])); ?></p>
                                        <a href="detalle-solicitud.php?id=<?php echo $solicitud['id']; ?>" class="solicitud-link">Ver detalle</a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pagos Recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Pagos Recientes</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pagos_recientes)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-receipt"></i>
                                    <p>No hay pagos registrados</p>
                                </div>
                            <?php else: ?>
                                <div class="pagos-list">
                                    <?php foreach ($pagos_recientes as $pago): ?>
                                    <div class="pago-item">
                                        <div class="pago-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="pago-info">
                                            <h5>$<?php echo number_format($pago['monto_pagado'], 2); ?></h5>
                                            <p>Préstamo #<?php echo $pago['numero_prestamo']; ?></p>
                                            <span class="pago-fecha"><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions">
                                <a href="solicitar-prestamo.php" class="quick-action">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Nueva Solicitud</span>
                                </a>
                                <a href="simulador.php" class="quick-action">
                                    <i class="fas fa-calculator"></i>
                                    <span>Simular Préstamo</span>
                                </a>
                                <a href="mis-pagos.php" class="quick-action">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Realizar Pago</span>
                                </a>
                                <a href="chat.php" class="quick-action">
                                    <i class="fas fa-comments"></i>
                                    <span>Chat con Asesor</span>
                                </a>
                                <a href="documentos.php" class="quick-action">
                                    <i class="fas fa-file-upload"></i>
                                    <span>Subir Documentos</span>
                                </a>
                                <a href="perfil.php" class="quick-action">
                                    <i class="fas fa-user-edit"></i>
                                    <span>Actualizar Perfil</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Banner Promocional -->
                    <div class="card card-promo">
                        <div class="promo-content">
                            <i class="fas fa-gift"></i>
                            <h4>¡Oferta Especial!</h4>
                            <p>Obtén hasta 5% de descuento en tu próximo préstamo</p>
                            <a href="promociones.php" class="btn btn-light">Ver promociones</a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>