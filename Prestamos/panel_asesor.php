<?php
session_start();

if (!isset($_SESSION['asesor_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'backend/connection.php';

$asesor_id     = $_SESSION['asesor_id'];
$asesor_nombre = $_SESSION['asesor_nombre'];

// LISTA DE CHATS AGRUPADOS POR DNI
$stmt = $pdo->prepare("
    SELECT 
        cd.dni,
        MIN(u.id) AS cliente_id_real,
        u.nombre AS cliente_nombre,
        u.telefono AS cliente_telefono,
        GROUP_CONCAT(c.id) AS chats_ids,
        d.nombre AS departamento_nombre,

        (
            SELECT c2.id 
            FROM chats c2
            WHERE c2.cliente_id IN (
                SELECT usuario_id FROM clientes_detalles WHERE dni = cd.dni
            )
            ORDER BY c2.fecha_inicio DESC
            LIMIT 1
        ) AS chat_id_representativo,

        (
            SELECT estado 
            FROM chats c2
            WHERE c2.cliente_id IN (
                SELECT usuario_id FROM clientes_detalles WHERE dni = cd.dni
            )
            ORDER BY c2.fecha_inicio DESC
            LIMIT 1
        ) AS estado,

        (
            SELECT fecha_inicio
            FROM chats c2
            WHERE c2.cliente_id IN (
                SELECT usuario_id FROM clientes_detalles WHERE dni = cd.dni
            )
            ORDER BY fecha_inicio DESC
            LIMIT 1
        ) AS fecha_inicio,

        (
            SELECT mensaje FROM mensajes 
            WHERE chat_id IN (
                SELECT id FROM chats WHERE cliente_id IN (
                    SELECT usuario_id FROM clientes_detalles WHERE dni = cd.dni
                )
            )
            ORDER BY fecha DESC LIMIT 1
        ) AS ultimo_mensaje,

        (
            SELECT fecha FROM mensajes 
            WHERE chat_id IN (
                SELECT id FROM chats WHERE cliente_id IN (
                    SELECT usuario_id FROM clientes_detalles WHERE dni = cd.dni
                )
            )
            ORDER BY fecha DESC LIMIT 1
        ) AS fecha_ultimo_mensaje,

        (
            SELECT COUNT(*) FROM mensajes 
            WHERE emisor = 'cliente' 
              AND chat_id IN (
                    SELECT id FROM chats WHERE cliente_id IN (
                        SELECT usuario_id FROM clientes_detalles WHERE dni = cd.dni
                    )
              )
              AND fecha > (
                    SELECT COALESCE(MAX(ultima_lectura_asesor), '1970-01-01') 
                    FROM chats 
                    WHERE cliente_id IN (SELECT usuario_id FROM clientes_detalles WHERE dni = cd.dni)
              )
        ) AS mensajes_nuevos

    FROM clientes_detalles cd
    INNER JOIN usuarios u ON u.id = cd.usuario_id
    LEFT JOIN chats c ON c.cliente_id = u.id
    LEFT JOIN departamentos d ON c.departamento_id = d.id
    WHERE cd.dni IS NOT NULL
    GROUP BY cd.dni
    ORDER BY fecha_ultimo_mensaje DESC
");
$stmt->execute();
$chats = $stmt->fetchAll();

// CHAT ACTIVO
$chat_activo_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : null;
$chat_activo    = null;
$mensajes       = [];

if ($chat_activo_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.nombre   AS cliente_nombre, 
               u.email    AS cliente_email, 
               u.telefono AS cliente_telefono,
               d.nombre   AS departamento_nombre,
               cd.dni     AS cliente_dni
        FROM chats c
        INNER JOIN usuarios u       ON c.cliente_id    = u.id
        LEFT JOIN departamentos d   ON c.departamento_id = d.id
        LEFT JOIN clientes_detalles cd ON cd.usuario_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$chat_activo_id]);
    $chat_activo = $stmt->fetch();

    if ($chat_activo) {
        // TODOS LOS MENSAJES DEL MISMO DNI (HISTORIAL COMPLETO)
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u.nombre AS usuario_nombre,
                   c.fecha_inicio AS chat_fecha_inicio,
                   a.id              AS archivo_id,
                   a.nombre_original,
                   a.nombre_guardado,
                   a.tipo_mime,
                   a.tamano,
                   a.ruta
            FROM mensajes m
            LEFT JOIN usuarios u      ON m.usuario_id = u.id
            LEFT JOIN chats c         ON m.chat_id    = c.id
            LEFT JOIN chat_archivos a ON a.mensaje_id = m.id
            WHERE c.cliente_id IN (
                SELECT usuario_id 
                FROM clientes_detalles 
                WHERE dni = (SELECT dni FROM clientes_detalles WHERE usuario_id = ? LIMIT 1)
            )
            ORDER BY m.fecha ASC
        ");
        $stmt->execute([$chat_activo['cliente_id']]);
        $mensajes = $stmt->fetchAll();

        // MARCAR COMO LEÍDO POR EL ASESOR
        $stmt = $pdo->prepare("
            UPDATE chats 
            SET ultima_lectura_asesor = NOW() 
            WHERE cliente_id IN (
                SELECT usuario_id FROM clientes_detalles 
                WHERE dni = (SELECT dni FROM clientes_detalles WHERE usuario_id = ? LIMIT 1)
            )
        ");
        $stmt->execute([$chat_activo['cliente_id']]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Asesor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif; }
        .notification-dot { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .chat-item:hover { background: #f3f4f6; }
        .chat-item.active { background: #e0f2fe; border-left: 3px solid #0284c7; }
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast-notification {
            animation: slideIn 0.3s ease-out;
        }

    .file-preview-box {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #fff;
        padding: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        width: fit-content;
        max-width: 240px;
    }
    .file-preview-image {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
    }
    .file-preview-info {
        display: flex;
        flex-direction: column;
        font-size: 12px;
    }
    </style>
</head>
<body class="bg-gray-50 h-screen overflow-hidden">
    <!-- TOP BAR -->
    <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="material-icons-outlined text-blue-600 text-2xl">support_agent</span>
                <h1 class="text-lg font-bold text-gray-800">Panel de Asesor</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button id="pending-chats-button"
                    class="px-3 py-1 bg-gray-300 text-gray-800 rounded-full text-sm font-semibold hover:bg-gray-400 transition"
                    onclick="openPendingChatsModal()">
                Sin chats pendientes
            </button>
            <div class="flex items-center gap-2 text-sm">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($asesor_nombre); ?></span>
            </div>
            <a href="logout.php" class="text-gray-600 hover:text-gray-800">
                <span class="material-icons-outlined">logout</span>
            </a>
        </div>
    </div>

    <!-- LAYOUT PRINCIPAL -->
    <div class="flex h-full min-h-0 overflow-hidden">
        <!-- COLUMNA IZQUIERDA: LISTA DE CHATS -->
        <div class="w-80 bg-white border-r border-gray-200 flex flex-col min-h-0">
            <div class="p-4 border-b border-gray-200">
                <div class="relative">
                    <span class="material-icons-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
                    <input type="text" id="search-chats" placeholder="Buscar..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="chat-list overflow-y-auto flex-1 min-h-0">
                <?php if (empty($chats)): ?>
                <div class="p-8 text-center">
                    <span class="material-icons-outlined text-gray-300 text-5xl mb-3">chat_bubble_outline</span>
                    <p class="text-sm text-gray-500">No hay conversaciones</p>
                </div>
                <?php else: ?>
                    <?php foreach ($chats as $chat): ?>
                    <a href="?chat_id=<?php echo $chat['chat_id_representativo']; ?>" 
                       class="chat-item block px-4 py-3 border-b border-gray-100 cursor-pointer transition <?php echo ($chat_activo_id == $chat['chat_id_representativo']) ? 'active' : ''; ?>">
                        <div class="flex items-start gap-3">
                            <div class="relative">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    <?php echo strtoupper(substr($chat['cliente_nombre'], 0, 2)); ?>
                                </div>
                                <?php if ($chat['estado'] === 'en_conversacion'): ?>
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-semibold text-sm text-gray-800 truncate">
                                        <?php echo htmlspecialchars($chat['cliente_nombre']); ?>
                                    </h3>
                                    <span class="text-xs text-gray-500">
                                        <?php 
                                        $fecha = $chat['fecha_ultimo_mensaje'] ?? $chat['fecha_inicio'];
                                        echo $fecha ? date('H:i', strtotime($fecha)) : '';
                                        ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate mb-1">
                                    <?php echo htmlspecialchars($chat['departamento_nombre']); ?>
                                </p>
                                <p class="text-xs text-gray-600 truncate">
                                    <?php echo htmlspecialchars($chat['ultimo_mensaje'] ?? 'Chat iniciado...'); ?>
                                </p>
                            </div>
                            <?php if ($chat['mensajes_nuevos'] > 0): ?>
                            <div class="flex-shrink-0 min-w-[20px] px-2 py-0.5 bg-blue-600 text-white text-xs font-bold rounded-full text-center">
                                <?php echo $chat['mensajes_nuevos']; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- COLUMNA CENTRAL: CHAT ACTIVO -->
        <?php if ($chat_activo): ?>
        <div class="flex-1 flex flex-col bg-gray-50 min-h-0 overflow-hidden">
            <!-- Header del chat -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                        <?php echo strtoupper(substr($chat_activo['cliente_nombre'], 0, 2)); ?>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-800"><?php echo htmlspecialchars($chat_activo['cliente_nombre']); ?></h2>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span><?php echo htmlspecialchars($chat_activo['departamento_nombre']); ?></span>
                            <?php if (!empty($chat_activo['estado'])): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold 
                                    <?php echo $chat_activo['estado'] === 'en_conversacion' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                    <?php echo htmlspecialchars($chat_activo['estado']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <div id="chat-messages" class="flex-1 overflow-y-auto px-6 py-4 min-h-0 max-h-[calc(100vh-220px)]">
                <?php foreach ($mensajes as $msg): ?>
                    <?php if ($msg['emisor'] === 'cliente'): ?>
                    <!-- Mensaje del cliente -->
                    <div class="flex justify-start mb-4">
                        <div class="flex gap-2 max-w-[60%]">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-semibold">
                                    <?php echo strtoupper(substr($chat_activo['cliente_nombre'], 0, 1)); ?>
                                </span>
                            </div>
                            <div>
                                <div class="bg-white rounded-2xl rounded-tl-none px-4 py-2.5 shadow-sm">
                                    <p class="text-sm text-gray-800"><?php echo nl2br(htmlspecialchars($msg['mensaje'])); ?></p>
                                    <?php if (!empty($msg['archivo_id']) && !empty($msg['ruta'])): ?>
                                    <a href="<?php echo htmlspecialchars($msg['ruta']); ?>" target="_blank" 
                                       class="inline-flex items-center gap-2 mt-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                        <span class="text-2xl">
                                            <?php 
                                            $ext = strtolower(pathinfo($msg['nombre_original'], PATHINFO_EXTENSION));
                                            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) echo '🖼️';
                                            elseif ($ext === 'pdf') echo '📕';
                                            elseif (in_array($ext, ['xls', 'xlsx'])) echo '📊';
                                            else echo '📄';
                                            ?>
                                        </span>
                                        <div class="text-left">
                                            <div class="text-xs font-semibold text-blue-700">
                                                <?php echo htmlspecialchars($msg['nombre_original']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?php 
                                                $size = (int)$msg['tamano'];
                                                if ($size < 1024) {
                                                    echo $size . ' B';
                                                } elseif ($size < 1048576) {
                                                    echo round($size/1024, 2) . ' KB';
                                                } else {
                                                    echo round($size/1048576, 2) . ' MB';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <span class="text-xs text-gray-400 mt-1 ml-1 block">
                                    <?php echo date('H:i', strtotime($msg['fecha'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Mensaje del asesor -->
                    <div class="flex justify-end mb-4">
                        <div class="flex gap-2 max-w-[60%]">
                            <div>
                                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none px-4 py-2.5 shadow-sm">
                                    <p class="text-sm"><?php echo nl2br(htmlspecialchars($msg['mensaje'])); ?></p>
                                    <?php if (!empty($msg['archivo_id']) && !empty($msg['ruta'])): ?>
                                    <a href="<?php echo htmlspecialchars($msg['ruta']); ?>" target="_blank" 
                                       class="inline-flex items-center gap-2 mt-2 px-3 py-2 bg-blue-700 rounded-lg hover:bg-blue-800 transition">
                                        <span class="text-2xl">
                                            <?php 
                                            $ext = strtolower(pathinfo($msg['nombre_original'], PATHINFO_EXTENSION));
                                            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) echo '🖼️';
                                            elseif ($ext === 'pdf') echo '📕';
                                            elseif (in_array($ext, ['xls', 'xlsx'])) echo '📊';
                                            else echo '📄';
                                            ?>
                                        </span>
                                        <div class="text-left">
                                            <div class="text-xs font-semibold text-white">
                                                <?php echo htmlspecialchars($msg['nombre_original']); ?>
                                            </div>
                                            <div class="text-xs text-blue-200">
                                                <?php 
                                                $size = (int)$msg['tamano'];
                                                if ($size < 1024) {
                                                    echo $size . ' B';
                                                } elseif ($size < 1048576) {
                                                    echo round($size/1024, 2) . ' KB';
                                                } else {
                                                    echo round($size/1048576, 2) . ' MB';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <span class="text-xs text-gray-400 mt-1 mr-1 block text-right">
                                    <?php echo date('H:i', strtotime($msg['fecha'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- INPUT MENSAJE + ADJUNTO -->
            <div class="bg-white border-t border-gray-200 px-6 py-3 flex-none">
                <div id="file-preview" class="hidden mb-3"></div>
                <div class="flex items-end gap-3 w-full">
                    <textarea id="message-input" rows="1" placeholder="Escribe tu mensaje..."
                        class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        style="max-height: 120px;"></textarea>

                    <input type="file" id="file-input" class="hidden">

                    <button type="button" id="attach-button"
                            class="px-3 py-3 bg-gray-200 rounded-xl hover:bg-gray-300 transition flex items-center">
                        <span class="material-icons-outlined">attach_file</span>
                    </button>

                    <button type="button" onclick="sendMessage()" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-2 font-semibold">
                        <span class="material-icons-outlined">send</span>
                        <span>Enviar</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: PERFIL DEL CLIENTE -->
        <div id="profile-panel" class="w-80 bg-white border-l border-gray-200 overflow-y-auto flex-none min-h-0">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-gray-800">Perfil del Cliente</h3>
                </div>

                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 
                                rounded-full flex items-center justify-center 
                                text-white font-bold text-2xl mx-auto mb-3">
                        <?php echo strtoupper(substr($chat_activo['cliente_nombre'], 0, 2)); ?>
                    </div>
                    <h2 class="font-bold text-gray-800 text-lg">
                        <?php echo htmlspecialchars($chat_activo['cliente_nombre']); ?>
                    </h2>
                    <p class="text-sm text-gray-500">
                        <?php echo htmlspecialchars($chat_activo['cliente_email']); ?>
                    </p>
                    <?php if (!empty($chat_activo['fecha_inicio'])): ?>
                    <p class="text-xs text-gray-400 mt-1">
                        Chat iniciado: <?php echo date('d/m/Y H:i', strtotime($chat_activo['fecha_inicio'])); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="pb-4 border-b border-gray-100">
                        <label class="text-xs text-gray-500 uppercase font-semibold">DNI</label>
                        <p class="text-sm text-gray-800 mt-1">
                            <?php echo $chat_activo['cliente_dni'] ?: 'N/A'; ?>
                        </p>
                    </div>
                    <div class="pb-4 border-b border-gray-100">
                        <label class="text-xs text-gray-500 uppercase font-semibold">Teléfono</label>
                        <p class="text-sm text-gray-800 mt-1">
                            <?php echo htmlspecialchars($chat_activo['cliente_telefono']); ?>
                        </p>
                    </div>
                    <div class="pb-4 border-b border-gray-100">
                        <label class="text-xs text-gray-500 uppercase font-semibold">Departamento</label>
                        <p class="text-sm text-gray-800 mt-1">
                            <?php echo htmlspecialchars($chat_activo['departamento_nombre']); ?>
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="text-xs text-gray-500 uppercase font-semibold mb-2">Ubicación aproximada</h4>
                    <p class="text-xs text-gray-500 mb-2">
                        Basado en IP o geolocalización (si está disponible).
                    </p>
                    <p class="text-sm text-gray-800 mb-2">
                        <?php
                        $ciudad = isset($chat_activo['ciudad']) ? $chat_activo['ciudad'] : null;
                        $pais   = isset($chat_activo['pais'])   ? $chat_activo['pais']   : null;
                        if ($ciudad || $pais) {
                            echo htmlspecialchars(trim($ciudad . ', ' . $pais, ', '));
                        } else {
                            echo 'Ubicación no disponible';
                        }
                        ?>
                    </p>
                    <?php
                    $tieneUbicacion = isset($chat_activo['latitud'], $chat_activo['longitud']) &&
                                      $chat_activo['latitud']  !== null && $chat_activo['longitud']  !== null &&
                                      $chat_activo['latitud']  !== ''   && $chat_activo['longitud']  !== '';
                    ?>
                    <?php if ($tieneUbicacion): ?>
                        <div id="mapa-ubicacion" class="w-full h-40 rounded-lg border border-gray-200 overflow-hidden"></div>
                    <?php else: ?>
                        <div class="w-full h-24 rounded-lg border border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-400">
                            Sin coordenadas almacenadas
                        </div>
                    <?php endif; ?>

                    <button onclick="openTransferModal()" 
                            class="w-full px-4 py-2.5 mb-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-sm font-semibold">
                        Transferir Chat
                    </button>

                    <button onclick="cerrarChat()" 
                            class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                            Cerrar Chat
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Sin chat seleccionado -->
        <div class="flex-1 flex items-center justify-center bg-gray-50">
            <div class="text-center">
                <span class="material-icons-outlined text-gray-300 text-6xl mb-4">chat</span>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Selecciona un chat</h3>
                <p class="text-sm text-gray-500">O espera a que llegue un nuevo cliente</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- MODAL: Chats pendientes -->
    <div id="pendingChatsModal"
         class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800">Chats pendientes</h2>
                <button onclick="closePendingChatsModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div id="pending-chats-list" class="space-y-3 max-h-96 overflow-y-auto">
                <p class="text-gray-500 text-sm">Buscando chats...</p>
            </div>
        </div>
    </div>

    <!-- MODAL: Transferir chat -->
    <div id="transferChatModal"
         class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800">Transferir chat</h2>
                <button onclick="closeTransferModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <p class="text-gray-600 text-sm mb-3">Selecciona el asesor al que deseas transferir este chat:</p>
            <div id="asesores-transfer-list" class="space-y-3 max-h-96 overflow-y-auto">
                <p class="text-gray-500 text-sm">Cargando asesores...</p>
            </div>
        </div>
    </div>

    <!-- Contenedor de notificaciones -->
    <div id="notificaciones-container" class="fixed top-20 right-6 z-50 space-y-3"></div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    const chatId   = <?php echo $chat_activo_id ?? 'null'; ?>;
    const asesorId = <?php echo $asesor_id; ?>;
    let lastMessageId = <?php echo empty($mensajes) ? 0 : end($mensajes)['id']; ?>;
    let knownPendingChats = new Set();

    const clientLat = <?php 
        echo ($chat_activo && isset($chat_activo['latitud']) && $chat_activo['latitud'] !== '' && $chat_activo['latitud'] !== null)
            ? floatval($chat_activo['latitud'])
            : 'null';
    ?>;
    const clientLng = <?php 
        echo ($chat_activo && isset($chat_activo['longitud']) && $chat_activo['longitud'] !== '' && $chat_activo['longitud'] !== null)
            ? floatval($chat_activo['longitud'])
            : 'null';
    ?>;
    const clientCity    = <?php echo json_encode($chat_activo['ciudad'] ?? null); ?>;
    const clientCountry = <?php echo json_encode($chat_activo['pais'] ?? null); ?>;

    let clientMapInitialized = false;
    let clientMap = null;

    function initClientMap() {
        if (clientMapInitialized) return;
        if (!window.L || !clientLat || !clientLng) return;

        const mapDiv = document.getElementById('mapa-ubicacion');
        if (!mapDiv) return;

        clientMap = L.map(mapDiv).setView([clientLat, clientLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(clientMap);

        const label = (clientCity || clientCountry) 
            ? `${clientCity ? clientCity + ', ' : ''}${clientCountry ? clientCountry : ''}`
            : 'Ubicación aproximada';

        L.marker([clientLat, clientLng]).addTo(clientMap)
            .bindPopup(label)
            .openPopup();

        clientMapInitialized = true;
    }

    // --------- ENVÍO DE MENSAJES + ADJUNTOS ---------

    async function sendMessage() {
    if (!chatId) return;

    const input     = document.getElementById('message-input');
    const fileInput = document.getElementById('file-input');

    const message = input.value.trim();
    const file    = fileInput?.files[0];

    // SI HAY ARCHIVO → usar upload_file.php
    if (file) {
        const formData = new FormData();
        formData.append("chat_id", chatId);
        formData.append("sender", "asesor");
        formData.append("message", message || "Archivo adjunto");
        formData.append("archivo", file);

        const response = await fetch('api/messages/upload_file.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            addFileToChat({
                url: data.data.archivo_url,
                name: data.data.archivo_nombre,
                size: file.size
            });
        } else {
            alert("Error al enviar archivo");
        }

        input.value = "";
        fileInput.value = "";
        return;
    }

    // SI NO HAY ARCHIVO → send_message.php (texto normal)
    if (message.length > 0) {
        const formData = new FormData();
        formData.append("chat_id", chatId);
        formData.append("sender", "asesor");
        formData.append("message", message);

        const response = await fetch('api/messages/send_message.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            addMessageToChat(message, "asesor");
        } else {
            alert("Error al enviar mensaje");
        }

        input.value = "";
    }
}


    const textarea = document.getElementById('message-input');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        textarea.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    // Botón de adjuntar
    const attachButton = document.getElementById('attach-button');
    const fileInput    = document.getElementById('file-input');

    if (attachButton && fileInput) {
        attachButton.addEventListener('click', () => {
            fileInput.click();
        });
    }

    function formatFileSize(size) {
        size = Number(size || 0);
        if (size < 1024) {
            return size + ' B';
        } else if (size < 1048576) {
            return (size / 1024).toFixed(2) + ' KB';
        } else {
            return (size / 1048576).toFixed(2) + ' MB';
        }
    }

    // Añadir mensaje de texto al chat (front)
    function addMessageToChat(message, sender) {
        const messagesDiv = document.getElementById('chat-messages');
        if (!messagesDiv) return;
        
        const messageDiv = document.createElement('div');
        const time = new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
        
        if (sender === 'asesor') {
            messageDiv.className = 'flex justify-end mb-4';
            messageDiv.innerHTML = `
                <div class="flex gap-2 max-w-[60%]">
                    <div>
                        <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none px-4 py-2.5 shadow-sm">
                            <p class="text-sm">${escapeHtml(message)}</p>
                        </div>
                        <span class="text-xs text-gray-400 mt-1 mr-1 block text-right">${time}</span>
                    </div>
                </div>
            `;
        } else {
            messageDiv.className = 'flex justify-start mb-4';
            messageDiv.innerHTML = `
                <div class="flex gap-2 max-w-[60%]">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xs font-semibold">C</span>
                    </div>
                    <div>
                        <div class="bg-white rounded-2xl rounded-tl-none px-4 py-2.5 shadow-sm">
                            <p class="text-sm text-gray-800">${escapeHtml(message)}</p>
                        </div>
                        <span class="text-xs text-gray-400 mt-1 ml-1 block">${time}</span>
                    </div>
                </div>
            `;
        }
        
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Añadir adjunto enviado por el asesor al chat (preview)
    function addFileToChat(file) {
    if (!file || !file.url || !file.name) return;

    const messagesDiv = document.getElementById('chat-messages');
    if (!messagesDiv) return;

    const time = new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
    const ext  = file.name.split('.').pop().toLowerCase();

    let icon = '📄';
    if (['png','jpg','jpeg','webp'].includes(ext)) icon = '🖼️';
    else if (ext === 'pdf') icon = '📕';
    else if (['xls','xlsx'].includes(ext)) icon = '📊';

    const sizeStr = formatFileSize(file.size);

    const div = document.createElement('div');
    div.className = 'flex justify-end mb-4';

    div.innerHTML = `
        <div class="flex gap-2 max-w-[60%]">
            <div>
                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none px-4 py-2.5 shadow-sm">

                    ${file.text ? `<p class="text-sm mb-2">${escapeHtml(file.text)}</p>` : ""}

                    <a href="${file.url}" target="_blank"
                       class="inline-flex items-center gap-2 px-3 py-2 bg-blue-700 rounded-lg hover:bg-blue-800 transition">
                        <span style="font-size:22px">${icon}</span>
                        <div class="text-left">
                            <div class="text-xs font-semibold text-white">${escapeHtml(file.name)}</div>
                            <div class="text-xs text-blue-200">${sizeStr}</div>
                        </div>
                    </a>
                </div>
                <span class="text-xs text-gray-400 mt-1 mr-1 block text-right">${time}</span>
            </div>
        </div>
    `;

    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}


    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    // POLLING MENSAJES NUEVOS DEL CLIENTE
    if (chatId) {
        setInterval(async () => {
            try {
                const response = await fetch(`api/messages/get_messages.php?chat_id=${chatId}&last_id=${lastMessageId}`);
                const data = await response.json();

                if (data.success && data.data.messages.length > 0) {
                    data.data.messages.forEach(msg => {
                        if (
                            msg.sender === 'cliente' &&
                            msg.message &&
                            msg.message.trim().toLowerCase() === 'termino la conversacion'
                        ) {
                            bloquearChat();
                            mostrarMensajeFinalizado();
                        }

                        if (msg.sender === 'cliente') {
                            addMessageToChat(msg.message, 'cliente');
                        }

                        lastMessageId = msg.id;
                    });
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }, 2000);

        const messagesDiv = document.getElementById('chat-messages');
        if (messagesDiv) messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Cerrar chat
    async function cerrarChat() {
        if (!confirm('¿Cerrar este chat?')) return;

        try {
            const formData = new FormData();
            formData.append('chat_id', chatId);

            const response = await fetch('api/chat/close_chat.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) window.location.href = 'panel_asesor.php';
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // POLLING CHATS PENDIENTES
    setInterval(async () => {
        try {
            const response = await fetch(`api/asesores/get_pending_chats.php?asesor_id=${asesorId}`);
            const data = await response.json();

            const badge = document.getElementById('pending-chats-button');

            if (data.success && data.data.chats.length > 0) {
                badge.textContent = `${data.data.chats.length} chat${data.data.chats.length > 1 ? 's' : ''} pendiente${data.data.chats.length > 1 ? 's' : ''}`;
                badge.classList.remove("bg-gray-300", "text-gray-800");
                badge.classList.add("bg-red-500", "text-white", "hover:bg-red-600");

                data.data.chats.forEach(chat => {
                    if (!knownPendingChats.has(chat.id)) {
                        knownPendingChats.add(chat.id);
                        showNewChatNotification(chat);
                    }
                });

            } else {
                badge.textContent = "Sin chats pendientes";
                badge.classList.remove("bg-red-500", "text-white", "hover:bg-red-600");
                badge.classList.add("bg-gray-300", "text-gray-800", "hover:bg-gray-400");
            }

        } catch (error) {
            console.error('Error polling chats:', error);
        }
    }, 3000);

    function showNewChatNotification(chat) {
        const container = document.getElementById('notificaciones-container');
        const existingNotif = document.getElementById('notif-' + chat.id);
        if (existingNotif) return;

        const notif = document.createElement('div');
        notif.id = 'notif-' + chat.id;
        notif.className = 'toast-notification bg-white shadow-2xl rounded-xl p-4 border-l-4 border-blue-600 max-w-sm';
        notif.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="material-icons-outlined text-blue-600">notifications_active</span>
                <div class="flex-1">
                    <h4 class="font-bold text-sm text-gray-800">Nuevo chat disponible</h4>
                    <p class="text-xs text-gray-600 mt-1">${escapeHtml(chat.cliente_nombre)} - ${escapeHtml(chat.departamento_nombre)}</p>
                    <button onclick="acceptChat(${chat.id})" 
                            class="mt-2 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition">
                        Aceptar Chat
                    </button>
                </div>
                <button onclick="dismissNotification(${chat.id})" class="text-gray-400 hover:text-gray-600">
                    <span class="material-icons-outlined text-sm">close</span>
                </button>
            </div>
        `;

        container.appendChild(notif);

        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuCzvLbjzgIG2S59+qSP');
            audio.play().catch(() => {});
        } catch(e) {}

        setTimeout(() => dismissNotification(chat.id), 30000);
    }

    function dismissNotification(chatId) {
        const notif = document.getElementById('notif-' + chatId);
        if (notif) {
            notif.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notif.remove(), 300);
        }
    }

    async function acceptChat(chatIdToAccept) {
        try {
            const response = await fetch('api/asesores/accept_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    chat_id: chatIdToAccept, 
                    asesor_id: asesorId 
                })
            });

            const data = await response.json();
            
            if (data.success) {
                dismissNotification(chatIdToAccept);
                window.location.href = `panel_asesor.php?chat_id=${chatIdToAccept}`;
            } else {
                alert(data.message || 'No se pudo aceptar el chat');
                dismissNotification(chatIdToAccept);
                knownPendingChats.delete(chatIdToAccept);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al aceptar el chat');
        }
    }

    // Filtro de búsqueda de chats
    document.getElementById('search-chats')?.addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        document.querySelectorAll('.chat-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? 'block' : 'none';
        });
    });

    // Inicializar mapa si hay coords
    if (clientLat && clientLng) {
        setTimeout(() => {
            initClientMap();
            if (clientMap) {
                setTimeout(() => clientMap.invalidateSize(), 200);
            }
        }, 200);
    }

    // Tecla ESC para salir de un chat
    (function() {
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
                const url = new URL(window.location.href);
                if (url.searchParams.has("chat_id")) {
                    url.searchParams.delete("chat_id");
                    window.location.href = url.pathname;
                }
            }
        });
    })();

    // MODAL: chats pendientes
    function openPendingChatsModal() {
        document.getElementById("pendingChatsModal").classList.remove("hidden");
        loadPendingChatsModal();
    }

    function closePendingChatsModal() {
        document.getElementById("pendingChatsModal").classList.add("hidden");
    }

    async function loadPendingChatsModal() {
        const container = document.getElementById("pending-chats-list");
        container.innerHTML = `<p class="text-gray-500 text-sm">Cargando...</p>`;

        try {
            const response = await fetch(`api/asesores/get_pending_chats.php?asesor_id=${asesorId}`);
            const data = await response.json();

            if (!data.success || data.data.chats.length === 0) {
                container.innerHTML = `<p class="text-gray-500 text-sm">No hay chats pendientes.</p>`;
                return;
            }

            container.innerHTML = "";

            data.data.chats.forEach(chat => {
                const item = document.createElement("div");
                item.className = "border p-3 rounded-lg flex justify-between items-center";

                item.innerHTML = `
                    <div>
                        <p class="font-semibold text-gray-800">${escapeHtml(chat.cliente_nombre)}</p>
                        <p class="text-xs text-gray-500">${escapeHtml(chat.departamento_nombre)}</p>
                    </div>
                    <button onclick="acceptChat(${chat.id})"
                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition">
                        Aceptar
                    </button>
                `;

                container.appendChild(item);
            });

        } catch (error) {
            container.innerHTML = `<p class='text-red-500 text-sm'>Error al cargar chats</p>`;
        }
    }

    // POLLING ESTADO DE CHATS (contadores de no leídos)
    setInterval(async () => {
        try {
            const response = await fetch("api/asesores/get_chats_status.php?asesor_id=" + asesorId);
            const data = await response.json();

            if (!data.success) return;

            const chatsStatus = data.data;

            chatsStatus.forEach(status => {
                const chatItem = document.querySelector(`.chat-item[href='?chat_id=${status.id}']`);
                if (!chatItem) return;

                let badge = chatItem.querySelector(".unread-badge");

                if (!badge) {
                    badge = document.createElement("div");
                    badge.className = "unread-badge flex-shrink-0 min-w-[20px] px-2 py-0.5 bg-blue-600 text-white text-xs font-bold rounded-full text-center ml-2";
                    const flexContainer = chatItem.querySelector(".flex.items-start");
                    if (flexContainer) {
                        flexContainer.appendChild(badge);
                    }
                }

                if (status.mensajes_nuevos > 0) {
                    badge.textContent = status.mensajes_nuevos;
                    badge.classList.remove("hidden");
                } else {
                    badge.classList.add("hidden");
                }
            });

        } catch (err) {
            console.error("Error actualizando contadores:", err);
        }

    }, 3000);

    // MODAL: Transferir chat
    function openTransferModal() {
        document.getElementById("transferChatModal").classList.remove("hidden");
        loadAsesoresForTransfer();
    }

    function closeTransferModal() {
        document.getElementById("transferChatModal").classList.add("hidden");
    }

    async function loadAsesoresForTransfer() {
        const container = document.getElementById("asesores-transfer-list");
        container.innerHTML = `<p class="text-gray-500 text-sm">Cargando asesores...</p>`;

        try {
            const response = await fetch(`api/asesores/list_asesores.php?asesor_id=${asesorId}`);
            const data = await response.json();

            if (!data.success) {
                container.innerHTML = `<p class="text-red-500">No hay asesores disponibles</p>`;
                return;
            }

            const asesores = data.data;
            container.innerHTML = "";

            if (asesores.length === 0) {
                container.innerHTML = `<p class="text-gray-500">No hay asesores disponibles.</p>`;
                return;
            }

            asesores.forEach(a => {
                const item = document.createElement("div");
                item.className = "border p-3 rounded-lg flex justify-between items-center";

                item.innerHTML = `
                    <div>
                        <p class="font-semibold text-gray-800">${escapeHtml(a.nombre)}</p>
                        <p class="text-xs text-gray-500">${escapeHtml(a.departamentos)}</p>
                    </div>
                    <button onclick="transferChat(${a.id})"
                            class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-xs font-semibold hover:bg-yellow-600 transition">
                        Transferir
                    </button>
                `;

                container.appendChild(item);
            });

        } catch (error) {
            container.innerHTML = `<p class="text-red-500">Error al cargar asesores</p>`;
        }
    }

    async function transferChat(asesorDestino) {
        if (!confirm("¿Confirmas la transferencia del chat?")) return;

        try {
            const response = await fetch("api/asesores/transfer_chat.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    chat_id: chatId,
                    asesor_destino: asesorDestino
                })
            });

            const data = await response.json();

            if (data.success) {
                closeTransferModal();
                alert("Chat transferido con éxito.");
                window.location.href = "panel_asesor.php";
            } else {
                alert("Error: " + data.message);
            }

        } catch (e) {
            alert("Error al transferir chat.");
        }
    }

    // Bloquear input si cliente terminó conversación
    function bloquearChat() {
        const textarea = document.getElementById("message-input");
        const btn = document.querySelector("button[onclick='sendMessage()']");

        if (textarea) {
            textarea.disabled = true;
            textarea.classList.add("bg-gray-200", "cursor-not-allowed");
            textarea.placeholder = "El cliente finalizó la conversación";
        }

        if (btn) {
            btn.disabled = true;
            btn.classList.add("opacity-50", "cursor-not-allowed");
        }
    }

    function mostrarMensajeFinalizado() {
        const messagesDiv = document.getElementById("chat-messages");

        const aviso = document.createElement("div");
        aviso.className = "text-center my-4";
        aviso.innerHTML = `
            <span class="px-4 py-2 bg-yellow-200 text-yellow-800 rounded-lg text-sm font-semibold">
                El usuario finalizó la conversación
            </span>
        `;

        messagesDiv.appendChild(aviso);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }


// --- VISTA PREVIA DE ARCHIVOS --- //
const fileInputPreview = document.getElementById("file-input");
const previewBox = document.getElementById("file-preview");

fileInputPreview.addEventListener("change", function () {
    const file = this.files[0];

    if (!file) {
        previewBox.classList.add("hidden");
        previewBox.innerHTML = "";
        return;
    }

    const ext = file.name.split(".").pop().toLowerCase();
    const sizeKB = (file.size / 1024).toFixed(1) + " KB";

    let previewHTML = "";

    if (["png", "jpg", "jpeg", "webp"].includes(ext)) {
        const imgURL = URL.createObjectURL(file);

        previewHTML = `
            <div class="file-preview-box shadow-sm">
                <img src="${imgURL}" class="file-preview-image">
                <div class="file-preview-info">
                    <strong>${file.name}</strong>
                    <span>${sizeKB}</span>
                </div>
                <button onclick="clearFilePreview()" class="ml-2 text-red-600 font-bold">×</button>
            </div>
        `;
    } else {
        const icon = ext === "pdf" ? "📕" :
                    ["xls", "xlsx"].includes(ext) ? "📊" :
                    "📄";

        previewHTML = `
            <div class="file-preview-box shadow-sm">
                <span style="font-size:28px">${icon}</span>
                <div class="file-preview-info">
                    <strong>${file.name}</strong>
                    <span>${sizeKB}</span>
                </div>
                <button onclick="clearFilePreview()" class="ml-2 text-red-600 font-bold">×</button>
            </div>
        `;
    }

    previewBox.innerHTML = previewHTML;
    previewBox.classList.remove("hidden");
});

function clearFilePreview() {
    fileInputPreview.value = "";
    previewBox.innerHTML = "";
    previewBox.classList.add("hidden");
}

    </script>
</body>
</html>
