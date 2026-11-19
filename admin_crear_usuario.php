<?php
session_start();

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
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

    if ($nombre === '') { $errores[] = 'El nombre es obligatorio.'; }
    if ($apellido === '') { $errores[] = 'El apellido es obligatorio.'; }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) { $errores[] = 'El DNI debe tener 7 u 8 números.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errores[] = 'Ingresá un correo válido.'; }
    if (strlen($password) < 8) { $errores[] = 'La contraseña debe tener al menos 8 caracteres.'; }
    if ($password !== $confirmar) { $errores[] = 'Las contraseñas no coinciden.'; }
    // El rol solo puede ser uno de los dos valores permitidos
    if (!in_array($rol, ['usuario', 'admin'], true)) { $errores[] = 'El rol no es válido.'; }

    if (empty($errores)) {
        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1');
        $stmt->execute(['dni' => $dni]);
        if ($stmt->fetch()) { $errores[] = 'Ese DNI ya está registrado.'; }

        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) { $errores[] = 'Ese correo ya está registrado.'; }
    }

    if (empty($errores)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = 'INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol)
                    VALUES (:nombre, :apellido, :dni, :email, :password_hash, :rol)';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'nombre'        => $nombre,
                'apellido'      => $apellido,
                'dni'           => $dni,
                'email'         => $email,
                'password_hash' => $passwordHash,
                'rol'           => $rol
            ]);

            $mensaje = '¡Usuario creado con éxito!';
            $nombre = $apellido = $dni = $email = '';
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
    <title>Registrar Nuevo Usuario - Panel Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="top-nav">
        <div class="logo-club" style="padding-left: 1.5rem; font-weight: bold; color: #f3b229;">CABJ | Panel Administrativo</div>
        <div class="top-nav-links" style="padding-right: 1.5rem;">
            <a href="admin_usuarios.php">Volver al Panel</a>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <div class="contenedor-auth" style="margin: 2rem auto;">
            <h2>Registrar Nuevo Usuario</h2>

            <?php if ($mensaje !== ''): ?>
                <div class="alert-success" style="background: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; border-left: 4px solid #22c55e;">
                    <?= escapar($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="alert-error" style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; border-left: 4px solid #ef4444;">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        <?php foreach ($errores as $error): ?>
                            <li><?= escapar($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="admin_crear_usuario.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= escapar(generar_token_csrf()) ?>">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" maxlength="80" value="<?= escapar($nombre) ?>" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" maxlength="80" value="<?= escapar($apellido) ?>" required>
                </div>

                <div class="form-group">
                    <label for="dni">DNI</label>
                    <input type="text" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" value="<?= escapar($dni) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" maxlength="254" value="<?= escapar($email) ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña (mínimo 8 caracteres)</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirmar">Repetir contraseña</label>
                    <input type="password" id="confirmar" name="confirmar" required>
                </div>

                <div class="form-group">
                    <label for="rol">Rol del Usuario</label>
                    <select id="rol" name="rol" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: #001233; color: white; border: 1px solid rgba(255,255,255,0.2);">
                        <option value="usuario" <?= $rol === 'usuario' ? 'selected' : '' ?>>Usuario (Socio)</option>
                        <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn-boca" style="margin-top: 1rem; width: 100%; padding: 0.75rem; border-radius: 8px; font-weight: bold; cursor: pointer;">Crear Usuario</button>
            </form>
        </div>
    </main>

    <footer style="text-align: center; padding: 1rem; color: #64748b; font-size: 0.85rem;">
        &copy; 2026 Club Atlético Boca Juniors - Todos los derechos reservados.
    </footer>
</body>
</html>