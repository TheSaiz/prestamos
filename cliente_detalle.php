<?php
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'asesor'], true)) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/backend/connection.php';

$chat_id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario_id  = (int)$_SESSION['usuario_id'];
$usuario_rol = (string)$_SESSION['usuario_rol'];

if ($chat_id <= 0) {
    header("Location: clientes.php");
    exit;
}

// Helper (compatible PHP 7.x)
function containsInsensitive($haystack, $needle) {
    if ($haystack === null || $needle === null) return false;
    $haystack = (string)$haystack;
    $needle   = (string)$needle;
    if ($needle === '') return false;
    return stripos($haystack, $needle) !== false;
}

// =====================================================
// OBTENER INFORMACIÓN DEL CLIENTE (AJUSTADO A TU BD)
// =====================================================
try {
    $stmt = $pdo->prepare("
        SELECT 
            c.id as chat_id,
            c.fecha_inicio,
            c.fecha_cierre,
            c.estado,
            c.estado_solicitud,
            c.prioridad,
            c.score,
            c.cuil_validado,
            c.nombre_validado,
            c.situacion_laboral,
            c.banco,
            c.ciudad,
            c.pais,
            c.latitud,
            c.longitud,
            c.ip_cliente,
            u.id as cliente_id,
            u.nombre as cliente_nombre,
            u.apellido as cliente_apellido,
            u.telefono,
            u.email,
            u.fecha_registro,
            cd.dni,
            d.nombre as departamento_nombre,
            asesor.id as asesor_id,
            asesor.nombre as asesor_nombre,
            asesor.apellido as asesor_apellido
        FROM chats c
        INNER JOIN usuarios u ON c.cliente_id = u.id
        LEFT JOIN clientes_detalles cd ON cd.usuario_id = u.id
        LEFT JOIN departamentos d ON c.departamento_id = d.id
        LEFT JOIN usuarios asesor ON c.asesor_id = asesor.id
        WHERE c.id = ?
        LIMIT 1
    ");
    $stmt->execute([$chat_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error SQL cliente: " . htmlspecialchars($e->getMessage());
    exit;
}

if (!$cliente) {
    header("Location: clientes.php");
    exit;
}

// Si es asesor, verificar que el chat esté asignado a él
if ($usuario_rol === 'asesor' && (int)$cliente['asesor_id'] !== $usuario_id) {
    header("Location: clientes.php");
    exit;
}

// =====================================================
// OBTENER RESPUESTAS DEL CHATBOT (pregunta_id -> chatbot_flujo)
// =====================================================
try {
    $stmt = $pdo->prepare("
        SELECT 
            f.pregunta,
            r.respuesta,
            r.fecha
        FROM chatbot_respuestas r
        INNER JOIN chatbot_flujo f ON f.id = r.pregunta_id
        WHERE r.chat_id = ?
        ORDER BY r.fecha ASC
    ");
    $stmt->execute([$chat_id]);
    $respuestas_chatbot = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $respuestas_chatbot = [];
}

// =====================================================
// OBTENER MENSAJES DEL CHAT (AJUSTADO A TU BD)
// mensajes: emisor, usuario_id, fecha, mensaje
// =====================================================
try {
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.mensaje,
            m.fecha,
            m.emisor,
            m.usuario_id,
            u.nombre as emisor_nombre
        FROM mensajes m
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        WHERE m.chat_id = ?
        ORDER BY m.fecha ASC
    ");
    $stmt->execute([$chat_id]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $mensajes = [];
}

// =====================================================
// OBTENER NOTAS INTERNAS
// =====================================================
try {
    $stmt = $pdo->prepare("
        SELECT 
            n.id,
            n.nota,
            n.tipo,
            n.fecha,
            u.nombre as usuario_nombre,
            u.apellido as usuario_apellido
        FROM cliente_notas n
        INNER JOIN usuarios u ON n.usuario_id = u.id
        WHERE n.chat_id = ?
        ORDER BY n.fecha DESC
    ");
    $stmt->execute([$chat_id]);
    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $notas = [];
}

// =====================================================
// OBTENER TAGS
// =====================================================
try {
    $stmt = $pdo->prepare("
        SELECT id, tag, color
        FROM cliente_tags
        WHERE chat_id = ?
        ORDER BY fecha DESC
    ");
    $stmt->execute([$chat_id]);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $tags = [];
}

// =====================================================
// OBTENER EVENTOS/TIMELINE
// =====================================================
try {
    $stmt = $pdo->prepare("
        SELECT 
            e.id,
            e.tipo,
            e.descripcion,
            e.fecha,
            u.nombre as usuario_nombre
        FROM cliente_eventos e
        LEFT JOIN usuarios u ON e.usuario_id = u.id
        WHERE e.chat_id = ?
        ORDER BY e.fecha DESC
        LIMIT 50
    ");
    $stmt->execute([$chat_id]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $eventos = [];
}

// =====================================================
// OBTENER LISTA DE ASESORES (usuarios.estado existe)
// =====================================================
try {
    $stmt = $pdo->query("
        SELECT id, nombre, apellido 
        FROM usuarios 
        WHERE rol = 'asesor' AND estado = 'activo'
        ORDER BY nombre
    ");
    $asesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $asesores = [];
}

// =====================================================
// CALCULAR SCORE AUTOMÁTICO (ROBUSTO)
// =====================================================
function calcularScore($cliente) {
    $score = 0;

    $banco = isset($cliente['banco']) ? (string)$cliente['banco'] : '';
    $sit   = isset($cliente['situacion_laboral']) ? (string)$cliente['situacion_laboral'] : '';

    // Banco (+20 nacion, +15 galicia, +10 otros)
    if (containsInsensitive($banco, 'nacion')) {
        $score += 20;
    } elseif (containsInsensitive($banco, 'galicia')) {
        $score += 15;
    } elseif ($banco !== '') {
        $score += 10;
    }

    // Situación laboral (+30 relación dependencia, +25 jubilado, +15 otros)
    if (containsInsensitive($sit, 'relacion') || containsInsensitive($sit, 'dependencia') || containsInsensitive($sit, 'recibo')) {
        $score += 30;
    } elseif (containsInsensitive($sit, 'jubil')) {
        $score += 25;
    } elseif ($sit !== '') {
        $score += 15;
    }

    // CUIL validado (+15)  (en tu BD es varchar, no boolean)
    if (!empty($cliente['cuil_validado'])) {
        $score += 15;
    }

    // Nombre validado (+10) (en tu BD es varchar, no boolean)
    if (!empty($cliente['nombre_validado'])) {
        $score += 10;
    }

    // Email válido (+10)
    if (!empty($cliente['email']) && filter_var($cliente['email'], FILTER_VALIDATE_EMAIL)) {
        $score += 10;
    }

    // Teléfono completo (+10)
    if (!empty($cliente['telefono']) && strlen(preg_replace('/\D+/', '', $cliente['telefono'])) >= 10) {
        $score += 10;
    }

    return min(100, $score);
}

$score_calculado = calcularScore($cliente);

// Actualizar score si es diferente
try {
    $score_actual = isset($cliente['score']) ? (int)$cliente['score'] : 0;
    if ((int)$score_calculado !== $score_actual) {
        $stmt = $pdo->prepare("UPDATE chats SET score = ? WHERE id = ?");
        $stmt->execute([(int)$score_calculado, (int)$chat_id]);
        $cliente['score'] = (int)$score_calculado;
    }
} catch (Throwable $e) {
    // no cortamos la página por un update de score
}

$message = isset($_SESSION['message']) ? (string)$_SESSION['message'] : '';
unset($_SESSION['message']);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - <?php echo htmlspecialchars($cliente['cliente_nombre']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 300px; border-radius: 0.75rem; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 0;
            bottom: -20px;
            width: 2px;
            background: #e5e7eb;
        }
        .timeline-item:last-child::before {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50">

    <?php include 'sidebar.php'; ?>

    <div class="ml-64">
        <!-- Header -->
        <nav class="bg-white shadow-md sticky top-0 z-40">
            <div class="max-w-full mx-auto px-6 py-3 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="clientes.php" class="text-gray-600 hover:text-gray-800 transition">
                        <span class="material-icons-outlined">arrow_back</span>
                    </a>
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-blue-600 text-3xl">person</span>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($cliente['cliente_nombre']); ?></h1>
                            <p class="text-sm text-gray-500">DNI: <?php echo htmlspecialchars($cliente['dni'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="imprimirReporte()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                        <span class="material-icons-outlined">print</span>
                        Imprimir
                    </button>
                </div>
            </div>
        </nav>

        <?php if ($message): ?>
        <div class="max-w-full mx-auto px-6 py-3">
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined">check_circle</span>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="max-w-full mx-auto px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Columna Izquierda: Info Principal -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Card: Información del Cliente -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <span class="material-icons-outlined text-blue-600">account_circle</span>
                                Información del Cliente
                            </h2>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">Score:</span>
                                <div class="w-24 h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-yellow-400 to-green-500" 
                                         style="width: <?php echo $cliente['score']; ?>%"></div>
                                </div>
                                <span class="text-lg font-bold text-gray-800"><?php echo $cliente['score']; ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Nombre Completo</p>
                                <p class="text-base font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($cliente['cliente_nombre'] . ' ' . ($cliente['cliente_apellido'] ?? '')); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">DNI</p>
                                <p class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($cliente['dni'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">CUIL</p>
                                <p class="text-base font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($cliente['cuil'] ?? 'N/A'); ?>
                                    <?php if ($cliente['cuil_validado']): ?>
                                    <span class="ml-2 text-green-600" title="Validado">✓</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Teléfono</p>
                                <p class="text-base font-semibold text-gray-800">
                                    <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?>
                                    </a>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Email</p>
                                <p class="text-base font-semibold text-gray-800">
                                    <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" class="text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($cliente['email'] ?? 'N/A'); ?>
                                    </a>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Situación Laboral</p>
                                <p class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($cliente['situacion_laboral'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Banco</p>
                                <p class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($cliente['banco'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Ubicación</p>
                                <p class="text-base font-semibold text-gray-800">
                                    <?php echo htmlspecialchars(($cliente['ciudad'] ?? 'N/A') . ', ' . ($cliente['pais'] ?? '')); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Fecha Registro</p>
                                <p class="text-base font-semibold text-gray-800">
                                    <?php echo date('d/m/Y H:i', strtotime($cliente['fecha_inicio'])); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">IP</p>
                                <p class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($cliente['ip'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Respuestas del Chatbot -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <span class="material-icons-outlined text-purple-600">smart_toy</span>
                            Respuestas del Chatbot
                        </h2>
                        <div class="space-y-4">
                            <?php if (empty($respuestas_chatbot)): ?>
                            <p class="text-gray-500 text-center py-4">No hay respuestas registradas</p>
                            <?php else: ?>
                                <?php foreach ($respuestas_chatbot as $resp): ?>
                                <div class="border-l-4 border-purple-500 pl-4 py-2">
                                    <p class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($resp['pregunta']); ?></p>
                                    <p class="text-base text-gray-900 mt-1"><?php echo htmlspecialchars($resp['respuesta']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('d/m/Y H:i', strtotime($resp['fecha_respuesta'])); ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card: Historial de Chat -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <span class="material-icons-outlined text-green-600">chat</span>
                            Historial de Conversación
                        </h2>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php if (empty($mensajes)): ?>
                            <p class="text-gray-500 text-center py-4">No hay mensajes en el chat</p>
                            <?php else: ?>
                                <?php foreach ($mensajes as $msg): ?>
                                <div class="flex <?php echo $msg['emisor_tipo'] === 'cliente' ? 'justify-start' : 'justify-end'; ?>">
                                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg <?php echo $msg['emisor_tipo'] === 'cliente' ? 'bg-gray-100' : 'bg-blue-600 text-white'; ?>">
                                        <p class="text-sm"><?php echo htmlspecialchars($msg['mensaje']); ?></p>
                                        <p class="text-xs opacity-70 mt-1">
                                            <?php echo date('d/m H:i', strtotime($msg['fecha_envio'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card: Mapa de Ubicación -->
                    <?php if (!empty($cliente['latitud']) && !empty($cliente['longitud'])): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <span class="material-icons-outlined text-red-600">location_on</span>
                            Ubicación Geográfica
                        </h2>
                        <div id="map"></div>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Columna Derecha: Acciones y Notas -->
                <div class="space-y-6">
                    
                    <!-- Card: Acciones Rápidas -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Acciones Rápidas</h2>
                        
                        <!-- Cambiar Estado -->
                        <form method="POST" action="clientes.php" class="mb-4">
                            <input type="hidden" name="action" value="cambiar_estado">
                            <input type="hidden" name="chat_id" value="<?php echo $chat_id; ?>">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Estado de Solicitud</label>
                            <select name="estado_solicitud" onchange="this.form.submit()" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="nuevo" <?php echo $cliente['estado_solicitud'] === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                                <option value="contactado" <?php echo $cliente['estado_solicitud'] === 'contactado' ? 'selected' : ''; ?>>Contactado</option>
                                <option value="documentacion" <?php echo $cliente['estado_solicitud'] === 'documentacion' ? 'selected' : ''; ?>>Documentación</option>
                                <option value="analisis" <?php echo $cliente['estado_solicitud'] === 'analisis' ? 'selected' : ''; ?>>Análisis</option>
                                <option value="aprobado" <?php echo $cliente['estado_solicitud'] === 'aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                                <option value="rechazado" <?php echo $cliente['estado_solicitud'] === 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                                <option value="desistio" <?php echo $cliente['estado_solicitud'] === 'desistio' ? 'selected' : ''; ?>>Desistió</option>
                            </select>
                        </form>

                        <!-- Cambiar Prioridad -->
                        <form method="POST" action="clientes.php" class="mb-4">
                            <input type="hidden" name="action" value="cambiar_prioridad">
                            <input type="hidden" name="chat_id" value="<?php echo $chat_id; ?>">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Prioridad</label>
                            <select name="prioridad" onchange="this.form.submit()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="baja" <?php echo $cliente['prioridad'] === 'baja' ? 'selected' : ''; ?>>Baja</option>
                                <option value="media" <?php echo $cliente['prioridad'] === 'media' ? 'selected' : ''; ?>>Media</option>
                                <option value="alta" <?php echo $cliente['prioridad'] === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                <option value="urgente" <?php echo $cliente['prioridad'] === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                            </select>
                        </form>

                        <!-- Asignar Asesor -->
                        <?php if ($usuario_rol === 'admin'): ?>
                        <form method="POST" action="clientes.php" class="mb-4">
                            <input type="hidden" name="action" value="asignar_asesor">
                            <input type="hidden" name="chat_id" value="<?php echo $chat_id; ?>">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Asesor Asignado</label>
                            <select name="asesor_id" onchange="this.form.submit()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Sin asignar</option>
                                <?php foreach ($asesores as $asesor): ?>
                                <option value="<?php echo $asesor['id']; ?>" 
                                        <?php echo $cliente['asesor_id'] == $asesor['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($asesor['nombre'] . ' ' . ($asesor['apellido'] ?? '')); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <?php endif; ?>

                        <!-- Botones de Contacto -->
                        <div class="space-y-2">
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $cliente['telefono']); ?>" 
                               target="_blank"
                               class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <span class="material-icons-outlined">chat</span>
                                WhatsApp
                            </a>
                            <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" 
                               class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <span class="material-icons-outlined">email</span>
                                Enviar Email
                            </a>
                            <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" 
                               class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                <span class="material-icons-outlined">phone</span>
                                Llamar
                            </a>
                        </div>
                    </div>

                    <!-- Card: Etiquetas -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Etiquetas</h2>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($tags as $tag): ?>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold text-white"
                                  style="background-color: <?php echo htmlspecialchars($tag['color']); ?>">
                                <?php echo htmlspecialchars($tag['tag']); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <form method="POST" action="clientes.php" class="flex gap-2">
                            <input type="hidden" name="action" value="agregar_tag">
                            <input type="hidden" name="chat_id" value="<?php echo $chat_id; ?>">
                            <input type="text" name="tag" placeholder="Nueva etiqueta..." required
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <input type="color" name="color" value="#3b82f6" class="w-10 h-10 border-0 rounded-lg cursor-pointer">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <span class="material-icons-outlined text-sm">add</span>
                            </button>
                        </form>
                    </div>

                    <!-- Card: Notas Internas -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Notas Internas</h2>
                        
                        <form method="POST" action="clientes.php" class="mb-4">
                            <input type="hidden" name="action" value="agregar_nota">
                            <input type="hidden" name="chat_id" value="<?php echo $chat_id; ?>">
                            <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 text-sm">
                                <option value="observacion">Observación</option>
                                <option value="llamada">Llamada</option>
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="seguimiento">Seguimiento</option>
                            </select>
                            <textarea name="nota" rows="3" placeholder="Escribe una nota..." required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 text-sm"></textarea>
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Agregar Nota
                            </button>
                        </form>

                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($notas as $nota): ?>
                            <div class="border-l-4 border-blue-500 pl-3 py-2 bg-gray-50 rounded">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-600 uppercase"><?php echo $nota['tipo']; ?></span>
                                    <span class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($nota['fecha'])); ?></span>
                                </div>
                                <p class="text-sm text-gray-800"><?php echo nl2br(htmlspecialchars($nota['nota'])); ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Por: <?php echo htmlspecialchars($nota['usuario_nombre'] . ' ' . ($nota['usuario_apellido'] ?? '')); ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Card: Timeline de Eventos -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Timeline de Eventos</h2>
                        <div class="space-y-4">
                            <?php foreach ($eventos as $evento): ?>
                            <div class="relative pl-10 timeline-item">
                                <div class="absolute left-0 top-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="material-icons-outlined text-blue-600 text-sm">
                                        <?php
                                        $iconos = [
                                            'chatbot' => 'smart_toy',
                                            'asignacion' => 'person_add',
                                            'mensaje' => 'chat',
                                            'nota' => 'note',
                                            'estado' => 'update',
                                            'documento' => 'description'
                                        ];
                                        echo $iconos[$evento['tipo']] ?? 'circle';
                                        ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-800"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('d/m/Y H:i', strtotime($evento['fecha'])); ?>
                                        <?php if ($evento['usuario_nombre']): ?>
                                        - <?php echo htmlspecialchars($evento['usuario_nombre']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
    // Inicializar mapa si hay coordenadas
    <?php if (!empty($cliente['latitud']) && !empty($cliente['longitud'])): ?>
    var map = L.map('map').setView([<?php echo $cliente['latitud']; ?>, <?php echo $cliente['longitud']; ?>], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);
    L.marker([<?php echo $cliente['latitud']; ?>, <?php echo $cliente['longitud']; ?>])
        .addTo(map)
        .bindPopup('<b><?php echo htmlspecialchars($cliente['cliente_nombre']); ?></b><br><?php echo htmlspecialchars($cliente['ciudad']); ?>')
        .openPopup();
    <?php endif; ?>

    function imprimirReporte() {
        window.print();
    }
    </script>

</body>
</html>
