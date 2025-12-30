<?php
/**
 * verificar_sistema.php
 * Script de verificación del sistema de emails
 * 
 * Ubicación: /system/verificar_sistema.php
 * Ejecutar desde: https://tudominio.com/system/verificar_sistema.php
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación del Sistema</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .check { margin: 15px 0; padding: 15px; border-radius: 5px; }
        .check.ok { background: #d4edda; border-left: 4px solid #28a745; }
        .check.error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .check.warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .icon { font-weight: bold; font-size: 18px; margin-right: 10px; }
        .icon.ok { color: #28a745; }
        .icon.error { color: #dc3545; }
        .icon.warning { color: #ffc107; }
        .path { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .section { margin-top: 30px; }
        .section h2 { color: #555; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación del Sistema de Emails</h1>
        <p><strong>Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

        <div class="section">
            <h2>1. Archivos del Sistema</h2>
            <?php
            $archivos = [
                'config.json' => __DIR__ . '/config.json',
                'templates.json' => __DIR__ . '/templates.json',
                'EmailDispatcher' => __DIR__ . '/correos/EmailDispatcher.php',
                'TemplateEngine' => __DIR__ . '/correos/TemplateEngine.php',
                'ExternalProvider' => __DIR__ . '/correos/ExternalProvider.php',
                'solicitudes_estado API' => __DIR__ . '/api/solicitudes_estado.php',
                'PHPMailer' => __DIR__ . '/mail/PHPMailer/PHPMailer.php',
            ];

            foreach ($archivos as $nombre => $ruta) {
                if (file_exists($ruta)) {
                    echo "<div class='check ok'><span class='icon ok'>✓</span><strong>$nombre:</strong> <span class='path'>$ruta</span></div>";
                } else {
                    echo "<div class='check error'><span class='icon error'>✗</span><strong>$nombre:</strong> <span class='path'>$ruta</span> <strong>NO ENCONTRADO</strong></div>";
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>2. Carpetas de Logs</h2>
            <?php
            $carpetas = [
                'Logs de emails' => __DIR__ . '/correos/logs',
                'Logs de API' => __DIR__ . '/logs',
            ];

            foreach ($carpetas as $nombre => $ruta) {
                if (is_dir($ruta)) {
                    $permisos = substr(sprintf('%o', fileperms($ruta)), -4);
                    $writable = is_writable($ruta) ? 'Escritura OK' : 'SIN PERMISOS DE ESCRITURA';
                    $class = is_writable($ruta) ? 'ok' : 'error';
                    echo "<div class='check $class'><span class='icon $class'>". (is_writable($ruta) ? '✓' : '✗') ."</span><strong>$nombre:</strong> <span class='path'>$ruta</span> (Permisos: $permisos) - $writable</div>";
                } else {
                    echo "<div class='check error'><span class='icon error'>✗</span><strong>$nombre:</strong> <span class='path'>$ruta</span> <strong>NO EXISTE</strong></div>";
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>3. Configuración SMTP</h2>
            <?php
            $configFile = __DIR__ . '/config.json';
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                if (isset($config['smtp'])) {
                    $smtp = $config['smtp'];
                    echo "<div class='check ok'><span class='icon ok'>✓</span><strong>Configuración SMTP encontrada</strong></div>";
                    
                    $camposRequeridos = ['host', 'port', 'username', 'password', 'from_email', 'from_name'];
                    foreach ($camposRequeridos as $campo) {
                        if (isset($smtp[$campo]) && !empty($smtp[$campo])) {
                            $valor = $campo === 'password' ? '********' : $smtp[$campo];
                            echo "<div class='check ok'><span class='icon ok'>✓</span><strong>$campo:</strong> $valor</div>";
                        } else {
                            echo "<div class='check error'><span class='icon error'>✗</span><strong>$campo:</strong> FALTANTE</div>";
                        }
                    }
                } else {
                    echo "<div class='check error'><span class='icon error'>✗</span><strong>Sección SMTP no encontrada en config.json</strong></div>";
                }
            } else {
                echo "<div class='check error'><span class='icon error'>✗</span><strong>config.json no encontrado</strong></div>";
            }
            ?>
        </div>

        <div class="section">
            <h2>4. Plantillas de Email</h2>
            <?php
            $templatesFile = __DIR__ . '/templates.json';
            if (file_exists($templatesFile)) {
                $templates = json_decode(file_get_contents($templatesFile), true);
                if (is_array($templates)) {
                    echo "<div class='check ok'><span class='icon ok'>✓</span><strong>Plantillas cargadas:</strong> " . count($templates) . " plantillas</div>";
                    
                    $plantillasRequeridas = ['docs_aprobados', 'docs_rechazados'];
                    foreach ($plantillasRequeridas as $plantilla) {
                        if (isset($templates[$plantilla])) {
                            $tieneSubject = isset($templates[$plantilla]['subject']) && !empty($templates[$plantilla]['subject']);
                            $tieneBody = isset($templates[$plantilla]['body']) && !empty($templates[$plantilla]['body']);
                            
                            if ($tieneSubject && $tieneBody) {
                                echo "<div class='check ok'><span class='icon ok'>✓</span><strong>$plantilla:</strong> OK (subject + body)</div>";
                            } else {
                                echo "<div class='check error'><span class='icon error'>✗</span><strong>$plantilla:</strong> Incompleta</div>";
                            }
                        } else {
                            echo "<div class='check error'><span class='icon error'>✗</span><strong>$plantilla:</strong> NO ENCONTRADA</div>";
                        }
                    }
                } else {
                    echo "<div class='check error'><span class='icon error'>✗</span><strong>templates.json inválido</strong></div>";
                }
            } else {
                echo "<div class='check error'><span class='icon error'>✗</span><strong>templates.json no encontrado</strong></div>";
            }
            ?>
        </div>

        <div class="section">
            <h2>5. Test de EmailDispatcher</h2>
            <?php
            try {
                if (file_exists(__DIR__ . '/correos/EmailDispatcher.php')) {
                    require_once __DIR__ . '/correos/EmailDispatcher.php';
                    $mailer = new EmailDispatcher();
                    echo "<div class='check ok'><span class='icon ok'>✓</span><strong>EmailDispatcher se puede instanciar correctamente</strong></div>";
                } else {
                    echo "<div class='check error'><span class='icon error'>✗</span><strong>EmailDispatcher.php no encontrado</strong></div>";
                }
            } catch (Exception $e) {
                echo "<div class='check error'><span class='icon error'>✗</span><strong>Error al instanciar EmailDispatcher:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            ?>
        </div>

        <div class="section">
            <h2>6. Información del Servidor</h2>
            <div class='check ok'>
                <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
                <strong>Sistema Operativo:</strong> <?php echo PHP_OS; ?><br>
                <strong>Directorio actual:</strong> <span class='path'><?php echo __DIR__; ?></span>
            </div>
        </div>

        <div class="section">
            <h2>7. Logs Recientes</h2>
            <?php
            $logEmailsFile = __DIR__ . '/correos/logs/emails.log';
            $logAPIFile = __DIR__ . '/logs/api_solicitudes_estado.log';

            echo "<h3>Últimas 10 líneas - Log de Emails:</h3>";
            if (file_exists($logEmailsFile)) {
                $lines = file($logEmailsFile);
                $lastLines = array_slice($lines, -10);
                echo "<pre>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
            } else {
                echo "<div class='check warning'><span class='icon warning'>⚠</span>No hay log de emails aún</div>";
            }

            echo "<h3>Últimas 10 líneas - Log de API:</h3>";
            if (file_exists($logAPIFile)) {
                $lines = file($logAPIFile);
                $lastLines = array_slice($lines, -10);
                echo "<pre>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
            } else {
                echo "<div class='check warning'><span class='icon warning'>⚠</span>No hay log de API aún</div>";
            }
            ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">🔄 Actualizar Verificación</a>
        </div>
    </div>
</body>
</html>
