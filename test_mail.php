<?php
/**
 * SCRIPT DE DIAGNÓSTICO COMPLETO
 * Guarda este archivo en: /system/test_email_completo.php
 * Ejecuta desde navegador: https://prestamolider.com/system/test_email_completo.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DE SISTEMA DE EMAILS ===\n\n";

// ============================================
// 1. VERIFICAR ARCHIVOS NECESARIOS
// ============================================
echo "1️⃣ VERIFICANDO ARCHIVOS...\n";

$archivosRequeridos = [
    'templates.json' => __DIR__ . '/templates.json',
    'config.json' => __DIR__ . '/config.json',
    'EmailDispatcher.php' => __DIR__ . '/correos/EmailDispatcher.php',
    'ExternalProvider.php' => __DIR__ . '/correos/ExternalProvider.php',
    'TemplateEngine.php' => __DIR__ . '/correos/TemplateEngine.php',
    'PHPMailer.php' => __DIR__ . '/mail/PHPMailer/PHPMailer.php',
    'SMTP.php' => __DIR__ . '/mail/PHPMailer/SMTP.php',
    'Exception.php' => __DIR__ . '/mail/PHPMailer/Exception.php',
];

$todosExisten = true;
foreach ($archivosRequeridos as $nombre => $ruta) {
    $existe = file_exists($ruta);
    echo ($existe ? "  ✅" : "  ❌") . " $nombre: ";
    echo $existe ? "OK ($ruta)\n" : "NO EXISTE ($ruta)\n";
    if (!$existe) $todosExisten = false;
}

if (!$todosExisten) {
    echo "\n❌ FALTA(N) ARCHIVO(S) CRÍTICO(S) - NO SE PUEDE CONTINUAR\n";
    exit(1);
}

echo "\n";

// ============================================
// 2. VERIFICAR templates.json
// ============================================
echo "2️⃣ VERIFICANDO templates.json...\n";

$templatesPath = __DIR__ . '/templates.json';
$templatesContent = file_get_contents($templatesPath);
$templates = json_decode($templatesContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "  ❌ ERROR: JSON inválido - " . json_last_error_msg() . "\n";
    exit(1);
}

echo "  ✅ JSON válido\n";
echo "  Templates encontrados: " . count($templates) . "\n";
echo "  Templates disponibles: " . implode(', ', array_keys($templates)) . "\n\n";

if (!isset($templates['docs_aprobados'])) {
    echo "  ❌ ERROR: Falta template 'docs_aprobados'\n";
    exit(1);
}

echo "  ✅ Template 'docs_aprobados' existe\n";
echo "     Subject: " . $templates['docs_aprobados']['subject'] . "\n";

if (!isset($templates['docs_rechazados'])) {
    echo "  ❌ ERROR: Falta template 'docs_rechazados'\n";
    exit(1);
}

echo "  ✅ Template 'docs_rechazados' existe\n";
echo "     Subject: " . $templates['docs_rechazados']['subject'] . "\n";

echo "\n";

// ============================================
// 3. VERIFICAR config.json
// ============================================
echo "3️⃣ VERIFICANDO config.json...\n";

$configPath = __DIR__ . '/config.json';
if (!file_exists($configPath)) {
    echo "  ❌ ERROR: config.json NO EXISTE en: $configPath\n\n";
    echo "  SOLUCIÓN: Crea el archivo config.json con:\n";
    echo "  {\n";
    echo "    \"smtp\": {\n";
    echo "      \"host\": \"mail.tudominio.com\",\n";
    echo "      \"port\": 465,\n";
    echo "      \"username\": \"tu_email@tudominio.com\",\n";
    echo "      \"password\": \"tu_password\",\n";
    echo "      \"from_email\": \"noreply@prestamolider.com\",\n";
    echo "      \"from_name\": \"Préstamo Líder\",\n";
    echo "      \"encryption\": \"ssl\",\n";
    echo "      \"secure\": \"ssl\"\n";
    echo "    }\n";
    echo "  }\n";
    exit(1);
}

$configContent = file_get_contents($configPath);
$config = json_decode($configContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "  ❌ ERROR: JSON inválido - " . json_last_error_msg() . "\n";
    exit(1);
}

echo "  ✅ JSON válido\n";

if (!isset($config['smtp'])) {
    echo "  ❌ ERROR: Falta sección 'smtp' en config.json\n";
    exit(1);
}

$smtp = $config['smtp'];
$camposRequeridos = ['host', 'port', 'username', 'password', 'from_email', 'from_name', 'encryption'];

foreach ($camposRequeridos as $campo) {
    if (!isset($smtp[$campo]) || empty($smtp[$campo])) {
        echo "  ❌ ERROR: Falta campo SMTP '$campo'\n";
        exit(1);
    }
    
    // Ocultar password
    $valor = $campo === 'password' ? str_repeat('*', min(8, strlen($smtp[$campo]))) : $smtp[$campo];
    echo "  ✅ $campo: $valor\n";
}

echo "\n";

// ============================================
// 4. VERIFICAR CARPETA DE LOGS
// ============================================
echo "4️⃣ VERIFICANDO CARPETA DE LOGS...\n";

$logsDir = __DIR__ . '/correos/logs';
if (!is_dir($logsDir)) {
    echo "  ⚠️  Carpeta no existe. Intentando crear...\n";
    if (@mkdir($logsDir, 0777, true)) {
        echo "  ✅ Carpeta creada exitosamente\n";
    } else {
        echo "  ❌ ERROR: No se pudo crear la carpeta de logs\n";
        echo "     Crea manualmente: $logsDir\n";
        echo "     Permisos: 777\n";
        exit(1);
    }
} else {
    echo "  ✅ Carpeta existe\n";
}

// Probar escritura
$testFile = $logsDir . '/test_' . time() . '.txt';
if (@file_put_contents($testFile, 'test')) {
    echo "  ✅ Permisos de escritura OK\n";
    @unlink($testFile);
} else {
    echo "  ❌ ERROR: No se puede escribir en la carpeta de logs\n";
    echo "     Ejecuta: chmod 777 $logsDir\n";
    exit(1);
}

echo "\n";

// ============================================
// 5. PROBAR CARGA DE CLASES
// ============================================
echo "5️⃣ PROBANDO CARGA DE CLASES...\n";

try {
    require_once __DIR__ . '/correos/EmailDispatcher.php';
    echo "  ✅ EmailDispatcher cargado\n";
} catch (Throwable $e) {
    echo "  ❌ ERROR cargando EmailDispatcher: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// ============================================
// 6. INSTANCIAR EmailDispatcher
// ============================================
echo "6️⃣ INSTANCIANDO EmailDispatcher...\n";

try {
    $mailer = new EmailDispatcher();
    echo "  ✅ EmailDispatcher instanciado correctamente\n";
} catch (Throwable $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n";

// ============================================
// 7. ENVIAR EMAIL DE PRUEBA
// ============================================
echo "7️⃣ ENVIANDO EMAIL DE PRUEBA...\n";
echo "  ⚠️  IMPORTANTE: Cambia el email destino en el código\n\n";

// 🔴 CAMBIA ESTE EMAIL POR EL TUYO
$emailDestino = 'tu_email@gmail.com';

echo "  Enviando a: $emailDestino\n";
echo "  Template: docs_aprobados\n";
echo "  Esperando respuesta del servidor SMTP...\n\n";

try {
    $resultado = $mailer->send(
        'docs_aprobados',
        $emailDestino,
        [
            'nombre' => 'Juan Pérez TEST'
        ]
    );
    
    if ($resultado) {
        echo "  ✅✅✅ EMAIL ENVIADO EXITOSAMENTE ✅✅✅\n";
        echo "  Revisa tu bandeja de entrada (y spam)\n";
    } else {
        echo "  ❌ El método send() retornó FALSE\n";
        echo "  Revisa el log: $logsDir/emails.log\n";
    }
    
} catch (Throwable $e) {
    echo "  ❌ ERROR ENVIANDO EMAIL:\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "  Archivo: " . $e->getFile() . "\n";
    echo "  Línea: " . $e->getLine() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";

// ============================================
// 8. MOSTRAR LOGS
// ============================================
echo "8️⃣ LOGS DEL SISTEMA...\n";

$logFile = $logsDir . '/emails.log';
if (file_exists($logFile)) {
    echo "  ✅ Log encontrado: $logFile\n\n";
    echo "  ÚLTIMAS 20 LÍNEAS:\n";
    echo "  " . str_repeat("-", 60) . "\n";
    
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        echo "  " . $line;
    }
    echo "  " . str_repeat("-", 60) . "\n";
} else {
    echo "  ⚠️  No hay archivo de log todavía\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";