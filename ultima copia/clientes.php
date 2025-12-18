<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol'])) {
    header("Location: login.php");
    exit;
}

// Solo admin y asesores pueden ver clientes
if (!in_array($_SESSION['usuario_rol'], ['admin', 'asesor'])) {
    header("Location: login.php");
    exit;
}

require_once 'backend/connection.php';

$usuario_id = $_SESSION['usuario_id'];
$usuario_rol = $_SESSION['usuario_rol'];

// =====================================================
// PROCESAR ACCIONES
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // REENVIAR A CRM EXTERNO
    if ($_POST['action'] === 'reenviar_api') {
        $chat_id = intval($_POST['chat_id']);

        try {
            require_once __DIR__ . "/api/send_riesgo_api.php";

            // Forzamos a "pendiente" para permitir reenviar (si estaba en error)
            $pdo->prepare("UPDATE chats SET api_enviado = 0 WHERE id = ?")->execute([$chat_id]);

            $ok = enviarDatosRiesgo($chat_id);

            $_SESSION['message'] = $ok ? 'Reenvío realizado (revisar estado CRM).' : 'No se pudo reenviar (posible duplicado/en proceso).';
        } catch (Throwable $e) {
            error_log("REENVIO CRM ERROR chat={$chat_id}: " . $e->getMessage());
            $_SESSION['message'] = 'Error al reenviar a CRM: ' . $e->getMessage();
        }

        header("Location: clientes.php");
        exit;
    }

    // CAMBIAR ESTADO DE SOLICITUD
    if ($_POST['action'] === 'cambiar_estado') {
        $chat_id = intval($_POST['chat_id']);
        $nuevo_estado = $_POST['estado_solicitud'];

        $stmt = $pdo->prepare("UPDATE chats SET estado_solicitud = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $chat_id]);

        // Registrar evento
        $stmt = $pdo->prepare("
            INSERT INTO cliente_eventos (chat_id, usuario_id, tipo, descripcion)
            VALUES (?, ?, 'estado', ?)
        ");
        $stmt->execute([$chat_id, $usuario_id, "Estado cambiado a: $nuevo_estado"]);

        $_SESSION['message'] = 'Estado actualizado correctamente';
        header("Location: clientes.php");
        exit;
    }

    // AGREGAR NOTA
    if ($_POST['action'] === 'agregar_nota') {
        $chat_id = intval($_POST['chat_id']);
        $nota = trim($_POST['nota']);
        $tipo = $_POST['tipo'];

        if (!empty($nota)) {
            $stmt = $pdo->prepare("
                INSERT INTO cliente_notas (chat_id, usuario_id, nota, tipo)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$chat_id, $usuario_id, $nota, $tipo]);

            // Registrar evento
            $stmt = $pdo->prepare("
                INSERT INTO cliente_eventos (chat_id, usuario_id, tipo, descripcion)
                VALUES (?, ?, 'nota', ?)
            ");
            $stmt->execute([$chat_id, $usuario_id, "Nota agregada: " . substr($nota, 0, 50) . "..."]);

            $_SESSION['message'] = 'Nota agregada correctamente';
        }
        header("Location: clientes.php?detalle=" . $chat_id);
        exit;
    }

    // AGREGAR TAG
    if ($_POST['action'] === 'agregar_tag') {
        $chat_id = intval($_POST['chat_id']);
        $tag = trim($_POST['tag']);
        $color = $_POST['color'] ?? '#3b82f6';

        if (!empty($tag)) {
            $stmt = $pdo->prepare("
                INSERT INTO cliente_tags (chat_id, tag, color)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE color = ?
            ");
            $stmt->execute([$chat_id, $tag, $color, $color]);

            $_SESSION['message'] = 'Etiqueta agregada correctamente';
        }
        header("Location: clientes.php?detalle=" . $chat_id);
        exit;
    }

    // CAMBIAR PRIORIDAD
    if ($_POST['action'] === 'cambiar_prioridad') {
        $chat_id = intval($_POST['chat_id']);
        $prioridad = $_POST['prioridad'];

        $stmt = $pdo->prepare("UPDATE chats SET prioridad = ? WHERE id = ?");
        $stmt->execute([$prioridad, $chat_id]);

        $_SESSION['message'] = 'Prioridad actualizada';
        header("Location: clientes.php");
        exit;
    }

    // ASIGNAR ASESOR
    if ($_POST['action'] === 'asignar_asesor') {
        $chat_id = intval($_POST['chat_id']);
        $asesor_id = intval($_POST['asesor_id']);

        $stmt = $pdo->prepare("UPDATE chats SET asesor_id = ? WHERE id = ?");
        $stmt->execute([$asesor_id, $chat_id]);

        // Registrar evento
        $stmt = $pdo->prepare("
            INSERT INTO cliente_eventos (chat_id, usuario_id, tipo, descripcion)
            VALUES (?, ?, 'asignacion', ?)
        ");
        $stmt->execute([$chat_id, $usuario_id, "Asignado a asesor ID: $asesor_id"]);

        $_SESSION['message'] = 'Asesor asignado correctamente';
        header("Location: clientes.php");
        exit;
    }
}

// =====================================================
// OBTENER MÉTRICAS DEL DASHBOARD
// =====================================================
$metricas = [];

// Total de clientes
$stmt = $pdo->query("SELECT COUNT(*) FROM chats WHERE origen = 'chatbot'");
$metricas['total'] = $stmt->fetchColumn();

// Por estado de solicitud
$stmt = $pdo->query("
    SELECT estado_solicitud, COUNT(*) as total
    FROM chats
    WHERE origen = 'chatbot'
    GROUP BY estado_solicitud
");
$metricas['por_estado'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Nuevos hoy
$stmt = $pdo->query("
    SELECT COUNT(*) FROM chats
    WHERE origen = 'chatbot' AND DATE(fecha_inicio) = CURDATE()
");
$metricas['hoy'] = $stmt->fetchColumn();

// Pendientes de contacto
$stmt = $pdo->query("
    SELECT COUNT(*) FROM chats
    WHERE origen = 'chatbot' AND estado_solicitud = 'nuevo'
");
$metricas['pendientes'] = $stmt->fetchColumn();

// Tasa de conversión (aprobados / total)
$aprobados = $metricas['por_estado']['aprobado'] ?? 0;
$metricas['conversion'] = $metricas['total'] > 0 ? round(($aprobados / $metricas['total']) * 100, 2) : 0;

// =====================================================
// FILTROS Y BÚSQUEDA
// =====================================================
$where = ["c.origen = 'chatbot'"];
$params = [];

// Filtro de búsqueda general
if (!empty($_GET['buscar'])) {
    $buscar = '%' . $_GET['buscar'] . '%';
    $where[] = "(u.nombre LIKE ? OR cd.dni LIKE ? OR u.telefono LIKE ? OR u.email LIKE ?)";
    $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
}

// Filtro por estado de solicitud
if (!empty($_GET['estado_solicitud'])) {
    $where[] = "c.estado_solicitud = ?";
    $params[] = $_GET['estado_solicitud'];
}

// Filtro por departamento
if (!empty($_GET['departamento_id'])) {
    $where[] = "c.departamento_id = ?";
    $params[] = intval($_GET['departamento_id']);
}

// Filtro por asesor
if (!empty($_GET['asesor_id'])) {
    $where[] = "c.asesor_id = ?";
    $params[] = intval($_GET['asesor_id']);
}

// Filtro por prioridad
if (!empty($_GET['prioridad'])) {
    $where[] = "c.prioridad = ?";
    $params[] = $_GET['prioridad'];
}

// Filtro por fecha
if (!empty($_GET['fecha_desde'])) {
    $where[] = "DATE(c.fecha_inicio) >= ?";
    $params[] = $_GET['fecha_desde'];
}
if (!empty($_GET['fecha_hasta'])) {
    $where[] = "DATE(c.fecha_inicio) <= ?";
    $params[] = $_GET['fecha_hasta'];
}

// Si es asesor, solo ver sus propios clientes
if ($usuario_rol === 'asesor') {
    $where[] = "c.asesor_id = ?";
    $params[] = $usuario_id;
}

$where_sql = implode(' AND ', $where);

// =====================================================
// PAGINACIÓN
// =====================================================
$por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Contar total de registros
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT c.id)
    FROM chats c
    INNER JOIN usuarios u ON c.cliente_id = u.id
    LEFT JOIN clientes_detalles cd ON cd.usuario_id = u.id
    WHERE $where_sql
");
$stmt->execute($params);
$total_registros = $stmt->fetchColumn();
$total_paginas = ceil($total_registros / $por_pagina);

// =====================================================
// OBTENER CLIENTES CON TODA LA INFO
// =====================================================
$stmt = $pdo->prepare("
    SELECT
        c.id as chat_id,
        c.fecha_inicio,
        c.estado_solicitud,
        c.prioridad,
        c.score,
        c.cuil_validado,
        c.nombre_validado,
        c.situacion_laboral,
        c.banco,
        c.ciudad,
        c.pais,
        c.api_enviado,
        c.api_respuesta,
        u.id as cliente_id,
        u.nombre as cliente_nombre,
        u.telefono,
        u.email,
        cd.dni,
        d.nombre as departamento_nombre,
        asesor.nombre as asesor_nombre,
        (SELECT COUNT(*) FROM mensajes WHERE chat_id = c.id) as total_mensajes,
        (SELECT COUNT(*) FROM cliente_notas WHERE chat_id = c.id) as total_notas,
        (SELECT GROUP_CONCAT(tag SEPARATOR ',') FROM cliente_tags WHERE chat_id = c.id) as tags
    FROM chats c
    INNER JOIN usuarios u ON c.cliente_id = u.id
    LEFT JOIN clientes_detalles cd ON cd.usuario_id = u.id
    LEFT JOIN departamentos d ON c.departamento_id = d.id
    LEFT JOIN usuarios asesor ON c.asesor_id = asesor.id
    WHERE $where_sql
    ORDER BY c.fecha_inicio DESC
    LIMIT $por_pagina OFFSET $offset
");
$stmt->execute($params);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// OBTENER LISTA DE DEPARTAMENTOS PARA FILTROS
// =====================================================
$stmt = $pdo->query("SELECT id, nombre FROM departamentos ORDER BY nombre");
$departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// OBTENER LISTA DE ASESORES PARA FILTROS
// =====================================================
$stmt = $pdo->query("
    SELECT id, nombre, apellido
    FROM usuarios
    WHERE rol = 'asesor' AND estado = 'activo'
    ORDER BY nombre
");
$asesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

function crmBadge($api_enviado) {
    // 0 pendiente, 1 ok, 2 enviando, -1 error
    if ((int)$api_enviado === 1) {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Enviado</span>';
    }
    if ((int)$api_enviado === 2) {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Enviando</span>';
    }
    if ((int)$api_enviado === -1) {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Error</span>';
    }
    return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Pendiente</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Clientes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        .prioridad-baja { background: #e5e7eb; color: #6b7280; }
        .prioridad-media { background: #dbeafe; color: #1e40af; }
        .prioridad-alta { background: #fef3c7; color: #92400e; }
        .prioridad-urgente { background: #fee2e2; color: #991b1b; }

        .estado-nuevo { background: #dbeafe; color: #1e40af; }
        .estado-contactado { background: #e0e7ff; color: #4338ca; }
        .estado-documentacion { background: #fef3c7; color: #92400e; }
        .estado-analisis { background: #e0f2fe; color: #0369a1; }
        .estado-aprobado { background: #d1fae5; color: #065f46; }
        .estado-rechazado { background: #fee2e2; color: #991b1b; }
        .estado-desistio { background: #f3f4f6; color: #374151; }
    </style>
</head>
<body class="bg-gray-50">

<?php include 'sidebar.php'; ?>

<div class="ml-64">
    <nav class="bg-white shadow-md sticky top-0 z-40">
        <div class="max-w-full mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="material-icons-outlined text-blue-600 text-3xl">groups</span>
                <h1 class="text-xl font-bold text-gray-800">CRM - Gestión de Clientes</h1>
            </div>
            <div class="flex gap-2">
                <button onclick="exportarCSV()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <span class="material-icons-outlined">download</span>
                    Exportar CSV
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
            <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div class="max-w-full mx-auto px-6 py-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold uppercase">Total Clientes</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($metricas['total']); ?></p>
                    </div>
                    <span class="material-icons-outlined text-blue-500 text-5xl">people</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold uppercase">Nuevos Hoy</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($metricas['hoy']); ?></p>
                    </div>
                    <span class="material-icons-outlined text-green-500 text-5xl">today</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold uppercase">Pendientes</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($metricas['pendientes']); ?></p>
                    </div>
                    <span class="material-icons-outlined text-yellow-500 text-5xl">pending</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold uppercase">Aprobados</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($metricas['por_estado']['aprobado'] ?? 0); ?></p>
                    </div>
                    <span class="material-icons-outlined text-purple-500 text-5xl">check_circle</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold uppercase">Conversión</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $metricas['conversion']; ?>%</p>
                    </div>
                    <span class="material-icons-outlined text-indigo-500 text-5xl">trending_up</span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar</label>
                    <input type="text" name="buscar" value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>"
                           placeholder="Nombre, DNI, teléfono, email..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <select name="estado_solicitud" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="nuevo" <?php echo ($_GET['estado_solicitud'] ?? '') === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="contactado" <?php echo ($_GET['estado_solicitud'] ?? '') === 'contactado' ? 'selected' : ''; ?>>Contactado</option>
                        <option value="documentacion" <?php echo ($_GET['estado_solicitud'] ?? '') === 'documentacion' ? 'selected' : ''; ?>>Documentación</option>
                        <option value="analisis" <?php echo ($_GET['estado_solicitud'] ?? '') === 'analisis' ? 'selected' : ''; ?>>Análisis</option>
                        <option value="aprobado" <?php echo ($_GET['estado_solicitud'] ?? '') === 'aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="rechazado" <?php echo ($_GET['estado_solicitud'] ?? '') === 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                        <option value="desistio" <?php echo ($_GET['estado_solicitud'] ?? '') === 'desistio' ? 'selected' : ''; ?>>Desistió</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Departamento</label>
                    <select name="departamento_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <?php foreach ($departamentos as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo ($_GET['departamento_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Prioridad</label>
                    <select name="prioridad" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas</option>
                        <option value="baja" <?php echo ($_GET['prioridad'] ?? '') === 'baja' ? 'selected' : ''; ?>>Baja</option>
                        <option value="media" <?php echo ($_GET['prioridad'] ?? '') === 'media' ? 'selected' : ''; ?>>Media</option>
                        <option value="alta" <?php echo ($_GET['prioridad'] ?? '') === 'alta' ? 'selected' : ''; ?>>Alta</option>
                        <option value="urgente" <?php echo ($_GET['prioridad'] ?? '') === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Desde</label>
                    <input type="date" name="fecha_desde" value="<?php echo $_GET['fecha_desde'] ?? ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?php echo $_GET['fecha_hasta'] ?? ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <?php if ($usuario_rol === 'admin'): ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Asesor</label>
                    <select name="asesor_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <?php foreach ($asesores as $asesor): ?>
                        <option value="<?php echo $asesor['id']; ?>" <?php echo ($_GET['asesor_id'] ?? '') == $asesor['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($asesor['nombre'] . ' ' . ($asesor['apellido'] ?? '')); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="flex items-end gap-2">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                        <span class="material-icons-outlined">search</span>
                        Buscar
                    </button>
                    <a href="clientes.php" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition flex items-center gap-2">
                        <span class="material-icons-outlined">clear</span>
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">CRM</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Asesor</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <span class="material-icons-outlined text-6xl text-gray-300 mb-4">search_off</span>
                                <p class="text-lg">No se encontraron clientes</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $cliente): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <?php echo strtoupper(substr($cliente['cliente_nombre'] ?: 'NA', 0, 2)); ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($cliente['cliente_nombre'] ?? ''); ?></p>
                                            <p class="text-xs text-gray-500">DNI: <?php echo htmlspecialchars($cliente['dni'] ?? 'N/A'); ?></p>
                                            <?php if (!empty($cliente['tags'])): ?>
                                            <div class="flex gap-1 mt-1">
                                                <?php foreach (explode(',', $cliente['tags']) as $tag): ?>
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                    <?php echo htmlspecialchars($tag); ?>
                                                </span>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-800"><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($cliente['email'] ?? 'N/A'); ?></p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold estado-<?php echo $cliente['estado_solicitud']; ?>">
                                        <?php echo ucfirst($cliente['estado_solicitud']); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold prioridad-<?php echo $cliente['prioridad']; ?>">
                                        <?php echo ucfirst($cliente['prioridad']); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <?php echo crmBadge($cliente['api_enviado']); ?>
                                        <?php if ((int)$cliente['api_enviado'] !== 1): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="reenviar_api">
                                                <input type="hidden" name="chat_id" value="<?php echo (int)$cliente['chat_id']; ?>">
                                                <button type="submit"
                                                        class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs flex items-center gap-1"
                                                        title="Reenviar a CRM externo">
                                                    <span class="material-icons-outlined" style="font-size:16px;">refresh</span>
                                                    Reenviar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-800"><?php echo htmlspecialchars($cliente['asesor_nombre'] ?? 'Sin asignar'); ?></p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-800"><?php echo date('d/m/Y', strtotime($cliente['fecha_inicio'])); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('H:i', strtotime($cliente['fecha_inicio'])); ?></p>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-12 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-yellow-400 to-green-500"
                                                 style="width: <?php echo min(100, (int)$cliente['score']); ?>%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-600"><?php echo htmlspecialchars($cliente['score']); ?></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="cliente_detalle.php?id=<?php echo (int)$cliente['chat_id']; ?>"
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                        <span class="material-icons-outlined text-sm mr-1">visibility</span>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_paginas > 1): ?>
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t">
                <div class="text-sm text-gray-600">
                    Mostrando <?php echo (($pagina_actual - 1) * $por_pagina) + 1; ?>
                    a <?php echo min($pagina_actual * $por_pagina, $total_registros); ?>
                    de <?php echo $total_registros; ?> registros
                </div>
                <div class="flex gap-2">
                    <?php if ($pagina_actual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_actual - 1; ?>&<?php echo http_build_query(array_filter($_GET, fn($k) => $k !== 'pagina', ARRAY_FILTER_USE_KEY)); ?>"
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Anterior
                    </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $pagina_actual - 2); $i <= min($total_paginas, $pagina_actual + 2); $i++): ?>
                    <a href="?pagina=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, fn($k) => $k !== 'pagina', ARRAY_FILTER_USE_KEY)); ?>"
                       class="px-4 py-2 <?php echo $i === $pagina_actual ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_actual + 1; ?>&<?php echo http_build_query(array_filter($_GET, fn($k) => $k !== 'pagina', ARRAY_FILTER_USE_KEY)); ?>"
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Siguiente
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportarCSV() {
    const params = new URLSearchParams(window.location.search);
    params.set('exportar', 'csv');
    window.location.href = 'api/clientes/exportar_csv.php?' + params.toString();
}
</script>

</body>
</html>
