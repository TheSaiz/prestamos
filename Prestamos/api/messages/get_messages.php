<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$chat_id = intval($_GET["chat_id"] ?? 0);
$last_id = intval($_GET["last_id"] ?? 0);

if (!$chat_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "chat_id es obligatorio"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE id = ?");
    $stmt->execute([$chat_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    // Obtener mensajes
    $stmt = $pdo->prepare("
        SELECT m.id, m.emisor as sender, m.mensaje as message, m.fecha as timestamp, m.tiene_archivo,
               a.id as archivo_id, a.nombre_original, a.nombre_guardado, a.tipo_mime, a.tamano, a.ruta
        FROM mensajes m
        LEFT JOIN chat_archivos a ON a.mensaje_id = m.id
        WHERE m.chat_id = ? AND m.id > ?
        ORDER BY m.id ASC
    ");
    $stmt->execute([$chat_id, $last_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar mensajes para incluir información de archivos
    foreach ($messages as &$msg) {
        if ($msg['tiene_archivo'] && $msg['archivo_id']) {
            $msg['archivo'] = [
                'id' => $msg['archivo_id'],
                'nombre' => $msg['nombre_original'],
                'tipo' => $msg['tipo_mime'],
                'tamano' => $msg['tamano'],
                'url' => $msg['ruta']
            ];
        } else {
            $msg['archivo'] = null;
        }
        
        // Limpiar campos innecesarios
        unset($msg['archivo_id'], $msg['nombre_original'], $msg['nombre_guardado'], 
              $msg['tipo_mime'], $msg['tamano'], $msg['ruta']);
    }

    $new_last_id = $last_id;
    if (count($messages) > 0) {
        $new_last_id = $messages[count($messages) - 1]['id'];
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "chat_id" => $chat_id,
            "last_id" => $new_last_id,
            "messages" => $messages
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener mensajes"
    ]);
}
?>