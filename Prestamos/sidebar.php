<?php
// sidebar.php
?>

<!-- SIDEBAR -->
<aside class="w-64 h-screen bg-white shadow-xl fixed left-0 top-0 flex flex-col border-r border-gray-200">

    <!-- Logo -->
    <div class="p-6 border-b border-gray-100">
        <h1 class="text-2xl font-bold text-blue-600">Panel Admin</h1>
    </div>

    <!-- Menú -->
    <nav class="flex-1 overflow-y-auto p-4">
        <ul class="space-y-2">

            <li>
                <a href="dashboard.php" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    <span class="material-icons-outlined mr-3">dashboard</span>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="asesores.php" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    <span class="material-icons-outlined mr-3">groups</span>
                    Asesores
                </a>
            </li>

            <li>
                <a href="chatbot.php" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    <span class="material-icons-outlined mr-3">smart_toy</span>
                    ChatBot
                </a>
            </li>

            <li>
                <a href="perfil.php" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    <span class="material-icons-outlined mr-3">person</span>
                    Perfil
                </a>
            </li>

            <li>
                <a href="reportes.php" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    <span class="material-icons-outlined mr-3">bar_chart</span>
                    Reportes
                </a>
            </li>

            <li>
                <a href="configuracion.php" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                    <span class="material-icons-outlined mr-3">settings</span>
                    Configuración
                </a>
            </li>

        </ul>
    </nav>

    <!-- LOGOUT ABAJO FIJO -->
    <div class="p-4 border-t border-gray-200">
        <a href="logout.php" class="flex items-center p-3 rounded-lg text-red-600 hover:bg-red-50 transition">
            <span class="material-icons-outlined mr-3">logout</span>
            Cerrar Sesión
        </a>
    </div>

</aside>
