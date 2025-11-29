<?php
/**
 * =====================================================
 * DASHBOARD DEL ADMINISTRADOR
 * dashboard-admin.php
 * =====================================================
 */

session_start();
require_once 'includes/db.php';
require_once 'includes/funciones-notificaciones.php';

// Verificar que sea administrador
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['ADMIN', 'SUPER_ADMIN'])) {
    header('Location: login.php');
    exit;
}

$_SESSION['page_title'] = 'Panel de Administración';

$usuario_id = $_SESSION['usuario_id'];
$db = getDB();

// =====================================================
// ESTADÍSTICAS GENERALES
// =====================================================

// Total de usuarios por rol
$total_clientes = $db->count("SELECT COUNT(*) FROM clientes");
$total_asesores = $db->count("SELECT COUNT(*) FROM asesores");
$total_admins = $db->count("SELECT COUNT(*) FROM administradores");

// Solicitudes
$solicitudes_pendientes = $db->count(
    "SELECT COUNT(*) FROM solicitudes_prestamo WHERE estado IN ('PENDIENTE', 'EN_REVISION', 'EN_ANALISIS')"
);
$solicitudes_hoy = $db->count(
    "SELECT COUNT(*) FROM solicitudes_prestamo WHERE DATE(fecha_solicitud) = CURDATE()"
);

// Préstamos
$prestamos_activos = $db->count(
    "SELECT COUNT(*) FROM prestamos WHERE estado IN ('ACTIVO', 'AL_DIA', 'ATRASADO', 'MORA')"
);
$prestamos_morosos = $db->count(
    "SELECT COUNT(*) FROM prestamos WHERE estado IN ('ATRASADO', 'MORA')"
);

// Montos
$monto_stats = $db->selectOne(
    "SELECT 
        COALESCE(SUM(monto_aprobado), 0) as total_cartera,
        COALESCE(SUM(monto_pendiente), 0) as monto_pendiente,
        COALESCE(SUM(monto_pagado), 0) as monto_recuperado
     FROM prestamos 
     WHERE estado IN ('ACTIVO', 'AL_DIA', 'ATRASADO', 'MORA')"
);

$pagos_hoy = $db->selectOne(
    "SELECT COALESCE(SUM(monto_pagado), 0) as total 
     FROM pagos 
     WHERE DATE(fecha_pago) = CURDATE() AND estado = 'ACREDITADO'"
);

// =====================================================
// ESTADÍSTICAS DEL MES ACTUAL
// =====================================================

$mes_actual = date('Y-m');
$stats_mes = $db->selectOne(
    "SELECT 
        COUNT(DISTINCT CASE WHEN DATE_FORMAT(fecha_solicitud, '%Y-%m') = ? THEN id END) as solicitudes_mes,
        COUNT(DISTINCT CASE WHEN DATE_FORMAT(fecha_aprobacion, '%Y-%m') = ? AND estado = 'APROBADO' THEN id END) as aprobadas_mes,
        COUNT(DISTINCT CASE WHEN DATE_FORMAT(fecha_rechazo, '%Y-%m') = ? AND estado = 'RECHAZADO' THEN id END) as rechazadas_mes
     FROM solicitudes_prestamo",
    [$mes_actual, $mes_actual, $mes_actual]
);

$prestamos_mes = $db->selectOne(
    "SELECT 
        COUNT(*) as total_prestamos,
        COALESCE(SUM(monto_desembolsado), 0) as monto_desembolsado
     FROM prestamos 
     WHERE DATE_FORMAT(fecha_desembolso, '%Y-%m') = ?",
    [$mes_actual]
);

$pagos_mes = $db->selectOne(
    "SELECT 
        COUNT(*) as total_pagos,
        COALESCE(SUM(monto_pagado), 0) as monto_recaudado
     FROM pagos 
     WHERE DATE_FORMAT(fecha_pago, '%Y-%m') = ? AND estado = 'ACREDITADO'",
    [$mes_actual]
);

// =====================================================
// GRÁFICAS Y TENDENCIAS
// =====================================================

// Solicitudes por día (últimos 7 días)
$solicitudes_semana = $db->select(
    "SELECT DATE(fecha_solicitud) as fecha, COUNT(*) as total
     FROM solicitudes_prestamo
     WHERE fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
     GROUP BY DATE(fecha_solicitud)
     ORDER BY fecha ASC"
);

// Top 5 asesores del mes
$top_asesores = $db->select(
    "SELECT 
        a.nombres, a.apellidos,
        COUNT(DISTINCT p.id) as prestamos_aprobados,
        COALESCE(SUM(p.monto_desembolsado), 0) as monto_total
     FROM asesores a
     LEFT JOIN prestamos p ON a.id = p.asesor_id AND DATE_FORMAT(p.fecha_desembolso, '%Y-%m') = ?
     GROUP BY a.id
     ORDER BY prestamos_aprobados DESC
     LIMIT 5",
    [$mes_actual]
);

// =====================================================
// ALERTAS Y NOTIFICACIONES
// =====================================================

// Solicitudes sin asignar
$solicitudes_sin_asignar = $db->count(
    "SELECT COUNT(*) FROM solicitudes_prestamo WHERE asesor_asignado_id IS NULL AND estado = 'PENDIENTE'"
);

// Cuotas vencidas hoy
$cuotas_vencidas_hoy = $db->count(
    "SELECT COUNT(*) FROM cuotas WHERE fecha_vencimiento = CURDATE() AND estado = 'PENDIENTE'"
);

// Documentos pendientes de verificar
$documentos_pendientes = $db->count(
    "SELECT COUNT(*) FROM documentos WHERE verificado = 0 AND entidad_tipo = 'SOLICITUD'"
);

// Últimas actividades del sistema
$actividades_recientes = $db->select(
    "SELECT * FROM auditoria 
     ORDER BY fecha_accion DESC 
     LIMIT 10"
);

// Solicitudes recientes
$solicitudes_recientes = $db->select(
    "SELECT s.*, c.nombres, c.apellidos, tp.nombre as tipo_prestamo
     FROM solicitudes_prestamo s
     INNER JOIN clientes c ON s.cliente_id = c.id
     INNER JOIN tipos_prestamo tp ON s.tipo_prestamo_id = tp.id
     ORDER BY s.fecha_solicitud DESC
     LIMIT 5"
);

// Préstamos próximos a vencer
$prestamos_vencer = $db->select(
    "SELECT p.*, c.nombres, c.apellidos
     FROM prestamos p
     INNER JOIN clientes c ON p.cliente_id = c.id
     WHERE p.proxima_cuota_fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
     AND p.estado IN ('ACTIVO', 'AL_DIA')
     ORDER BY p.proxima_cuota_fecha ASC
     LIMIT 5"
);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - Sistema de Préstamos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <!-- Bienvenida -->
            <div class="welcome-section admin-welcome">
                <div class="welcome-content">
                    <h2>Panel de Administración 📊</h2>
                    <p>Vista general del sistema y métricas clave</p>
                </div>
                <div class="welcome-actions">
                    <button class="btn btn-outline" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <a href="reportes.php" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Generar Reporte
                    </a>
                </div>
            </div>

            <!-- Alertas Importantes -->
            <?php if ($solicitudes_sin_asignar > 0 || $documentos_pendientes > 0 || $prestamos_morosos > 0): ?>
            <div class="alerts-section">
                <?php if ($solicitudes_sin_asignar > 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="alert-content">
                        <strong>Atención:</strong> Hay <?php echo $solicitudes_sin_asignar; ?> solicitudes sin asignar a ningún asesor.
                        <a href="solicitudes-admin.php?filter=sin_asignar" class="alert-link">Asignar ahora</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($prestamos_morosos > 0): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="alert-content">
                        <strong>Mora:</strong> <?php echo $prestamos_morosos; ?> préstamos con cuotas vencidas.
                        <a href="prestamos-admin.php?filter=morosos" class="alert-link">Ver detalle</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($documentos_pendientes > 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-file-alt"></i>
                    <div class="alert-content">
                        <strong>Documentos:</strong> <?php echo $documentos_pendientes; ?> documentos pendientes de verificación.
                        <a href="documentos-admin.php" class="alert-link">Revisar</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Estadísticas Principales -->
            <div class="stats-grid stats-grid-admin">
                <!-- Usuarios -->
                <div class="stat-card stat-primary">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <h3><?php echo number_format($total_clientes); ?></h3>
                        <p>Total Clientes</p>
                        <div class="stat-detail">
                            <span><i class="fas fa-user-tie"></i> <?php echo $total_asesores; ?> Asesores</span>
                            <span><i class="fas fa-user-shield"></i> <?php echo $total_admins; ?> Admins</span>
                        </div>
                    </div>
                </div>

                <!-- Solicitudes -->
                <div class="stat-card stat-warning">
                    <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $solicitudes_pendientes; ?></h3>
                        <p>Solicitudes Pendientes</p>
                        <div class="stat-detail">
                            <span><i class="fas fa-plus"></i> <?php echo $solicitudes_hoy; ?> Hoy</span>
                        </div>
                    </div>
                    <a href="solicitudes-admin.php" class="stat-link">Gestionar <i class="fas fa-arrow-right"></i></a>
                </div>

                <!-- Préstamos -->
                <div class="stat-card stat-success">
                    <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <div class="stat-content">
                        <h3><?php echo number_format($prestamos_activos); ?></h3>
                        <p>Préstamos Activos</p>
                        <div class="stat-detail">
                            <span class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $prestamos_morosos; ?> en Mora
                            </span>
                        </div>
                    </div>
                    <a href="prestamos-admin.php" class="stat-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                </div>

                <!-- Cartera -->
                <div class="stat-card stat-info">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($monto_stats['total_cartera'], 0); ?></h3>
                        <p>Cartera Total</p>
                        <div class="stat-detail">
                            <span><i class="fas fa-clock"></i> $<?php echo number_format($monto_stats['monto_pendiente'], 0); ?> Pendiente</span>
                        </div>
                    </div>
                </div>

                <!-- Pagos Hoy -->
                <div class="stat-card stat-success-alt">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($pagos_hoy['total'], 0); ?></h3>
                        <p>Recaudado Hoy</p>
                    </div>
                    <a href="pagos-admin.php?filter=today" class="stat-link">Ver detalle <i class="fas fa-arrow-right"></i></a>
                </div>

                <!-- Recuperación -->
                <div class="stat-card stat-purple">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $monto_stats['total_cartera'] > 0 ? number_format(($monto_stats['monto_recuperado'] / $monto_stats['total_cartera']) * 100, 1) : 0; ?>%</h3>
                        <p>Tasa de Recuperación</p>
                        <div class="stat-detail">
                            <span>$<?php echo number_format($monto_stats['monto_recuperado'], 0); ?> Recuperado</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métricas del Mes -->
            <div class="card metrics-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Resumen del Mes (<?php echo date('F Y'); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <div class="metric-icon bg-blue">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="metric-content">
                                <h4><?php echo $stats_mes['solicitudes_mes']; ?></h4>
                                <p>Solicitudes Recibidas</p>
                                <div class="metric-breakdown">
                                    <span class="text-success">✓ <?php echo $stats_mes['aprobadas_mes']; ?> Aprobadas</span>
                                    <span class="text-danger">✗ <?php echo $stats_mes['rechazadas_mes']; ?> Rechazadas</span>
                                </div>
                            </div>
                        </div>

                        <div class="metric-item">
                            <div class="metric-icon bg-green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="metric-content">
                                <h4><?php echo $prestamos_mes['total_prestamos']; ?></h4>
                                <p>Préstamos Desembolsados</p>
                                <div class="metric-breakdown">
                                    <span>$<?php echo number_format($prestamos_mes['monto_desembolsado'], 0); ?> Total</span>
                                </div>
                            </div>
                        </div>

                        <div class="metric-item">
                            <div class="metric-icon bg-orange">
                                <i class="fas fa-money-check-alt"></i>
                            </div>
                            <div class="metric-content">
                                <h4><?php echo $pagos_mes['total_pagos']; ?></h4>
                                <p>Pagos Recibidos</p>
                                <div class="metric-breakdown">
                                    <span>$<?php echo number_format($pagos_mes['monto_recaudado'], 0); ?> Recaudado</span>
                                </div>
                            </div>
                        </div>

                        <div class="metric-item">
                            <div class="metric-icon bg-purple">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="metric-content">
                                <h4><?php echo $stats_mes['solicitudes_mes'] > 0 ? round(($stats_mes['aprobadas_mes'] / $stats_mes['solicitudes_mes']) * 100, 1) : 0; ?>%</h4>
                                <p>Tasa de Aprobación</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Columna Principal -->
                <div class="dashboard-main">
                    
                    <!-- Gráfica de Solicitudes -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-line"></i> Solicitudes - Últimos 7 Días</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartSolicitudes" height="80"></canvas>
                        </div>
                    </div>

                    <!-- Solicitudes Recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-alt"></i> Solicitudes Recientes</h3>
                            <a href="solicitudes-admin.php" class="card-action">Ver todas</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($solicitudes_recientes)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No hay solicitudes recientes</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Solicitud</th>
                                                <th>Cliente</th>
                                                <th>Tipo</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($solicitudes_recientes as $sol): ?>
                                            <tr>
                                                <td><strong>#<?php echo $sol['numero_solicitud']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($sol['nombres'] . ' ' . $sol['apellidos']); ?></td>
                                                <td><?php echo $sol['tipo_prestamo']; ?></td>
                                                <td><strong>$<?php echo number_format($sol['monto_solicitado'], 2); ?></strong></td>
                                                <td><span class="badge badge-<?php echo strtolower($sol['estado']); ?>"><?php echo $sol['estado']; ?></span></td>
                                                <td><?php echo date('d/m/Y', strtotime($sol['fecha_solicitud'])); ?></td>
                                                <td>
                                                    <a href="detalle-solicitud-admin.php?id=<?php echo $sol['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Top Asesores -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-trophy"></i> Top Asesores del Mes</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($top_asesores)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-award"></i>
                                    <p>Sin datos de asesores</p>
                                </div>
                            <?php else: ?>
                                <div class="top-asesores-list">
                                    <?php 
                                    $posicion = 1;
                                    foreach ($top_asesores as $asesor): 
                                    ?>
                                    <div class="top-asesor-item">
                                        <div class="asesor-rank rank-<?php echo $posicion; ?>">
                                            <?php if ($posicion <= 3): ?>
                                                <i class="fas fa-medal"></i>
                                            <?php endif; ?>
                                            <span>#<?php echo $posicion; ?></span>
                                        </div>
                                        <div class="asesor-info">
                                            <h5><?php echo htmlspecialchars($asesor['nombres'] . ' ' . $asesor['apellidos']); ?></h5>
                                            <p><?php echo $asesor['prestamos_aprobados']; ?> préstamos aprobados</p>
                                        </div>
                                        <div class="asesor-monto">
                                            <strong>$<?php echo number_format($asesor['monto_total'], 0); ?></strong>
                                        </div>
                                    </div>
                                    <?php 
                                    $posicion++;
                                    endforeach; 
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Sidebar Derecho -->
                <div class="dashboard-sidebar">
                    
                    <!-- Acciones Rápidas Admin -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions-admin">
                                <a href="solicitudes-admin.php?accion=nueva" class="quick-action-admin">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Nueva Solicitud</span>
                                </a>
                                <a href="clientes-admin.php?accion=nuevo" class="quick-action-admin">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Nuevo Cliente</span>
                                </a>
                                <a href="asesores-admin.php?accion=nuevo" class="quick-action-admin">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Nuevo Asesor</span>
                                </a>
                                <a href="tipos-prestamo.php" class="quick-action-admin">
                                    <i class="fas fa-list-alt"></i>
                                    <span>Config. Préstamos</span>
                                </a>
                                <a href="reportes.php" class="quick-action-admin">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Reportes</span>
                                </a>
                                <a href="configuracion.php" class="quick-action-admin">
                                    <i class="fas fa-cog"></i>
                                    <span>Configuración</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Próximos Vencimientos -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-calendar-check"></i> Próximos Vencimientos</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($prestamos_vencer)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Sin vencimientos próximos</p>
                                </div>
                            <?php else: ?>
                                <div class="vencimientos-list">
                                    <?php foreach ($prestamos_vencer as $prestamo): ?>
                                    <div class="vencimiento-item">
                                        <div class="vencimiento-info">
                                            <h5><?php echo htmlspecialchars($prestamo['nombres'] . ' ' . $prestamo['apellidos']); ?></h5>
                                            <p>Préstamo #<?php echo $prestamo['numero_prestamo']; ?></p>
                                            <span class="vencimiento-fecha">
                                                <i class="fas fa-calendar"></i> 
                                                <?php echo date('d/m/Y', strtotime($prestamo['proxima_cuota_fecha'])); ?>
                                            </span>
                                        </div>
                                        <div class="vencimiento-monto">
                                            $<?php echo number_format($prestamo['proxima_cuota_monto'], 2); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actividad Reciente -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                            <a href="auditoria.php" class="card-action">Ver todo</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($actividades_recientes)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-clock"></i>
                                    <p>Sin actividad reciente</p>
                                </div>
                            <?php else: ?>
                                <div class="actividad-list">
                                    <?php foreach (array_slice($actividades_recientes, 0, 5) as $actividad): ?>
                                    <div class="actividad-item">
                                        <div class="actividad-icon">
                                            <i class="fas fa-<?php echo $actividad['accion'] === 'INSERT' ? 'plus' : ($actividad['accion'] === 'UPDATE' ? 'edit' : 'trash'); ?>"></i>
                                        </div>
                                        <div class="actividad-content">
                                            <p><strong><?php echo $actividad['accion']; ?></strong> en <?php echo $actividad['tabla_afectada']; ?></p>
                                            <span class="actividad-fecha"><?php echo date('H:i', strtotime($actividad['fecha_accion'])); ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estado del Sistema -->
                    <div class="card card-system-status">
                        <div class="card-header">
                            <h3><i class="fas fa-server"></i> Estado del Sistema</h3>
                        </div>
                        <div class="card-body">
                            <div class="system-status-list">
                                <div class="status-item status-ok">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Base de Datos</span>
                                    <span class="status-badge">Online</span>
                                </div>
                                <div class="status-item status-ok">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Servidor</span>
                                    <span class="status-badge">Activo</span>
                                </div>
                                <div class="status-item status-ok">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Emails</span>
                                    <span class="status-badge">Operativo</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Gráfica de solicitudes
        const ctx = document.getElementById('chartSolicitudes').getContext('2d');
        const solicitudesData = <?php echo json_encode($solicitudes_semana); ?>;
        
        const labels = solicitudesData.map(item => {
            const fecha = new Date(item.fecha);
            return fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
        });
        
        const datos = solicitudesData.map(item => item.total);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Solicitudes',
                    data: datos,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        borderColor: '#334155',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>