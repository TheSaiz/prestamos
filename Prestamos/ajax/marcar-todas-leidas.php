/**
 * =====================================================
 * ARCHIVO 2: ajax/marcar-todas-leidas.php
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

$usuario_id = $_SESSION['usuario_id'];

try {
    $db = getDB();
    
    $result = $db->update(
        "UPDATE notificaciones 
         SET leida = 1, fecha_lectura = NOW() 
         WHERE usuario_id = ? AND leida = 0",
        [$usuario_id]
    );
    
    if ($result !== false) {
        echo json_encode([
            'success' => true, 
            'message' => 'Todas las notificaciones marcadas como leídas',
            'updated' => $result
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>

<?php