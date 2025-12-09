<?php
$metricsFile = 'metrics.json';

// Cargar métricas
$metrics = [];
if (file_exists($metricsFile)) {
    $metrics = json_decode(file_get_contents($metricsFile), true) ?: [];
}

// Función para calcular estadísticas por período
function getStatsByPeriod($metrics, $period) {
    $now = time();
    $stats = ['visits' => 0, 'total_time' => 0, 'avg_time' => 0];
    
    foreach ($metrics as $metric) {
        $timestamp = $metric['timestamp'];
        $duration = isset($metric['duration']) ? $metric['duration'] : 0;
        
        switch ($period) {
            case 'hour':
                if ($timestamp >= $now - 3600) {
                    $stats['visits']++;
                    $stats['total_time'] += $duration;
                }
                break;
            case 'day':
                if ($timestamp >= $now - 86400) {
                    $stats['visits']++;
                    $stats['total_time'] += $duration;
                }
                break;
            case 'week':
                if ($timestamp >= $now - 604800) {
                    $stats['visits']++;
                    $stats['total_time'] += $duration;
                }
                break;
            case 'month':
                if ($timestamp >= $now - 2592000) {
                    $stats['visits']++;
                    $stats['total_time'] += $duration;
                }
                break;
        }
    }
    
    if ($stats['visits'] > 0) {
        $stats['avg_time'] = round($stats['total_time'] / $stats['visits']);
    }
    
    return $stats;
}

// Función para obtener datos horarios (últimas 24 horas)
function getHourlyData($metrics) {
    $now = time();
    $hourlyData = array_fill(0, 24, 0);
    
    foreach ($metrics as $metric) {
        $timestamp = $metric['timestamp'];
        if ($timestamp >= $now - 86400) {
            $hour = 23 - floor(($now - $timestamp) / 3600);
            if ($hour >= 0 && $hour < 24) {
                $hourlyData[$hour]++;
            }
        }
    }
    
    return $hourlyData;
}

// Función para obtener datos diarios (última semana)
function getDailyData($metrics) {
    $now = time();
    $dailyData = array_fill(0, 7, 0);
    
    foreach ($metrics as $metric) {
        $timestamp = $metric['timestamp'];
        if ($timestamp >= $now - 604800) {
            $day = 6 - floor(($now - $timestamp) / 86400);
            if ($day >= 0 && $day < 7) {
                $dailyData[$day]++;
            }
        }
    }
    
    return $dailyData;
}

// Calcular estadísticas
$hourStats = getStatsByPeriod($metrics, 'hour');
$dayStats = getStatsByPeriod($metrics, 'day');
$weekStats = getStatsByPeriod($metrics, 'week');
$monthStats = getStatsByPeriod($metrics, 'month');

$hourlyData = getHourlyData($metrics);
$dailyData = getDailyData($metrics);

// Formatear tiempo
function formatDuration($seconds) {
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
}
?>

<!-- ================== ESTILOS ================== -->
<style>
    /* Ajusta la altura de los gráficos del dashboard */
    .chart-container {
        height: 300px; /* Puedes subir a 350 o 400 si querés */
        position: relative;
    }
</style>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Préstamo Líder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

</head>
<body 

<?php include 'sidebar.php'; ?>

class="bg-gray-50">
    <!-- Navegación -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-xl font-bold text-gray-800">Préstamo Líder</span>
            </div>
            <div class="flex gap-2">
                <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Landing</a>
                <a href="dashboard.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg transition">Dashboard</a>
                <a href="configuracion.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Configuración</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard de Analíticas</h1>
            <p class="text-gray-600">Métricas de visitas y permanencia en el sitio</p>
        </div>

       <!-- Tarjetas de estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Última hora -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-blue-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase">Última Hora</h3>
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold text-gray-800 mb-2"><?php echo $hourStats['visits']; ?></div>
        <div class="text-sm text-gray-600">Visitas</div>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500">Permanencia promedio: </span>
            <span class="text-sm font-semibold text-blue-600"><?php echo formatDuration($hourStats['avg_time']); ?></span>
        </div>
    </div>

    <!-- Último día -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-green-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase">Último Día</h3>
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold text-gray-800 mb-2"><?php echo $dayStats['visits']; ?></div>
        <div class="text-sm text-gray-600">Visitas</div>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500">Permanencia promedio: </span>
            <span class="text-sm font-semibold text-green-600"><?php echo formatDuration($dayStats['avg_time']); ?></span>
        </div>
    </div>

    <!-- Última semana -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase">Última Semana</h3>
            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold text-gray-800 mb-2"><?php echo $weekStats['visits']; ?></div>
        <div class="text-sm text-gray-600">Visitas</div>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500">Permanencia promedio: </span>
            <span class="text-sm font-semibold text-purple-600"><?php echo formatDuration($weekStats['avg_time']); ?></span>
        </div>
    </div>

    <!-- Último mes -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-orange-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase">Último Mes</h3>
            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        <div class="text-3xl font-bold text-gray-800 mb-2"><?php echo $monthStats['visits']; ?></div>
        <div class="text-sm text-gray-600">Visitas</div>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500">Permanencia promedio: </span>
            <span class="text-sm font-semibold text-orange-600"><?php echo formatDuration($monthStats['avg_time']); ?></span>
        </div>
    </div>

</div>

<!-- Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Gráfico por hora -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Visitas por Hora (Últimas 24h)</h3>
        <div class="chart-container">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    <!-- Gráfico por día -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Visitas por Día (Última Semana)</h3>
        <div class="chart-container">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

</div>

<!-- Tabla de visitas recientes -->
<div class="mt-8 bg-white rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Últimas 10 Visitas</h3>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Sesión</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Fecha y Hora</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Duración</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recentMetrics = array_slice(array_reverse($metrics), 0, 10);
                foreach ($recentMetrics as $metric):
                ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4 text-sm text-gray-600 font-mono">
                        <?php echo substr($metric['session_id'], 0, 8); ?>...
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-800">
                        <?php echo date('d/m/Y H:i:s', $metric['timestamp']); ?>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-800 font-semibold">
                        <?php echo isset($metric['duration']) ? formatDuration($metric['duration']) : 'En curso'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


    <script>
        // Datos para gráfico por hora
        const hourlyData = <?php echo json_encode($hourlyData); ?>;
        const hourLabels = [];
        for (let i = 23; i >= 0; i--) {
            const hour = new Date();
            hour.setHours(hour.getHours() - i);
            hourLabels.push(hour.getHours() + ':00');
        }

        const hourlyChart = new Chart(document.getElementById('hourlyChart'), {
            type: 'line',
            data: {
                labels: hourLabels,
                datasets: [{
                    label: 'Visitas',
                    data: hourlyData,
                    borderColor: 'rgb(37, 99, 235)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Datos para gráfico por día
        const dailyData = <?php echo json_encode($dailyData); ?>;
        const dayLabels = [];
        const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        for (let i = 6; i >= 0; i--) {
            const day = new Date();
            day.setDate(day.getDate() - i);
            dayLabels.push(dayNames[day.getDay()]);
        }

        const dailyChart = new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: dayLabels,
                datasets: [{
                    label: 'Visitas',
                    data: dailyData,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Auto-refresh cada 30 segundos
        setTimeout(() => location.reload(), 30000);
    </script>
</body>
</html>