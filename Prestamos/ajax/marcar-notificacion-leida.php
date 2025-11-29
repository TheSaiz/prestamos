/**
 * =====================================================
 * ARCHIVO 1: ajax/marcar-notificacion-leida.php
 * =====================================================
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

$data = json_decode(file_get_contents('php://input'), true);
$notificacion_id = $data['notificacion_id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];

if (!$notificacion_id) {
    echo json_encode(['success' => false, 'message' => 'ID de notificación no válido']);
    exit;
}

try {
    $db = getDB();
    
    // Verificar que la notificación pertenece al usuario
    $notif = $db->selectOne(
        "SELECT id FROM notificaciones WHERE id = ? AND usuario_id = ?",
        [$notificacion_id, $usuario_id]
    );
    
    if (!$notif) {
        echo json_encode(['success' => false, 'message' => 'Notificación no encontrada']);
        exit;
    }
    
    // Marcar como leída
    $result = $db->update(
        "UPDATE notificaciones SET leida = 1, fecha_lectura = NOW() WHERE id = ?",
        [$notificacion_id]
    );
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>

<?php