/**
 * =====================================================
 * ARCHIVO 4: ajax/obtener-notificaciones.php
 * =====================================================
 * Para cargar más notificaciones o actualizar la lista
 */
?>
<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
    $db = getDB();
    
    $notificaciones = $db->select(
        "SELECT * FROM notificaciones 
         WHERE usuario_id = ? 
         ORDER BY fecha_envio DESC 
         LIMIT ? OFFSET ?",
        [$usuario_id, $limit, $offset]
    );
    
    // Función para calcular tiempo transcurrido
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
    
    // Función para obtener icono
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
    
    // Procesar notificaciones para incluir datos adicionales
    foreach ($notificaciones as &$notif) {
        $notif['tiempo_transcurrido'] = timeAgo($notif['fecha_envio']);
        $notif['icono'] = getNotificationIcon($notif['tipo']);
        $notif['tipo_lower'] = strtolower($notif['tipo']);
    }
    
    echo json_encode([
        'success' => true,
        'notificaciones' => $notificaciones,
        'total' => count($notificaciones)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>

<?php
/**
 * =====================================================
 * FUNCIONES AUXILIARES PARA CREAR NOTIFICACIONES
 * includes/funciones-notificaciones.php
 * =====================================================
 */
?>
<?php
/**
 * Crear una nueva notificación
 */
function crearNotificacion($usuario_id, $tipo, $titulo, $mensaje, $opciones = []) {
    try {
        $db = getDB();
        
        $datos = [
            'usuario_id' => $usuario_id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'icono' => $opciones['icono'] ?? null,
            'color' => $opciones['color'] ?? null,
            'url_destino' => $opciones['url'] ?? null,
            'entidad_tipo' => $opciones['entidad_tipo'] ?? null,
            'entidad_id' => $opciones['entidad_id'] ?? null,
            'prioridad' => $opciones['prioridad'] ?? 'NORMAL'
        ];
        
        $sql = "INSERT INTO notificaciones 
                (usuario_id, tipo, titulo, mensaje, icono, color, url_destino, entidad_tipo, entidad_id, prioridad) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $valores = [
            $datos['usuario_id'],
            $datos['tipo'],
            $datos['titulo'],
            $datos['mensaje'],
            $datos['icono'],
            $datos['color'],
            $datos['url_destino'],
            $datos['entidad_tipo'],
            $datos['entidad_id'],
            $datos['prioridad']
        ];
        
        $id = $db->insert($sql, $valores);
        
        // Si se requiere enviar email
        if (isset($opciones['enviar_email']) && $opciones['enviar_email']) {
            enviarNotificacionEmail($usuario_id, $titulo, $mensaje);
        }
        
        // Si se requiere enviar SMS
        if (isset($opciones['enviar_sms']) && $opciones['enviar_sms']) {
            enviarNotificacionSMS($usuario_id, $mensaje);
        }
        
        return $id;
        
    } catch (Exception $e) {
        error_log("Error al crear notificación: " . $e->getMessage());
        return false;
    }
}

/**
 * Notificación: Nueva solicitud de préstamo
 */
function notificarNuevaSolicitud($cliente_id, $numero_solicitud, $monto) {
    return crearNotificacion(
        $cliente_id,
        'SOLICITUD_NUEVA',
        'Solicitud Recibida',
        "Hemos recibido tu solicitud de préstamo por $" . number_format($monto, 2) . ". Número: #$numero_solicitud",
        [
            'url' => "mis-prestamos.php?solicitud=$numero_solicitud",
            'entidad_tipo' => 'SOLICITUD',
            'entidad_id' => null, // Se llenará con el ID de la solicitud
            'enviar_email' => true
        ]
    );
}

/**
 * Notificación: Préstamo aprobado
 */
function notificarPrestamoAprobado($cliente_id, $numero_prestamo, $monto) {
    return crearNotificacion(
        $cliente_id,
        'PRESTAMO_APROBADO',
        '¡Préstamo Aprobado!',
        "¡Felicitaciones! Tu préstamo por $" . number_format($monto, 2) . " ha sido aprobado.",
        [
            'url' => "mis-prestamos.php?prestamo=$numero_prestamo",
            'prioridad' => 'ALTA',
            'enviar_email' => true,
            'enviar_sms' => true
        ]
    );
}

/**
 * Notificación: Préstamo rechazado
 */
function notificarPrestamoRechazado($cliente_id, $numero_solicitud, $motivo) {
    return crearNotificacion(
        $cliente_id,
        'PRESTAMO_RECHAZADO',
        'Solicitud No Aprobada',
        "Tu solicitud #$numero_solicitud no ha sido aprobada. Motivo: $motivo",
        [
            'url' => "mis-prestamos.php?solicitud=$numero_solicitud",
            'enviar_email' => true
        ]
    );
}

/**
 * Notificación: Cuota próxima a vencer
 */
function notificarCuotaProxima($cliente_id, $numero_prestamo, $monto_cuota, $fecha_vencimiento) {
    return crearNotificacion(
        $cliente_id,
        'CUOTA_PROXIMA',
        'Recordatorio de Pago',
        "Tu cuota de $" . number_format($monto_cuota, 2) . " vence el " . date('d/m/Y', strtotime($fecha_vencimiento)),
        [
            'url' => "mis-pagos.php?prestamo=$numero_prestamo",
            'prioridad' => 'ALTA',
            'enviar_email' => true,
            'enviar_sms' => true
        ]
    );
}

/**
 * Notificación: Cuota vencida
 */
function notificarCuotaVencida($cliente_id, $numero_prestamo, $monto_cuota, $dias_mora) {
    return crearNotificacion(
        $cliente_id,
        'CUOTA_VENCIDA',
        'Cuota Vencida',
        "Tienes una cuota vencida de $" . number_format($monto_cuota, 2) . " con $dias_mora días de mora.",
        [
            'url' => "mis-pagos.php?prestamo=$numero_prestamo",
            'prioridad' => 'URGENTE',
            'enviar_email' => true,
            'enviar_sms' => true
        ]
    );
}

/**
 * Notificación: Pago recibido
 */
function notificarPagoRecibido($cliente_id, $numero_prestamo, $monto_pagado) {
    return crearNotificacion(
        $cliente_id,
        'PAGO_RECIBIDO',
        'Pago Recibido',
        "Hemos recibido tu pago de $" . number_format($monto_pagado, 2) . ". ¡Gracias!",
        [
            'url' => "mis-pagos.php?prestamo=$numero_prestamo",
            'enviar_email' => true
        ]
    );
}

/**
 * Notificación: Nuevo mensaje en chat
 */
function notificarNuevoMensaje($cliente_id, $remitente, $vista_previa) {
    return crearNotificacion(
        $cliente_id,
        'MENSAJE_CHAT',
        'Nuevo Mensaje',
        "$remitente te ha enviado un mensaje: \"$vista_previa\"",
        [
            'url' => "chat.php",
            'prioridad' => 'NORMAL'
        ]
    );
}

/**
 * Enviar notificación por email (implementación básica)
 */
function enviarNotificacionEmail($usuario_id, $asunto, $mensaje) {
    // Aquí implementarías el envío real de email
    // Usando PHPMailer, SendGrid, etc.
    return true;
}

/**
 * Enviar notificación por SMS (implementación básica)
 */
function enviarNotificacionSMS($usuario_id, $mensaje) {
    // Aquí implementarías el envío real de SMS
    // Usando Twilio, etc.
    return true;
}
?>