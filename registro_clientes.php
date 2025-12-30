<?php
session_start();
require_once __DIR__ . '/backend/connection.php';

$error = '';
$success = '';

$nombreValue   = htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$apellidoValue = htmlspecialchars($_POST['apellido'] ?? '', ENT_QUOTES, 'UTF-8');
$emailValue    = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // =========================
    // VALIDACIONES
    // =========================
    if ($nombre === '' || $apellido === '' || $email === '' || $password === '') {
        $error = 'Completá todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {

        try {
            // =========================
            // INICIO TRANSACCIÓN
            // =========================
            $pdo->beginTransaction();

            // Verificar email único
            $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
            $chk->execute([$email]);

            if ($chk->fetch()) {
                throw new Exception('Ya existe una cuenta con ese email.');
            }

            // Hash seguro
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Crear usuario
            $ins = $pdo->prepare("
                INSERT INTO usuarios
                (nombre, apellido, email, password, rol, estado, fecha_registro)
                VALUES (?, ?, ?, ?, 'cliente', 'activo', NOW())
            ");
            $ins->execute([$nombre, $apellido, $email, $hash]);

            $usuario_id = (int)$pdo->lastInsertId();
            if ($usuario_id <= 0) {
                throw new Exception('No se pudo crear el usuario.');
            }

            // Crear detalles cliente
            $insDet = $pdo->prepare("
                INSERT INTO clientes_detalles (usuario_id, docs_completos)
                VALUES (?, 0)
            ");
            $insDet->execute([$usuario_id]);

            // =========================
            // COMMIT DB
            // =========================
            $pdo->commit();

            // =========================
            // ENVÍO DE MAIL (NO BLOQUEANTE)
            // =========================
            try {
                require_once __DIR__ . '/correos/EmailDispatcher.php';

                (new EmailDispatcher())->send(
                    'registro',
                    $email,
                    [
                        'nombre'      => trim($nombre . ' ' . $apellido),
                        'email'       => $email,
                        'link_login'  => 'https://prestamolider.com/system/login_clientes.php',
                    ]
                );
            } catch (Throwable $mailError) {
                // Log interno si querés, pero NO rompemos el registro
            }

            $success = 'Cuenta creada correctamente. Revisá tu email.';
            $nombreValue = $apellidoValue = $emailValue = '';

        } catch (Throwable $e) {
            // Rollback seguro
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Clientes - Préstamo Líder</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-600 to-blue-800 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">

  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full mb-4">
      <span class="material-icons-outlined text-white text-4xl">person_add</span>
    </div>
    <h1 class="text-3xl font-bold text-gray-800">Crear cuenta</h1>
    <p class="text-gray-600 mt-2">Registro de clientes · Préstamo Líder</p>
  </div>

  <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
      <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
      <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-5" novalidate>

    <input type="text" name="nombre" required value="<?php echo $nombreValue; ?>"
           class="w-full px-4 py-3 border rounded-lg" placeholder="Nombre">

    <input type="text" name="apellido" required value="<?php echo $apellidoValue; ?>"
           class="w-full px-4 py-3 border rounded-lg" placeholder="Apellido">

    <input type="email" name="email" required value="<?php echo $emailValue; ?>"
           class="w-full px-4 py-3 border rounded-lg" placeholder="Email">

    <input type="password" name="password" required minlength="6"
           class="w-full px-4 py-3 border rounded-lg" placeholder="Contraseña">

    <button type="submit"
            class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold">
      Crear cuenta
    </button>

  </form>

  <div class="mt-8 pt-6 border-t text-center text-sm">
    <a href="login_clientes.php" class="text-blue-600 hover:underline">← Volver al login</a>
  </div>

</div>
</body>
</html>
