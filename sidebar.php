<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);

function activo($archivo) {
    global $pagina_actual;
    return $pagina_actual === $archivo
        ? 'bg-blue-50 text-blue-600 font-semibold'
        : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600';
}
?>

<aside class="w-64 h-screen bg-white shadow-xl fixed left-0 top-0 flex flex-col border-r border-gray-200 z-50">

    <!-- Logo -->
    <div class="p-6 border-b border-gray-100">
        <h1 class="text-2xl font-bold text-blue-600">Panel Admin</h1>
    </div>

    <!-- Menú -->
    <nav class="flex-1 overflow-y-auto p-4">
        <ul class="space-y-2">

            <li>
                <a href="dashboard.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('dashboard.php'); ?>">
                    <span class="material-icons-outlined mr-3">dashboard</span>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="clientes.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('clientes.php'); ?>">
                    <span class="material-icons-outlined mr-3">groups</span>
                    Clientes
                </a>
            </li>

            <li>
                <a href="asesores.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('asesores.php'); ?>">
                    <span class="material-icons-outlined mr-3">support_agent</span>
                    Asesores
                </a>
            </li>

            <!-- SOLICITUDES -->
            <li>
                <a href="solicitudes.php"
                   class="flex items-center justify-between p-3 rounded-lg transition <?php echo activo('solicitudes.php'); ?>">
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-3">assignment</span>
                        Solicitudes
                    </div>
                    <span id="badge-solicitudes"
                          class="hidden min-w-[22px] h-[22px] px-2 text-xs font-bold rounded-full bg-red-600 text-white flex items-center justify-center">
                        0
                    </span>
                </a>
            </li>

            <li>
                <a href="chatbot.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('chatbot.php'); ?>">
                    <span class="material-icons-outlined mr-3">smart_toy</span>
                    ChatBot
                </a>
            </li>

            <li>
                <a href="plantillas.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('plantillas.php'); ?>">
                    <span class="material-icons-outlined mr-3">email</span>
                    Plantillas Email
                </a>
            </li>

            <li>
                <a href="perfil.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('perfil.php'); ?>">
                    <span class="material-icons-outlined mr-3">person</span>
                    Perfil
                </a>
            </li>

            <li>
                <a href="reportes.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('reportes.php'); ?>">
                    <span class="material-icons-outlined mr-3">bar_chart</span>
                    Reportes
                </a>
            </li>

            <li>
                <a href="configuracion.php" class="flex items-center p-3 rounded-lg transition <?php echo activo('configuracion.php'); ?>">
                    <span class="material-icons-outlined mr-3">settings</span>
                    Configuración
                </a>
            </li>

        </ul>
    </nav>

    <div class="p-4 border-t border-gray-200">
        <a href="logout.php" class="flex items-center p-3 rounded-lg text-red-600 hover:bg-red-50 transition">
            <span class="material-icons-outlined mr-3">logout</span>
            Cerrar Sesión
        </a>
    </div>

</aside>

<script>
(function () {
    const badge = document.getElementById('badge-solicitudes');
    if (!badge) return;

    async function actualizarSolicitudes() {
        try {
            const res = await fetch('api/contador_solicitudes.php', { cache: 'no-store' });
            const data = await res.json();

            if (data.success && data.total > 0) {
                badge.textContent = data.total;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        } catch (e) {}
    }

    actualizarSolicitudes();
    setInterval(actualizarSolicitudes, 1000);
})();
</script>
