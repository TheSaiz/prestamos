<?php
/**
 * =====================================================
 * HEADER DEL SISTEMA
 * header.php
 * =====================================================
 * Uso: include 'includes/header.php';
 */

// Verificar que haya sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_email = $_SESSION['usuario_email'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? 'CLIENTE';
$usuario_foto = $_SESSION['usuario_foto'] ?? 'assets/img/default-avatar.png';

// Obtener notificaciones sin leer
$db = getDB();
$notificaciones_query = "SELECT COUNT(*) as total FROM notificaciones 
                         WHERE usuario_id = ? AND leida = 0";
$notificaciones_count = $db->selectOne($notificaciones_query, [$usuario_id]);
$total_notificaciones = $notificaciones_count['total'] ?? 0;

// Obtener últimas 5 notificaciones
$notificaciones_recientes = $db->select(
    "SELECT * FROM notificaciones 
     WHERE usuario_id = ? 
     ORDER BY fecha_envio DESC 
     LIMIT 5",
    [$usuario_id]
);

// Determinar título de página
$page_title = $_SESSION['page_title'] ?? 'Dashboard';
?>

<!-- Header / Navbar -->
<header class="main-header">
    <div class="header-container">
        <!-- Sección Izquierda -->
        <div class="header-left">
            <!-- Toggle para móvil -->
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Título de la página -->
            <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
        </div>

        <!-- Sección Derecha -->
        <div class="header-right">
            
            <!-- Barra de búsqueda (opcional - solo para admin y asesores) -->
            <?php if ($usuario_rol === 'ADMIN' || $usuario_rol === 'SUPER_ADMIN' || $usuario_rol === 'ASESOR'): ?>
            <div class="header-search">
                <form action="buscar.php" method="GET" class="search-form">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input 
                            type="text" 
                            name="q" 
                            class="search-input" 
                            placeholder="Buscar clientes, préstamos..." 
                            autocomplete="off"
                        >
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Botón de Simulador (solo clientes) -->
            <?php if ($usuario_rol === 'CLIENTE'): ?>
            <a href="simulador.php" class="header-btn" title="Simular Préstamo">
                <i class="fas fa-calculator"></i>
                <span class="btn-text">Simular</span>
            </a>
            <?php endif; ?>

            <!-- Notificaciones -->
            <div class="header-notifications">
                <button class="notification-btn" id="notificationBtn" title="Notificaciones">
                    <i class="fas fa-bell"></i>
                    <?php if ($total_notificaciones > 0): ?>
                        <span class="notification-badge"><?php echo $total_notificaciones > 99 ? '99+' : $total_notificaciones; ?></span>
                    <?php endif; ?>
                </button>

                <!-- Dropdown de Notificaciones -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notificaciones</h3>
                        <?php if ($total_notificaciones > 0): ?>
                            <a href="#" class="mark-all-read" id="markAllRead">Marcar todas como leídas</a>
                        <?php endif; ?>
                    </div>

                    <div class="notification-list">
                        <?php if (empty($notificaciones_recientes)): ?>
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No tienes notificaciones</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notificaciones_recientes as $notif): ?>
                                <div class="notification-item <?php echo $notif['leida'] ? 'read' : 'unread'; ?>" data-id="<?php echo $notif['id']; ?>">
                                    <div class="notification-icon <?php echo strtolower($notif['tipo']); ?>">
                                        <i class="fas <?php echo getNotificationIcon($notif['tipo']); ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h4 class="notification-title"><?php echo htmlspecialchars($notif['titulo']); ?></h4>
                                        <p class="notification-message"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                        <span class="notification-time"><?php echo timeAgo($notif['fecha_envio']); ?></span>
                                    </div>
                                    <?php if (!$notif['leida']): ?>
                                        <button class="notification-mark-read" data-id="<?php echo $notif['id']; ?>" title="Marcar como leída">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($notificaciones_recientes)): ?>
                        <div class="notification-footer">
                            <a href="notificaciones.php" class="view-all-notifications">Ver todas las notificaciones</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Menú de Usuario -->
            <div class="header-user">
                <button class="user-btn" id="userMenuBtn">
                    <img src="<?php echo htmlspecialchars($usuario_foto); ?>" alt="Usuario" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($usuario_nombre); ?></span>
                    <i class="fas fa-chevron-down user-arrow"></i>
                </button>

                <!-- Dropdown de Usuario -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
                        <img src="<?php echo htmlspecialchars($usuario_foto); ?>" alt="Usuario" class="dropdown-avatar">
                        <div class="dropdown-user-info">
                            <h4><?php echo htmlspecialchars($usuario_nombre); ?></h4>
                            <p><?php echo htmlspecialchars($usuario_email); ?></p>
                        </div>
                    </div>

                    <div class="user-dropdown-menu">
                        <a href="perfil.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Mi Perfil</span>
                        </a>
                        
                        <?php if ($usuario_rol === 'CLIENTE'): ?>
                            <a href="mis-prestamos.php" class="dropdown-item">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Mis Préstamos</span>
                            </a>
                            <a href="documentos.php" class="dropdown-item">
                                <i class="fas fa-folder"></i>
                                <span>Mis Documentos</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($usuario_rol === 'ASESOR'): ?>
                            <a href="mis-clientes.php" class="dropdown-item">
                                <i class="fas fa-users"></i>
                                <span>Mis Clientes</span>
                            </a>
                            <a href="tareas.php" class="dropdown-item">
                                <i class="fas fa-tasks"></i>
                                <span>Mis Tareas</span>
                            </a>
                        <?php endif; ?>

                        <a href="configuracion-cuenta.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>

                        <a href="ayuda.php" class="dropdown-item">
                            <i class="fas fa-question-circle"></i>
                            <span>Ayuda y Soporte</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <a href="logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Scripts del Header -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Notificaciones
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
            userDropdown.classList.remove('active');
        });
    }

    // Toggle Menú Usuario
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenuBtn) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
            notificationDropdown.classList.remove('active');
        });
    }

    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (notificationDropdown) {
            notificationDropdown.classList.remove('active');
        }
        if (userDropdown) {
            userDropdown.classList.remove('active');
        }
    });

    // Prevenir que los dropdowns se cierren al hacer click dentro
    if (notificationDropdown) {
        notificationDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    if (userDropdown) {
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Marcar notificación como leída
    const markReadButtons = document.querySelectorAll('.notification-mark-read');
    markReadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const notifId = this.getAttribute('data-id');
            marcarNotificacionLeida(notifId, this);
        });
    });

    // Marcar todas como leídas
    const markAllReadBtn = document.getElementById('markAllRead');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            marcarTodasLeidas();
        });
    }

    // Click en notificación para ir al destino
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            const notifId = this.getAttribute('data-id');
            const url = this.getAttribute('data-url');
            
            // Marcar como leída
            if (!this.classList.contains('read')) {
                marcarNotificacionLeida(notifId);
            }
            
            // Redirigir si hay URL
            if (url) {
                window.location.href = url;
            }
        });
    });
});

// Función para marcar notificación como leída
function marcarNotificacionLeida(notifId, button = null) {
    fetch('ajax/marcar-notificacion-leida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notificacion_id: notifId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notifItem = document.querySelector(`.notification-item[data-id="${notifId}"]`);
            if (notifItem) {
                notifItem.classList.remove('unread');
                notifItem.classList.add('read');
                if (button) {
                    button.remove();
                }
            }
            actualizarContadorNotificaciones();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para marcar todas como leídas
function marcarTodasLeidas() {
    fetch('ajax/marcar-todas-leidas.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Actualizar contador de notificaciones
function actualizarContadorNotificaciones() {
    fetch('ajax/contar-notificaciones.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (data.total > 0) {
                if (badge) {
                    badge.textContent = data.total > 99 ? '99+' : data.total;
                } else {
                    const notificationBtn = document.getElementById('notificationBtn');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = data.total;
                    notificationBtn.appendChild(newBadge);
                }
            } else {
                if (badge) {
                    badge.remove();
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Actualizar notificaciones cada 30 segundos
setInterval(function() {
    actualizarContadorNotificaciones();
}, 30000);
</script>

<?php
/**
 * Funciones auxiliares para el header
 */

// Obtener icono según tipo de notificación
function getNotificationIcon($tipo) {
    $iconos = [
        'SOLICITUD_NUEVA' => 'fa-file-invoice',
        'SOLICITUD_ACTUALIZADA' => 'fa-edit',
        'PRESTAMO_APROBADO' => 'fa-check-circle',
        'PRESTAMO_RECHAZADO' => 'fa-times-circle',
        'CUOTA_PROXIMA' => 'fa-calendar-alt',
        'CUOTA_VENCIDA' => 'fa-exclamation-triangle',
        'PAGO_RECIBIDO' => 'fa-dollar-sign',
        'MENSAJE_CHAT' => 'fa-comment',
        'DOCUMENTO_REQUERIDO' => 'fa-file-upload',
        'SISTEMA' => 'fa-info-circle'
    ];
    return $iconos[$tipo] ?? 'fa-bell';
}

// Calcular tiempo transcurrido
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Hace un momento';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "Hace " . $minutes . " minuto" . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "Hace " . $hours . " hora" . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "Hace " . $days . " día" . ($days > 1 ? 's' : '');
    } else {
        return date('d/m/Y', $timestamp);
    }
}
?>