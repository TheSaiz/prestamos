<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol'])) {
    header("Location: login.php");
    exit;
}

// Verificar que sea admin
if ($_SESSION['usuario_rol'] !== 'admin') {
    // Si no es admin, lo sacamos del dashboard
    header("Location: panel_asesor.php");
    exit;
}

$configFile = 'config.json';

// Procesar actualización de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_config') {
    $newConfig = json_decode($_POST['config'], true);
    if ($newConfig) {
        file_put_contents($configFile, json_encode($newConfig, JSON_PRETTY_PRINT));
        $_SESSION['message'] = 'Configuración guardada exitosamente';
        header('Location: configuracion.php');
        exit;
    }
}

// Cargar configuración actual
$defaultConfig = [
    'hero' => [
        'title' => 'Tu préstamo en 24 horas',
        'subtitle' => 'Hasta $4.000.000 con la mejor tasa del mercado',
        'cta' => 'Solicitar ahora'
    ],
    'benefits' => [
        ['icon' => 'shield', 'title' => '100% Seguro', 'description' => 'Oficinas propias y transparencia total'],
        ['icon' => 'headphones', 'title' => 'Atención Personalizada', 'description' => 'Personas reales que te ayudan'],
        ['icon' => 'trending', 'title' => 'Sin Anticipos', 'description' => 'No pedimos pagos adelantados'],
        ['icon' => 'award', 'title' => 'Mejor Tasa', 'description' => 'Desde 100% TNA según tu perfil']
    ],
    'products' => [
        ['title' => 'Empleados Nacionales', 'maxAmount' => '4.000.000', 'minAge' => '1 mes', 'tna' => '100%', 'features' => ['Hasta $4M', '24-36 cuotas', 'Por recibo']],
        ['title' => 'Empleados Provinciales', 'maxAmount' => '1.000.000', 'minAge' => '1 mes', 'tna' => '165%', 'features' => ['Hasta $1M', '12-36 cuotas', 'Rápido']],
        ['title' => 'Empleados Privados', 'maxAmount' => '120.000', 'minAge' => '12 meses', 'tna' => '180%', 'features' => ['Hasta $120K', '12 cuotas', 'Con Veraz']],
        ['title' => 'Jubilados ANSES', 'maxAmount' => '90.000', 'minAge' => '1 mes', 'tna' => '195%', 'features' => ['Hasta $90K', '6-11 cuotas', '1er cobro']]
    ],
    'contact' => [
        'address' => 'Córdoba 2454 Piso 6 Of. B, Posadas, Misiones',
        'phone' => '0376-5431525',
        'whatsapp' => '0376-4739033',
        'email' => 'info@prestamolider.com',
        'hours' => 'Lun-Vie 9-17hs | Sáb 7-15hs'
    ],
    'colors' => [
        'primary' => '#2563eb',
        'secondary' => '#16a34a',
        'accent' => '#f59e0b'
    ]
];

if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
} else {
    $config = $defaultConfig;
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Préstamo Líder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
</head>
<body class="bg-gray-50">

    <?php include 'sidebar.php'; ?>

    <!-- Contenido principal con margen para el sidebar -->
    <div class="ml-64">
        
        <!-- Navegación superior -->
        <nav class="bg-white shadow-md sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xl font-bold text-gray-800">Préstamo Líder</span>
                </div>
                <div class="flex gap-2">
                    <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Ver Sitio</a>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Mensaje de éxito -->
            <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Panel de Configuración</h1>
                        <p class="text-gray-600">Edita el contenido de tu landing page</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="cancelEdit()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                            Cancelar
                        </button>
                        <button onclick="saveConfig()" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-600 text-white rounded-lg hover:shadow-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            <span>Guardar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pestañas -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="flex border-b overflow-x-auto">
                    <button onclick="showTab('hero')" id="tab-hero" class="px-6 py-4 font-semibold transition whitespace-nowrap bg-blue-600 text-white">Hero</button>
                    <button onclick="showTab('benefits')" id="tab-benefits" class="px-6 py-4 font-semibold transition whitespace-nowrap text-gray-600 hover:bg-gray-50">Beneficios</button>
                    <button onclick="showTab('products')" id="tab-products" class="px-6 py-4 font-semibold transition whitespace-nowrap text-gray-600 hover:bg-gray-50">Productos</button>
                    <button onclick="showTab('contact')" id="tab-contact" class="px-6 py-4 font-semibold transition whitespace-nowrap text-gray-600 hover:bg-gray-50">Contacto</button>
                    <button onclick="showTab('colors')" id="tab-colors" class="px-6 py-4 font-semibold transition whitespace-nowrap text-gray-600 hover:bg-gray-50">Colores</button>
                </div>

                <div class="p-8">
                    <!-- Sección Hero -->
                    <div id="section-hero" class="tab-content">
                        <h2 class="text-2xl font-bold mb-6">Sección Hero</h2>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título Principal</label>
                                <input type="text" id="hero-title" value="<?php echo htmlspecialchars($config['hero']['title']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subtítulo</label>
                                <input type="text" id="hero-subtitle" value="<?php echo htmlspecialchars($config['hero']['subtitle']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Texto del Botón</label>
                                <input type="text" id="hero-cta" value="<?php echo htmlspecialchars($config['hero']['cta']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Sección Beneficios -->
                    <div id="section-benefits" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Beneficios</h2>
                        <div class="space-y-8">
                            <?php foreach ($config['benefits'] as $index => $benefit): ?>
                            <div class="p-6 bg-gray-50 rounded-lg space-y-4">
                                <h3 class="font-bold text-lg">Beneficio <?php echo $index + 1; ?></h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
                                    <input type="text" id="benefit-title-<?php echo $index; ?>" value="<?php echo htmlspecialchars($benefit['title']); ?>" class="w-full px-4 py-2 border rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                    <input type="text" id="benefit-desc-<?php echo $index; ?>" value="<?php echo htmlspecialchars($benefit['description']); ?>" class="w-full px-4 py-2 border rounded-lg">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sección Productos -->
                    <div id="section-products" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Productos</h2>
                        <div class="space-y-8">
                            <?php foreach ($config['products'] as $index => $product): ?>
                            <div class="p-6 bg-gray-50 rounded-lg space-y-4">
                                <h3 class="font-bold text-lg"><?php echo htmlspecialchars($product['title']); ?></h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto Máximo</label>
                                        <input type="text" id="product-amount-<?php echo $index; ?>" value="<?php echo htmlspecialchars($product['maxAmount']); ?>" class="w-full px-4 py-2 border rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">TNA</label>
                                        <input type="text" id="product-tna-<?php echo $index; ?>" value="<?php echo htmlspecialchars($product['tna']); ?>" class="w-full px-4 py-2 border rounded-lg">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sección Contacto -->
                    <div id="section-contact" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Información de Contacto</h2>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                <input type="text" id="contact-address" value="<?php echo htmlspecialchars($config['contact']['address']); ?>" class="w-full px-4 py-3 border rounded-lg">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                    <input type="text" id="contact-phone" value="<?php echo htmlspecialchars($config['contact']['phone']); ?>" class="w-full px-4 py-3 border rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
                                    <input type="text" id="contact-whatsapp" value="<?php echo htmlspecialchars($config['contact']['whatsapp']); ?>" class="w-full px-4 py-3 border rounded-lg">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="text" id="contact-email" value="<?php echo htmlspecialchars($config['contact']['email']); ?>" class="w-full px-4 py-3 border rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Sección Colores -->
                    <div id="section-colors" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Colores del Sitio</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php foreach ($config['colors'] as $key => $value): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 capitalize"><?php echo $key; ?></label>
                                <div class="flex gap-2">
                                    <input type="color" id="color-<?php echo $key; ?>" value="<?php echo $value; ?>" class="w-16 h-12 rounded cursor-pointer">
                                    <input type="text" id="color-text-<?php echo $key; ?>" value="<?php echo $value; ?>" class="flex-1 px-4 py-2 border rounded-lg" onchange="document.getElementById('color-<?php echo $key; ?>').value = this.value">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-6 p-6 bg-gray-50 rounded-lg">
                            <h3 class="font-bold mb-4">Vista Previa</h3>
                            <div class="flex gap-4">
                                <?php foreach ($config['colors'] as $key => $value): ?>
                                <div id="preview-<?php echo $key; ?>" class="flex-1 h-24 rounded-lg" style="background-color: <?php echo $value; ?>"></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        let originalConfig = <?php echo json_encode($config); ?>;

        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('bg-blue-600', 'text-white');
                el.classList.add('text-gray-600', 'hover:bg-gray-50');
            });
            
            document.getElementById('section-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).classList.add('bg-blue-600', 'text-white');
            document.getElementById('tab-' + tab).classList.remove('text-gray-600', 'hover:bg-gray-50');
        }

        function cancelEdit() {
            if (confirm('¿Descartar cambios y volver?')) {
                window.location.href = 'index.php';
            }
        }

        function saveConfig() {
            let newConfig = {
                hero: {
                    title: document.getElementById('hero-title').value,
                    subtitle: document.getElementById('hero-subtitle').value,
                    cta: document.getElementById('hero-cta').value
                },
                benefits: [],
                products: [],
                contact: {
                    address: document.getElementById('contact-address').value,
                    phone: document.getElementById('contact-phone').value,
                    whatsapp: document.getElementById('contact-whatsapp').value,
                    email: document.getElementById('contact-email').value,
                    hours: originalConfig.contact.hours
                },
                colors: {
                    primary: document.getElementById('color-primary').value,
                    secondary: document.getElementById('color-secondary').value,
                    accent: document.getElementById('color-accent').value
                }
            };

            for (let i = 0; i < originalConfig.benefits.length; i++) {
                newConfig.benefits.push({
                    icon: originalConfig.benefits[i].icon,
                    title: document.getElementById('benefit-title-' + i).value,
                    description: document.getElementById('benefit-desc-' + i).value
                });
            }

            for (let i = 0; i < originalConfig.products.length; i++) {
                newConfig.products.push({
                    title: originalConfig.products[i].title,
                    maxAmount: document.getElementById('product-amount-' + i).value,
                    minAge: originalConfig.products[i].minAge,
                    tna: document.getElementById('product-tna-' + i).value,
                    features: originalConfig.products[i].features
                });
            }

            let form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="action" value="save_config"><input type="hidden" name="config" value=\'' + JSON.stringify(newConfig) + '\'>';
            document.body.appendChild(form);
            form.submit();
        }

        document.querySelectorAll('[id^="color-"]').forEach(el => {
            if (el.type === 'color') {
                el.addEventListener('change', function() {
                    let key = this.id.replace('color-', '');
                    document.getElementById('color-text-' + key).value = this.value;
                    document.getElementById('preview-' + key).style.backgroundColor = this.value;
                });
            }
        });
    </script>
</body>
</html>