<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../backend/connection.php";

$chat_id = intval($_GET["chat_id"] ?? 0);
$last_id = intval($_GET["last_id"] ?? 0);

if (!$chat_id) {
    echo json_encode(["success" => false]);
    exit;
}

try {
    // Obtener mensajes nuevos CON archivos adjuntos
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.chat_id,
            m.emisor,
            m.mensaje,
            m.fecha,
            m.tiene_archivo,
            a.id as archivo_id,
            a.nombre_original as archivo_nombre,
            a.ruta as archivo_url,
            a.tamano as archivo_tamano
        FROM mensajes m
        LEFT JOIN chat_archivos a ON a.mensaje_id = m.id
        WHERE m.chat_id = ?
          AND m.id > ?
          AND m.emisor = 'asesor'
        ORDER BY m.id ASC
    ");
    $stmt->execute([$chat_id, $last_id]);
    
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear respuesta con archivos
    $mensajes_formateados = [];
    
    foreach ($mensajes as $msg) {
        $mensaje_data = [
            "id" => (int)$msg["id"],
            "emisor" => $msg["emisor"],
            "mensaje" => $msg["mensaje"],
            "fecha" => $msg["fecha"],
            "tiene_archivo" => (bool)$msg["tiene_archivo"]
        ];
        
        // Si tiene archivo adjunto
        if ($msg["tiene_archivo"] && $msg["archivo_id"]) {
            $mensaje_data["archivo"] = [
                "id" => (int)$msg["archivo_id"],
                "nombre" => $msg["archivo_nombre"],
                "url" => $msg["archivo_url"],
                "tamano" => (int)$msg["archivo_tamano"]
            ];
        }
        
        $mensajes_formateados[] = $mensaje_data;
    }
    
    echo json_encode([
        "success" => true,
        "data" => $mensajes_formateados
    ]);
    
} catch (PDOException $e) {
    error_log("Error en get_new_messages: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "Error al obtener mensajes"
    ]);
}