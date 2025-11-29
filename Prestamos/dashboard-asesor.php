<?php
/**
 * =====================================================
 * DASHBOARD DEL ASESOR
 * dashboard-asesor.php
 * =====================================================
 */

session_start();
require_once 'includes/db.php';
require_once 'includes/funciones-notificaciones.php';

// Verificar que sea un asesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'ASESOR') {
    header('Location: login.php');
    exit;
}

$_SESSION['page_title'] = 'Dashboard Asesor';

$usuario_id = $_SESSION['usuario_id'];
$db = getDB();

// Obtener información del asesor
$asesor = $db->selectOne(
    "SELECT a.*, u.email 
     FROM asesores a 
     INNER JOIN usuarios u ON a.usuario_id = u.id 
     WHERE a.usuario_id = ?",
    [$usuario_id]
);

$asesor_id = $asesor['id'];

// Estadísticas generales
$total_clientes = $db->count(
    "SELECT COUNT(*) FROM clientes WHERE asesor_asignado_id = ?",
    [$asesor_id]
);

$solicitudes_pendientes = $db->count(
    "SELECT COUNT(*) FROM solicitudes_prestamo 
     WHERE asesor_asignado_id = ? 
     AND estado IN ('PENDIENTE', 'EN_REVISION', 'EN_ANALISIS')",
    [$asesor_id]
);

$prestamos_activos = $db->count(
    "SELECT COUNT(*) FROM prestamos 
     WHERE asesor_id = ? 
     AND estado IN ('ACTIVO', 'AL_DIA', 'ATRASADO', 'MORA')",
    [$asesor_id]
);

$chats_activos = $db->count(
    "SELECT COUNT(*) FROM salas_chat 
     WHERE asesor_id = ? 
     AND estado IN ('ABIERTA', 'EN_CURSO')",
    [$asesor_id]
);

// Solicitudes nuevas sin asignar (para tomar)
$solicitudes_disponibles = $db->select(
    "SELECT s.*, c.nombres, c.apellidos, c.numero_documento, tp.nombre as tipo_prestamo_nombre
     FROM solicitudes_prestamo s
     INNER JOIN clientes c ON s.cliente_id = c.id
     INNER JOIN tipos_prestamo tp ON s.tipo_prestamo_id = tp.id
     WHERE s.asesor_asignado_id IS NULL 
     AND s.estado = 'PENDIENTE'
     ORDER BY s.fecha_solicitud DESC
     LIMIT 5"
);

// Mis solicitudes pendientes de acción
$mis_solicitudes = $db->select(
    "SELECT s.*, c.nombres, c.apellidos, c.numero_documento, tp.nombre as tipo_prestamo_nombre
     FROM solicitudes_prestamo s
     INNER JOIN clientes c ON s.cliente_id = c.id
     INNER JOIN tipos_prestamo tp ON s.tipo_prestamo_id = tp.id
     WHERE s.asesor_asignado_id = ? 
     AND s.estado IN ('EN_REVISION', 'EN_ANALISIS', 'DOCUMENTACION_INCOMPLETA')
     ORDER BY s.prioridad DESC, s.fecha_solicitud ASC
     LIMIT 5",
    [$asesor_id]
);

// Chats activos
$chats_pendientes = $db->select(
    "SELECT sc.*, c.nombres, c.apellidos,
     (SELECT COUNT(*) FROM mensajes_chat 
      WHERE sala_id = sc.id AND leido = 0 AND remitente_tipo = 'CLIENTE') as mensajes_sin_leer
     FROM salas_chat sc
     INNER JOIN clientes c ON sc.cliente_id = c.id
     WHERE sc.asesor_id = ? 
     AND sc.estado IN ('ABIERTA', 'EN_CURSO')
     ORDER BY sc.fecha_actualizacion DESC
     LIMIT 5",
    [$asesor_id]
);

// Tareas pendientes
$tareas_pendientes = $db->select(
    "SELECT * FROM tareas 
     WHERE asignado_a_id = ? 
     AND estado IN ('PENDIENTE', 'EN_PROCESO')
     ORDER BY fecha_vencimiento ASC
     LIMIT 5",
    [$asesor_id]
);

// Préstamos con cuotas vencidas
$prestamos_morosos = $db->select(
    "SELECT p.*, c.nombres, c.apellidos, c.celular
     FROM prestamos p
     INNER JOIN clientes c ON p.cliente_id = c.id
     WHERE p.asesor_id = ? 
     AND p.estado IN ('ATRASADO', 'MORA')
     ORDER BY p.dias_mora DESC
     LIMIT 5",
    [$asesor_id]
);

// Estadísticas del mes actual
$mes_actual = date('Y-m');
$stats_mes = $db->selectOne(
    "SELECT 
        COUNT(DISTINCT CASE WHEN DATE_FORMAT(s.fecha_solicitud, '%Y-%m') = ? THEN s.id END) as solicitudes_mes,
        COUNT(DISTINCT CASE WHEN DATE_FORMAT(p.fecha_desembolso, '%Y-%m') = ? THEN p.id END) as prestamos_aprobados_mes,
        COALESCE(SUM(CASE WHEN DATE_FORMAT(p.fecha_desembolso, '%Y-%m') = ? THEN p.monto_desembolsado END), 0) as monto_desembolsado_mes
     FROM solicitudes_prestamo s
     LEFT JOIN prestamos p ON s.id = p.solicitud_id
     WHERE s.asesor_asignado_id = ?",
    [$mes_actual, $mes_actual, $mes_actual, $asesor_id]
);

// Clientes recientes
$clientes_recientes = $db->select(
    "SELECT * FROM clientes 
     WHERE asesor_asignado_id = ?
     ORDER BY fecha_creacion DESC
     LIMIT 5",
    [$asesor_id]
);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Asesor - Sistema de Préstamos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <!-- Bienvenida y Estado -->
            <div class="welcome-section">
                <div class="welcome-content">
                    <h2>Bienvenido, <?php echo htmlspecialchars($asesor['nombres']); ?> 👔</h2>
                    <p>Gestiona tus solicitudes y mantén contacto con tus clientes</p>
                </div>
                <div class="welcome-actions">
                    <div class="status-selector">
                        <label>Estado:</label>
                        <select id="estadoAsesor" class="form-select">
                            <option value="DISPONIBLE" <?php echo $asesor['estado_disponibilidad'] === 'DISPONIBLE' ? 'selected' : ''; ?>>
                                🟢 Disponible
                            </option>
                            <option value="OCUPADO" <?php echo $asesor['estado_disponibilidad'] === 'OCUPADO' ? 'selected' : ''; ?>>
                                🟡 Ocupado
                            </option>
                            <option value="AUSENTE" <?php echo $asesor['estado_disponibilidad'] === 'AUSENTE' ? 'selected' : ''; ?>>
                                🔴 Ausente
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $total_clientes; ?></h3>
                        <p>Mis Clientes</p>
                    </div>
                    <a href="mis-clientes.php" class="stat-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $solicitudes_pendientes; ?></h3>
                        <p>Solicitudes Pendientes</p>
                    </div>
                    <a href="solicitudes.php" class="stat-link">Revisar <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $prestamos_activos; ?></h3>
                        <p>Préstamos Activos</p>
                    </div>
                    <a href="prestamos-activos.php" class="stat-link">Ver detalle <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon"><i class="fas fa-comments"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $chats_activos; ?></h3>
                        <p>Chats Activos</p>
                    </div>
                    <a href="chat-asesor.php" class="stat-link">Abrir chat <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Estadísticas del Mes -->
            <div class="card stats-month-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Rendimiento del Mes (<?php echo date('F Y'); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="stats-month-grid">
                        <div class="stat-month-item">
                            <i class="fas fa-file-invoice"></i>
                            <h4><?php echo $stats_mes['solicitudes_mes']; ?></h4>
                            <p>Solicitudes Recibidas</p>
                        </div>
                        <div class="stat-month-item">
                            <i class="fas fa-check-circle"></i>
                            <h4><?php echo $stats_mes['prestamos_aprobados_mes']; ?></h4>
                            <p>Préstamos Aprobados</p>
                        </div>
                        <div class="stat-month-item">
                            <i class="fas fa-dollar-sign"></i>
                            <h4>$<?php echo number_format($stats_mes['monto_desembolsado_mes'], 0); ?></h4>
                            <p>Monto Desembolsado</p>
                        </div>
                        <div class="stat-month-item">
                            <i class="fas fa-percentage"></i>
                            <h4><?php echo $stats_mes['solicitudes_mes'] > 0 ? round(($stats_mes['prestamos_aprobados_mes'] / $stats_mes['solicitudes_mes']) * 100) : 0; ?>%</h4>
                            <p>Tasa de Aprobación</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Columna Principal -->
                <div class="dashboard-main">
                    
                    <!-- Solicitudes Nuevas para Tomar -->
                    <?php if (!empty($solicitudes_disponibles)): ?>
                    <div class="card card-highlight">
                        <div class="card-header">
                            <h3><i class="fas fa-bell"></i> Solicitudes Disponibles para Tomar</h3>
                            <span class="badge badge-primary"><?php echo count($solicitudes_disponibles); ?> nuevas</span>
                        </div>
                        <div class="card-body">
                            <div class="solicitudes-disponibles">
                                <?php foreach ($solicitudes_disponibles as $solicitud): ?>
                                <div class="solicitud-disponible-item">
                                    <div class="solicitud-info">
                                        <h4><?php echo htmlspecialchars($solicitud['nombres'] . ' ' . $solicitud['apellidos']); ?></h4>
                                        <p>
                                            <strong>Monto:</strong> $<?php echo number_format($solicitud['monto_solicitado'], 2); ?> | 
                                            <strong>Tipo:</strong> <?php echo $solicitud['tipo_prestamo_nombre']; ?>
                                        </p>
                                        <span class="solicitud-fecha">
                                            <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
                                        </span>
                                    </div>
                                    <button class="btn btn-primary btn-tomar-solicitud" data-id="<?php echo $solicitud['id']; ?>">
                                        <i class="fas fa-hand-paper"></i> Tomar Solicitud
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Mis Solicitudes en Proceso -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-tasks"></i> Mis Solicitudes en Proceso</h3>
                            <a href="solicitudes.php" class="card-action">Ver todas</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($mis_solicitudes)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <h4>No tienes solicitudes pendientes</h4>
                                    <p>Toma nuevas solicitudes de la cola de arriba</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Monto</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mis_solicitudes as $sol): ?>
                                            <tr>
                                                <td>
                                                    <div class="cliente-cell">
                                                        <strong><?php echo htmlspecialchars($sol['nombres'] . ' ' . $sol['apellidos']); ?></strong>
                                                        <span><?php echo $sol['numero_documento']; ?></span>
                                                    </div>
                                                </td>
                                                <td><strong>$<?php echo number_format($sol['monto_solicitado'], 2); ?></strong></td>
                                                <td><?php echo $sol['tipo_prestamo_nombre']; ?></td>
                                                <td><span class="badge badge-<?php echo strtolower($sol['estado']); ?>"><?php echo $sol['estado']; ?></span></td>
                                                <td><?php echo date('d/m/Y', strtotime($sol['fecha_solicitud'])); ?></td>
                                                <td>
                                                    <a href="procesar-solicitud.php?id=<?php echo $sol['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Procesar
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

                    <!-- Chats Activos -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-comment-dots"></i> Chats Activos</h3>
                            <a href="chat-asesor.php" class="card-action">Ver todos</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($chats_pendientes)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-comments"></i>
                                    <p>No tienes chats activos</p>
                                </div>
                            <?php else: ?>
                                <div class="chats-list">
                                    <?php foreach ($chats_pendientes as $chat): ?>
                                    <a href="chat-asesor.php?sala=<?php echo $chat['id']; ?>" class="chat-item <?php echo $chat['mensajes_sin_leer'] > 0 ? 'unread' : ''; ?>">
                                        <div class="chat-avatar">
                                            <i class="fas fa-user-circle"></i>
                                            <?php if ($chat['mensajes_sin_leer'] > 0): ?>
                                                <span class="chat-badge"><?php echo $chat['mensajes_sin_leer']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="chat-info">
                                            <h5><?php echo htmlspecialchars($chat['nombres'] . ' ' . $chat['apellidos']); ?></h5>
                                            <p><?php echo htmlspecialchars($chat['asunto'] ?? 'Sin asunto'); ?></p>
                                            <span class="chat-time"><?php echo date('H:i', strtotime($chat['fecha_actualizacion'])); ?></span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Sidebar Derecho -->
                <div class="dashboard-sidebar">
                    
                    <!-- Tareas Pendientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-clipboard-list"></i> Mis Tareas</h3>
                            <a href="tareas.php" class="card-action">Ver todas</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($tareas_pendientes)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-check"></i>
                                    <p>Sin tareas pendientes</p>
                                </div>
                            <?php else: ?>
                                <div class="tareas-list">
                                    <?php foreach ($tareas_pendientes as $tarea): ?>
                                    <?php
                                        $dias_para_vencer = (strtotime($tarea['fecha_vencimiento']) - time()) / (60 * 60 * 24);
                                        $urgente = $dias_para_vencer <= 1;
                                    ?>
                                    <div class="tarea-item <?php echo $urgente ? 'urgente' : ''; ?>">
                                        <div class="tarea-header">
                                            <h5><?php echo htmlspecialchars($tarea['titulo']); ?></h5>
                                            <span class="badge badge-<?php echo $tarea['prioridad']; ?>"><?php echo $tarea['prioridad']; ?></span>
                                        </div>
                                        <p><?php echo htmlspecialchars(substr($tarea['descripcion'], 0, 60)); ?>...</p>
                                        <span class="tarea-fecha">
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo date('d/m/Y', strtotime($tarea['fecha_vencimiento'])); ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Préstamos en Mora -->
                    <?php if (!empty($prestamos_morosos)): ?>
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3><i class="fas fa-exclamation-triangle"></i> Atención Requerida</h3>
                        </div>
                        <div class="card-body">
                            <div class="morosos-list">
                                <?php foreach ($prestamos_morosos as $moroso): ?>
                                <div class="moroso-item">
                                    <div class="moroso-info">
                                        <h5><?php echo htmlspecialchars($moroso['nombres'] . ' ' . $moroso['apellidos']); ?></h5>
                                        <p>Préstamo #<?php echo $moroso['numero_prestamo']; ?></p>
                                        <span class="badge badge-danger"><?php echo $moroso['dias_mora']; ?> días de mora</span>
                                    </div>
                                    <a href="tel:<?php echo $moroso['celular']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-phone"></i> Llamar
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Clientes Recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user-plus"></i> Clientes Recientes</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($clientes_recientes)): ?>
                                <div class="empty-state-small">
                                    <i class="fas fa-users"></i>
                                    <p>Sin clientes nuevos</p>
                                </div>
                            <?php else: ?>
                                <div class="clientes-recientes-list">
                                    <?php foreach ($clientes_recientes as $cliente): ?>
                                    <a href="detalle-cliente.php?id=<?php echo $cliente['id']; ?>" class="cliente-reciente-item">
                                        <div class="cliente-avatar">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <div class="cliente-info">
                                            <h5><?php echo htmlspecialchars($cliente['nombres'] . ' ' . $cliente['apellidos']); ?></h5>
                                            <p><?php echo $cliente['celular']; ?></p>
                                        </div>
                                    </a>
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
                                <a href="solicitudes.php?accion=nueva" class="quick-action">
                                    <i class="fas fa-plus"></i>
                                    <span>Crear Solicitud</span>
                                </a>
                                <a href="mis-clientes.php" class="quick-action">
                                    <i class="fas fa-users"></i>
                                    <span>Mis Clientes</span>
                                </a>
                                <a href="reportes-asesor.php" class="quick-action">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Mis Reportes</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Cambiar estado del asesor
        document.getElementById('estadoAsesor').addEventListener('change', function() {
            const estado = this.value;
            
            fetch('ajax/cambiar-estado-asesor.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ estado: estado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Estado actualizado correctamente');
                }
            });
        });

        // Tomar solicitud
        document.querySelectorAll('.btn-tomar-solicitud').forEach(btn => {
            btn.addEventListener('click', function() {
                const solicitudId = this.getAttribute('data-id');
                
                if (confirm('¿Deseas tomar esta solicitud?')) {
                    fetch('ajax/tomar-solicitud.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ solicitud_id: solicitudId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>