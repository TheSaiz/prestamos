<?php
session_start();

if (!isset($_SESSION['asesor_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'backend/connection.php';

$asesor_id = $_SESSION['asesor_id'];
$asesor_nombre = $_SESSION['asesor_nombre'];

// Obtener todos los chats del asesor (activos y cerrados)
$stmt = $pdo->prepare("
    SELECT c.*, 
           u.nombre as cliente_nombre, 
           u.telefono as cliente_telefono,
           d.nombre as departamento_nombre,
           (SELECT COUNT(*) FROM mensajes WHERE chat_id = c.id AND emisor = 'cliente' 
            AND fecha > COALESCE(c.ultima_lectura_asesor, '1970-01-01')) as mensajes_nuevos,
           (SELECT mensaje FROM mensajes WHERE chat_id = c.id ORDER BY fecha DESC LIMIT 1) as ultimo_mensaje,
           (SELECT fecha FROM mensajes WHERE chat_id = c.id ORDER BY fecha DESC LIMIT 1) as fecha_ultimo_mensaje
    FROM chats c
    INNER JOIN usuarios u ON c.cliente_id = u.id
    LEFT JOIN departamentos d ON c.departamento_id = d.id
    WHERE c.asesor_id = ?
    ORDER BY 
        CASE WHEN c.estado = 'en_conversacion' THEN 0 ELSE 1 END,
        COALESCE(c.fecha_cierre, c.fecha_inicio) DESC
");
$stmt->execute([$asesor_id]);
$chats = $stmt->fetchAll();

// Obtener chat activo
$chat_activo_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : (count($chats) > 0 ? $chats[0]['id'] : null);
$chat_activo = null;
$mensajes = [];

if ($chat_activo_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.nombre as cliente_nombre, 
               u.email as cliente_email, 
               u.telefono as cliente_telefono,
               d.nombre as departamento_nombre,
               cd.dni as cliente_dni
        FROM chats c
        INNER JOIN usuarios u ON c.cliente_id = u.id
        LEFT JOIN departamentos d ON c.departamento_id = d.id
        LEFT JOIN clientes_detalles cd ON cd.usuario_id = u.id
        WHERE c.id = ? AND c.asesor_id = ?
    ");
    $stmt->execute([$chat_activo_id, $asesor_id]);
    $chat_activo = $stmt->fetch();

    if ($chat_activo) {
        $stmt = $pdo->prepare("
            SELECT m.*, u.nombre as usuario_nombre
            FROM mensajes m
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.chat_id = ?
            ORDER BY m.fecha ASC
        ");
        $stmt->execute([$chat_activo_id]);
        $mensajes = $stmt->fetchAll();

        // Marcar como leído
        $stmt = $pdo->prepare("UPDATE chats SET ultima_lectura_asesor = NOW() WHERE id = ?");
        $stmt->execute([$chat_activo_id]);
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
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif; }
        .chat-list { height: calc(100vh - 140px); }
        .chat-messages { height: calc(100vh - 280px); }
        .notification-dot { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .chat-item:hover { background: #f3f4f6; }
        .chat-item.active { background: #e0f2fe; border-left: 3px solid #0284c7; }
        
        /* Notificación toast */
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast-notification {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Topbar -->
    <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="material-icons-outlined text-blue-600 text-2xl">support_agent</span>
                <h1 class="text-lg font-bold text-gray-800">Panel de Asesor</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div id="pending-chats-badge" class="hidden px-3 py-1 bg-red-500 text-white rounded-full text-sm font-semibold">
                0 chats pendientes
            </div>
            <div class="flex items-center gap-2 text-sm">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($asesor_nombre); ?></span>
            </div>
            <a href="logout.php" class="text-gray-600 hover:text-gray-800">
                <span class="material-icons-outlined">logout</span>
            </a>
        </div>
    </div>

    <div class="flex h-full">

        <!-- Sidebar izquierdo -->
        <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
            
            <div class="p-4 border-b border-gray-200">
                <div class="relative">
                    <span class="material-icons-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
                    <input type="text" id="search-chats" placeholder="Buscar..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="chat-list overflow-y-auto">
                <?php if (empty($chats)): ?>
                <div class="p-8 text-center">
                    <span class="material-icons-outlined text-gray-300 text-5xl mb-3">chat_bubble_outline</span>
                    <p class="text-sm text-gray-500">No hay conversaciones</p>
                </div>
                <?php else: ?>
                    <?php foreach ($chats as $chat): ?>
                    <a href="?chat_id=<?php echo $chat['id']; ?>" 
                       class="chat-item block px-4 py-3 border-b border-gray-100 cursor-pointer transition <?php echo $chat_activo_id === $chat['id'] ? 'active' : ''; ?>">
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
                                        echo date('H:i', strtotime($fecha)); 
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
                            <div class="notification-dot flex-shrink-0 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-xs font-bold"><?php echo $chat['mensajes_nuevos']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- Área principal -->
        <?php if ($chat_activo): ?>
        <div class="flex-1 flex flex-col bg-gray-50">

            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                        <?php echo strtoupper(substr($chat_activo['cliente_nombre'], 0, 2)); ?>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-800"><?php echo htmlspecialchars($chat_activo['cliente_nombre']); ?></h2>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span><?php echo htmlspecialchars($chat_activo['departamento_nombre']); ?></span>
                        </div>
                    </div>
                </div>
                <button onclick="toggleProfile()" class="px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                    Ver detalles
                </button>
            </div>

            <div id="chat-messages" class="chat-messages flex-1 overflow-y-auto px-6 py-4">
                <?php foreach ($mensajes as $msg): ?>
                    <?php if ($msg['emisor'] === 'cliente'): ?>
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
                                </div>
                                <span class="text-xs text-gray-400 mt-1 ml-1 block">
                                    <?php echo date('H:i', strtotime($msg['fecha'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="flex justify-end mb-4">
                        <div class="flex gap-2 max-w-[60%]">
                            <div>
                                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none px-4 py-2.5 shadow-sm">
                                    <p class="text-sm"><?php echo nl2br(htmlspecialchars($msg['mensaje'])); ?></p>
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

            <div class="bg-white border-t border-gray-200 px-6 py-4">
                <div class="flex items-end gap-3">
                    <textarea id="message-input" rows="1" placeholder="Escribe tu mensaje..."
                              class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                              style="max-height: 120px;"></textarea>
                    <button onclick="sendMessage()" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-2 font-semibold">
                        <span class="material-icons-outlined">send</span>
                        <span>Enviar</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Panel derecho -->
        <div id="profile-panel" class="w-80 bg-white border-l border-gray-200 overflow-y-auto hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-gray-800">Perfil del Cliente</h3>
                    <button onclick="toggleProfile()" class="text-gray-400 hover:text-gray-600">
                        <span class="material-icons-outlined">close</span>
                    </button>
                </div>

                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mx-auto mb-3">
                        <?php echo strtoupper(substr($chat_activo['cliente_nombre'], 0, 2)); ?>
                    </div>
                    <h2 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($chat_activo['cliente_nombre']); ?></h2>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($chat_activo['cliente_email']); ?></p>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="pb-4 border-b border-gray-100">
                        <label class="text-xs text-gray-500 uppercase font-semibold">DNI</label>
                        <p class="text-sm text-gray-800 mt-1"><?php echo htmlspecialchars($chat_activo['cliente_dni'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="pb-4 border-b border-gray-100">
                        <label class="text-xs text-gray-500 uppercase font-semibold">Teléfono</label>
                        <p class="text-sm text-gray-800 mt-1"><?php echo htmlspecialchars($chat_activo['cliente_telefono']); ?></p>
                    </div>
                    <div class="pb-4 border-b border-gray-100">
                        <label class="text-xs text-gray-500 uppercase font-semibold">Departamento</label>
                        <p class="text-sm text-gray-800 mt-1"><?php echo htmlspecialchars($chat_activo['departamento_nombre']); ?></p>
                    </div>
                </div>

                <button onclick="cerrarChat()" 
                        class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                    Cerrar Chat
                </button>
            </div>
        </div>

        <?php else: ?>
        <div class="flex-1 flex items-center justify-center bg-gray-50">
            <div class="text-center">
                <span class="material-icons-outlined text-gray-300 text-6xl mb-4">chat</span>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Selecciona un chat</h3>
                <p class="text-sm text-gray-500">O espera a que llegue un nuevo cliente</p>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Container de notificaciones -->
    <div id="notificaciones-container" class="fixed top-20 right-6 z-50 space-y-3"></div>

    <script>
        const chatId = <?php echo $chat_activo_id ?? 'null'; ?>;
        const asesorId = <?php echo $asesor_id; ?>;
        let lastMessageId = <?php echo empty($mensajes) ? 0 : end($mensajes)['id']; ?>;
        let knownPendingChats = new Set();

        function toggleProfile() {
            document.getElementById('profile-panel').classList.toggle('hidden');
        }

        async function sendMessage() {
            if (!chatId) return;
            
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            
            if (!message) return;

            try {
                const formData = new FormData();
                formData.append('chat_id', chatId);
                formData.append('sender', 'asesor');
                formData.append('message', message);

                const response = await fetch('api/messages/send_message.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    input.value = '';
                    input.style.height = 'auto';
                    addMessageToChat(message, 'asesor');
                }
            } catch (error) {
                console.error('Error:', error);
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

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Polling para nuevos mensajes
        if (chatId) {
            setInterval(async () => {
                try {
                    const response = await fetch(`api/messages/get_messages.php?chat_id=${chatId}&last_id=${lastMessageId}`);
                    const data = await response.json();

                    if (data.success && data.data.messages.length > 0) {
                        data.data.messages.forEach(msg => {
                            if (msg.sender === 'client' || msg.sender === 'cliente') {
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

        // POLLING PARA CHATS PENDIENTES - CORREGIDO
        setInterval(async () => {
            try {
                const response = await fetch(`api/asesores/get_pending_chats.php?asesor_id=${asesorId}`);
                const data = await response.json();

                if (data.success && data.data.chats.length > 0) {
                    // Actualizar badge
                    const badge = document.getElementById('pending-chats-badge');
                    badge.textContent = `${data.data.chats.length} chat${data.data.chats.length > 1 ? 's' : ''} pendiente${data.data.chats.length > 1 ? 's' : ''}`;
                    badge.classList.remove('hidden');
                    
                    // Mostrar notificación solo para chats nuevos
                    data.data.chats.forEach(chat => {
                        if (!knownPendingChats.has(chat.id)) {
                            knownPendingChats.add(chat.id);
                            showNewChatNotification(chat);
                        }
                    });
                } else {
                    document.getElementById('pending-chats-badge').classList.add('hidden');
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
                            Aceptar
                        </button>
                    </div>
                    <button onclick="dismissNotification(${chat.id})" class="text-gray-400 hover:text-gray-600">
                        <span class="material-icons-outlined text-sm">close</span>
                    </button>
                </div>
            `;
            container.appendChild(notif);

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

        document.getElementById('search-chats')?.addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            document.querySelectorAll('.chat-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(search) ? 'block' : 'none';
            });
        });
    </script>

</body>
</html>