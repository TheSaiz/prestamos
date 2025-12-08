<?php
// Configuración de almacenamiento simulado (en producción usar base de datos)
$configFile = 'config.json';
$metricsFile = 'metrics.json';

// Cargar configuración
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

// Registrar visita del usuario
$sessionId = session_id() ?: uniqid();
$visitTime = time();

$metrics = [];
if (file_exists($metricsFile)) {
    $metrics = json_decode(file_get_contents($metricsFile), true) ?: [];
}

// Agregar nueva visita
$metrics[] = [
    'session_id' => $sessionId,
    'timestamp' => $visitTime,
    'enter_time' => $visitTime
];

file_put_contents($metricsFile, json_encode($metrics));
?>

<style>
    .bg-hero-gradient {
        background: linear-gradient(120deg, #1E3C95 0%, #126EA2 50%, #0E9F74 100%);
    }
</style>

<style>

/* -------------------------- */
/* BOTÓN FLOTANTE DEL CHAT    */
/* -------------------------- */
#chat-widget-button {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 70px;
    height: 70px;
    background: #2563eb; /* Azul premium */
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    z-index: 999999;
    transition: transform .25s ease;
}

#chat-widget-button:hover {
    transform: scale(1.08);
}

.chat-icon {
    font-size: 32px;
}


/* -------------------------- */
/* VENTANA DEL CHAT           */
/* -------------------------- */
#chat-widget-window {
    position: fixed;
    bottom: 110px;
    right: 25px;
    width: 380px;
    height: 520px;
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 999999;
    animation: fadeInUp .35s ease;
}

/* animación */
@keyframes fadeInUp {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
}


/* -------------------------- */
/* HEADER                     */
/* -------------------------- */
.chat-header {
    background: #2563eb;
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-info {
    display: flex;
    flex-direction: column;
}

.chat-status {
    font-size: 13px;
    opacity: 0.85;
}

.chat-close-btn {
    background: transparent;
    color: white;
    border: none;
    font-size: 26px;
    cursor: pointer;
}


/* -------------------------- */
/* BODY DEL CHAT              */
/* -------------------------- */
.chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #f3f4f6;
}

/* FORMULARIO INICIAL */
#chat-initial-form {
    background: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

#chat-initial-form h3 {
    margin-bottom: 10px;
}

#chat-initial-form input {
    width: 100%;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

#chat-initial-form button {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    margin-top: 10px;
}

#chat-initial-form button:hover {
    opacity: 0.9;
}

/* -------------------------- */
/* MENSAJES                   */
/* -------------------------- */
#chat-messages {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* mensaje del usuario */
.msg-user {
    align-self: flex-end;
    background: #2563eb;
    color: white;
    padding: 10px 14px;
    border-radius: 14px 14px 0px 14px;
    max-width: 70%;
    word-wrap: break-word;
}

/* mensaje del bot o asesor */
.msg-bot {
    align-self: flex-start;
    background: #e5e7eb;
    color: #111827;
    padding: 10px 14px;
    border-radius: 14px 14px 14px 0px;
    max-width: 70%;
    word-wrap: break-word;
}


/* -------------------------- */
/* INPUT DEL CHAT             */
/* -------------------------- */
#chat-input-area {
    display: flex;
    padding: 12px;
    background: #fff;
    border-top: 2px solid #e5e7eb;
}

#chat-input {
    flex: 1;
    padding: 10px;
    border-radius: 12px;
    border: 1px solid #ccc;
}

#chat-send-btn {
    width: 50px;
    margin-left: 10px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 18px;
}

#chat-send-btn:hover {
    opacity: 0.85;
}


/* -------------------------- */
/* MOBILE RESPONSIVE          */
/* -------------------------- */
@media (max-width: 600px) {
    #chat-widget-window {
        width: 95%;
        height: 80%;
        right: 10px;
        left: 10px;
        bottom: 100px;
    }
}
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamo Líder - Tu préstamo en 24 horas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-primary {
            background: linear-gradient(135deg, <?php echo $config['colors']['primary']; ?> 0%, <?php echo $config['colors']['secondary']; ?> 100%);
        }
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gray-50">
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
                <a href="dashboard.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Dashboard</a>
                <a href="configuracion.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Configuración</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
   <section class="relative min-h-screen flex items-center bg-hero-gradient">
    <div class="relative max-w-7xl mx-auto px-4 py-20 text-center text-white z-10">
        <h1 class="text-5xl md:text-7xl font-bold mb-6">
            <?php echo htmlspecialchars($config['hero']['title']); ?>
        </h1>

        <p class="text-2xl md:text-3xl mb-12 opacity-90">
            <?php echo htmlspecialchars($config['hero']['subtitle']); ?>
        </p>

        <a href="tel:<?php echo $config['contact']['phone']; ?>"
           class="inline-block px-12 py-5 bg-white text-blue-600 rounded-full text-xl font-bold hover:scale-105 transform transition shadow-2xl">
            <?php echo htmlspecialchars($config['hero']['cta']); ?> →
        </a>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-20">
                <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl p-6 border border-white border-opacity-20">
                    <div class="text-4xl font-bold mb-2">+10.000</div>
                    <div class="text-lg opacity-90">Clientes Satisfechos</div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl p-6 border border-white border-opacity-20">
                    <div class="text-4xl font-bold mb-2">24-72hs</div>
                    <div class="text-lg opacity-90">Aprobación Rápida</div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl p-6 border border-white border-opacity-20">
                    <div class="text-4xl font-bold mb-2">100%</div>
                    <div class="text-lg opacity-90">Seguro y Legal</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">¿Por qué elegirnos?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($config['benefits'] as $benefit): ?>
                <div class="text-center p-8 rounded-2xl bg-gradient-to-br from-blue-50 to-green-50 hover:shadow-xl transition">
                    <div class="inline-flex p-4 rounded-full mb-4 gradient-primary">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800"><?php echo htmlspecialchars($benefit['title']); ?></h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($benefit['description']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">Contactanos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center p-6">
                    <svg class="w-12 h-12 text-blue-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <h3 class="font-bold mb-2">Dirección</h3>
                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($config['contact']['address']); ?></p>
                </div>
                <div class="text-center p-6">
                    <svg class="w-12 h-12 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <h3 class="font-bold mb-2">Teléfono</h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($config['contact']['phone']); ?></p>
                    <p class="text-sm text-gray-500 mt-1">WhatsApp: <?php echo htmlspecialchars($config['contact']['whatsapp']); ?></p>
                </div>
                <div class="text-center p-6">
                    <svg class="w-12 h-12 text-blue-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="font-bold mb-2">Email</h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($config['contact']['email']); ?></p>
                </div>
                <div class="text-center p-6">
                    <svg class="w-12 h-12 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="font-bold mb-2">Horarios</h3>
                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($config['contact']['hours']); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-white py-12 gradient-primary">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex justify-center gap-6 mb-6">
                <a href="#" class="hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="#" class="hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>
                <a href="mailto:<?php echo $config['contact']['email']; ?>" class="hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </a>
            </div>
            <p class="text-sm opacity-90">© 2024 Préstamo Líder. Todos los derechos reservados.</p>
        </div>
    </footer>
<!-- ============================= -->
<!--   WIDGET DE CHAT FLOTANTE     -->
<!-- ============================= -->

<!-- Botón flotante -->
<div id="chat-widget-button">
    <div class="chat-icon">💬</div>
</div>

<!-- Ventana del chat -->
<div id="chat-widget-window">

    <!-- ENCABEZADO -->
    <div class="chat-header">
        <div class="chat-header-info">
            <strong>Asistencia en Línea</strong>
            <span class="chat-status">🟢 En línea</span>
        </div>
        <button id="chat-widget-close" class="chat-close-btn">×</button>
    </div>

    <!-- CONTENIDO DEL CHAT -->
    <div class="chat-body">

        <!-- Formulario inicial -->
        <div id="chat-initial-form">
            <h3>Antes de comenzar</h3>
            <p>Completa tus datos para iniciar el chat</p>

            <input type="text" id="chat-name" placeholder="Nombre completo">
            <input type="text" id="chat-dni" placeholder="DNI">
            <input type="text" id="chat-phone" placeholder="Teléfono">

            <button id="start-chat-btn">Iniciar Chat</button>
        </div>

        <!-- Conversación -->
        <div id="chat-messages" style="display: none;">
            <!-- Los mensajes se agregarán aquí -->
        </div>

    </div>

    <!-- INPUT DE MENSAJE -->
    <div id="chat-input-area" style="display: none;">
        <input type="text" id="chat-input" placeholder="Escribe un mensaje...">
        <button id="chat-send-btn">➤</button>
    </div>

</div>


    <script>
        // Registrar tiempo de salida
        let enterTime = <?php echo $visitTime; ?>;
        window.addEventListener('beforeunload', function() {
            let exitTime = Math.floor(Date.now() / 1000);
            let duration = exitTime - enterTime;
            navigator.sendBeacon('track_exit.php', JSON.stringify({
                session_id: '<?php echo $sessionId; ?>',
                duration: duration
            }));
        });
    </script>


<script>
// -------------------------------------------------------------------
// ELEMENTOS DEL DOM
// -------------------------------------------------------------------
const chatButton = document.getElementById("chat-widget-button");
const chatWindow = document.getElementById("chat-widget-window");
const chatClose = document.getElementById("chat-widget-close");
const initialForm = document.getElementById("chat-initial-form");
const chatMessages = document.getElementById("chat-messages");
const chatInputArea = document.getElementById("chat-input-area");
const chatInput = document.getElementById("chat-input");
const chatSendBtn = document.getElementById("chat-send-btn");

let chatId = null;        // ID del chat en la base de datos
let currentStep = null;   // paso actual del chatbot


// -------------------------------------------------------------------
// ABRIR Y CERRAR WIDGET
// -------------------------------------------------------------------
chatButton.addEventListener("click", () => {
    chatWindow.style.display = "flex";
});

chatClose.addEventListener("click", () => {
    chatWindow.style.display = "none";
});


// -------------------------------------------------------------------
// ENVIAR MENSAJE EN LA VISTA
// -------------------------------------------------------------------
function addMessage(text, sender = "bot") {
    const msg = document.createElement("div");
    msg.className = sender === "user" ? "msg-user" : "msg-bot";
    msg.textContent = text;
    chatMessages.appendChild(msg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}


// -------------------------------------------------------------------
// FORMULARIO INICIAL
// -------------------------------------------------------------------
initialForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const nombre = document.getElementById("chat-nombre").value.trim();
    const email  = document.getElementById("chat-email").value.trim();

    if (!nombre || !email) return;

    // Enviar datos iniciales al servidor
    const res = await fetch("api/chat/iniciar_chat.php", {
        method: "POST",
        body: new FormData(initialForm)
    });

    const data = await res.json();

    if (!data.success) {
        alert("Error iniciando chat.");
        return;
    }

    chatId = data.chat_id;

    // Ocultar formulario y mostrar área de chat
    initialForm.style.display = "none";
    chatInputArea.style.display = "flex";

    addMessage("¡Hola " + nombre + "! 😊 Soy el asistente virtual. Te haré unas preguntas rápidas.");

    // pedir primer paso
    solicitarSiguientePregunta();
});


// -------------------------------------------------------------------
// SOLICITAR SIGUIENTE PASO DEL CHATBOT
// -------------------------------------------------------------------
async function solicitarSiguientePregunta() {
    const res = await fetch("api/chat/chatbot_next.php?chat_id=" + chatId);
    const data = await res.json();

    if (data.success) {
        currentStep = data.step;
        addMessage(data.pregunta);
    }
}


// -------------------------------------------------------------------
// GUARDAR RESPUESTA DEL USUARIO
// -------------------------------------------------------------------
async function guardarRespuesta(texto) {

    const formData = new FormData();
    formData.append("chat_id", chatId);
    formData.append("texto", texto);

    await fetch("api/chat/chatbot_guardar_respuesta.php", {
        method: "POST",
        body: formData
    });

    // pedir siguiente paso
    solicitarSiguientePregunta();
}


// -------------------------------------------------------------------
// ENVIAR MENSAJE MANUAL (cliente escribiendo)
// -------------------------------------------------------------------
chatSendBtn.addEventListener("click", enviarMensaje);
chatInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") enviarMensaje();
});

async function enviarMensaje() {
    const texto = chatInput.value.trim();
    if (!texto) return;

    // mostrar en pantalla
    addMessage(texto, "user");
    chatInput.value = "";

    // si estamos en modo chatbot
    await guardarRespuesta(texto);
}

</script>



</body>
</html>