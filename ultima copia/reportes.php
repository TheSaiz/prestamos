<?php
session_start();
require_once 'backend/connection.php';

// ===========================
// PROTEGER SOLO PARA ADMIN
// ===========================
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ===========================
// CARGAR MÉTRICAS (metrics.json)
// ===========================
$metricsFile = 'metrics.json';
$metrics = [];

if (file_exists($metricsFile)) {
    $metrics = json_decode(file_get_contents($metricsFile), true) ?: [];
}

// ===========================
// FUNCIÓN PARA DESCARGAR CSV
// ===========================
function descargarCSV($filename, $header, $data) {
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=$filename");

    $output = fopen("php://output", "w");
    fputcsv($output, $header);

    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// ===========================
// DESCARGA DE REPORTES
// ===========================
if (isset($_GET['descargar'])) {

    // REPORTE DE MÉTRICAS
    if ($_GET['descargar'] === "metrics") {
        $header = ["Session ID", "Timestamp", "Duración (segundos)"];
        $rows = [];

        foreach ($metrics as $m) {
            $rows[] = [
                $m['session_id'] ?? '',
                date("Y-m-d H:i:s", $m['timestamp']),
                $m['duration'] ?? ''
            ];
        }

        descargarCSV("reporte_metricas.csv", $header, $rows);
    }

    // REPORTE DE CHATS DESDE BD
    if ($_GET['descargar'] === "chats") {

        $stmt = $pdo->query("
            SELECT c.id, u.nombre as cliente, ases.nombre as asesor,
                   c.departamento_id, c.estado, c.fecha_inicio, c.fecha_cierre
            FROM chats c
            LEFT JOIN usuarios u ON c.cliente_id = u.id
            LEFT JOIN usuarios ases ON c.asesor_id = ases.id
            ORDER BY c.fecha_inicio DESC
        ");

        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $header = ["ID", "Cliente", "Asesor", "Departamento", "Estado", "Fecha Inicio", "Fecha Cierre"];
        $rows = [];

        foreach ($chats as $c) {
            $rows[] = [
                $c['id'],
                $c['cliente'],
                $c['asesor'],
                $c['departamento_id'],
                $c['estado'],
                $c['fecha_inicio'],
                $c['fecha_cierre']
            ];
        }

        descargarCSV("reporte_chats.csv", $header, $rows);
    }
}

// ===========================
// GRÁFICOS DE CHATS POR TIEMPO
// ===========================
function contarPorPeriodo($pdo, $interval) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM chats 
        WHERE fecha_inicio >= DATE_SUB(NOW(), INTERVAL $interval)
    ");
    $stmt->execute();
    return $stmt->fetchColumn();
}

$chats_hora = contarPorPeriodo($pdo, "1 HOUR");
$chats_dia  = contarPorPeriodo($pdo, "1 DAY");
$chats_mes  = contarPorPeriodo($pdo, "1 MONTH");
$chats_anio = contarPorPeriodo($pdo, "1 YEAR");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - Préstamo Líder</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
</head>

<body 
<?php include 'sidebar.php'; ?>
class="bg-gray-50">

<!-- NAV -->
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
            <span class="text-xl font-bold text-gray-800">Préstamo Líder</span>
        </div>
    </div>
</nav>

<!-- CONTENIDO -->
<div class="max-w-7xl mx-auto px-6 py-8">

    <h1 class="text-3xl font-bold mb-6 text-gray-800">Reportes del Sistema</h1>

    <!-- BOTONES DE DESCARGA -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

        <a href="reportes.php?descargar=metrics"
           class="bg-blue-600 text-white p-5 rounded-xl shadow hover:bg-blue-700 font-semibold text-center">
            <span class="material-icons-outlined text-4xl">download</span>
            <div>Descargar Reporte de Métricas</div>
        </a>

        <a href="reportes.php?descargar=chats"
           class="bg-green-600 text-white p-5 rounded-xl shadow hover:bg-green-700 font-semibold text-center">
            <span class="material-icons-outlined text-4xl">download</span>
            <div>Descargar Reporte de Chats</div>
        </a>
    </div>

    <!-- GRÁFICOS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="font-bold text-gray-700 mb-3">Chats Iniciados (Hora/Día/Mes/Año)</h3>
            <canvas id="chatsChart" height="120"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="font-bold text-gray-700 mb-3">Métricas registradas</h3>
            <canvas id="metricsChart" height="120"></canvas>
        </div>

    </div>

</div>

<script>
// =============================
// GRÁFICO CHATS POR PERÍODO
// =============================
const chatsCtx = document.getElementById('chatsChart');

new Chart(chatsCtx, {
    type: 'bar',
    data: {
        labels: ['Última Hora', 'Último Día', 'Último Mes', 'Último Año'],
        datasets: [{
            label: 'Cantidad',
            data: [<?= $chats_hora ?>, <?= $chats_dia ?>, <?= $chats_mes ?>, <?= $chats_anio ?>],
            backgroundColor: [
                'rgba(37, 99, 235, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(168, 85, 247, 0.8)',
                'rgba(249, 115, 22, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// =============================
// GRÁFICO MÉTRICAS
// =============================
const metricas = <?= json_encode(array_column($metrics, 'timestamp')) ?>;
const metricsCtx = document.getElementById('metricsChart');

new Chart(metricsCtx, {
    type: 'line',
    data: {
        labels: metricas.map(t => new Date(t * 1000).toLocaleTimeString()),
        datasets: [{
            label: 'Visitas',
            data: metricas.map(() => 1),
            borderColor: 'rgb(37, 99, 235)',
            backgroundColor: 'rgba(37, 99, 235, 0.3)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>
