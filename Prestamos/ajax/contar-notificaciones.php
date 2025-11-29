* =====================================================
 * ARCHIVO 3: ajax/contar-notificaciones.php
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
    
    $total = $db->count(
        "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = 0",
        [$usuario_id]
    );
    
    echo json_encode([
        'success' => true,
        'total' => (int)$total
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>

<?php