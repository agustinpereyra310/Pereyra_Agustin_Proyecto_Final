<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// PROTECCIÓN: solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

$errores  = [];
$mensaje  = '';
$nombre   = '';
$apellido = '';
$dni      = '';
$email    = '';
$rol      = 'usuario';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Primero verificamos el token CSRF
    if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $dni       = trim($_POST['dni'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    $rol       = $_POST['rol'] ?? 'usuario';

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }
    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($password !== $confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    // El rol solo puede ser uno de los dos valores permitidos
    if (!in_array($rol, ['usuario', 'admin'], true)) {
        $errores[] = 'El rol no es válido.';
    }

    // Duplicados
    if (empty($errores)) {
        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1');
        $stmt->execute(['dni' => $dni]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese DNI ya está registrado.';
        }

        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese correo ya está registrado.';
        }
    }

    // Guardar
    if (empty($errores)) {
        try {
            $stmt = $conexion->prepare(
                'INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol)
                 VALUES (:nombre, :apellido, :dni, :email, :password_hash, :rol)'
            );
            $stmt->execute([
                'nombre'        => $nombre,
                'apellido'      => $apellido,
                'dni'           => $dni,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'rol'           => $rol
            ]);

            $mensaje = 'Usuario registrado correctamente.';
            $nombre = $apellido = $dni = $email = '';
            $rol = 'usuario';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'El DNI o el correo ya están registrados.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <h1>Registrar nuevo usuario</h1>
        <p><a href="admin_usuarios.php">Volver al listado</a></p>

        <?php if ($mensaje !== ''): ?>
            <p><?= escapar($mensaje) ?></p>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= escapar($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="admin_crear_usuario.php" method="POST">
            <!-- Token CSRF escondido -->
            <input type="hidden" name="csrf_token" value="<?= escapar(generar_token_csrf()) ?>">

            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" maxlength="80"
                   value="<?= escapar($nombre) ?>" required>

            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" maxlength="80"
                   value="<?= escapar($apellido) ?>" required>

            <label for="dni">DNI</label>
            <input type="text" id="dni" name="dni" inputmode="numeric"
                   pattern="[0-9]{7,8}" maxlength="8"
                   value="<?= escapar($dni) ?>" required>

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" maxlength="254"
                   value="<?= escapar($email) ?>" required>

            <label for="password">Contraseña (mínimo 8 caracteres)</label>
            <input type="password" id="password" name="password" required>

            <label for="confirmar">Repetir contraseña</label>
            <input type="password" id="confirmar" name="confirmar" required>

            <label for="rol">Rol</label>
            <select id="rol" name="rol">
                <option value="usuario" <?= $rol === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <button type="submit">Registrar usuario</button>
        </form>
    </main>
</body>
</html>