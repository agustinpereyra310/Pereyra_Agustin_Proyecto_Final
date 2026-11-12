<?php
require_once __DIR__ . '/conexion.php';

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

$errores  = [];
$mensaje  = '';
$nombre   = '';
$apellido = '';
$dni      = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $dni       = trim($_POST['dni'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

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
                'rol'           => 'usuario'
            ]);

            $mensaje  = '¡Te registraste correctamente! Ya sos socio.';
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
    <title>Hacete Socio - Club Atlético Boca Juniors</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="hero">
        <h1>Club Atlético Boca Juniors</h1>
        <p>Asociación Oficial de Socios</p>
    </header>

    <main>
        <div class="contenedor-auth">
            <h2>Hacete socio</h2>

            <?php if ($mensaje !== ''): ?>
                <div class="alert-success" style="background: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; border-left: 4px solid #22c55e;">
                    <?= escapar($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="alert-error">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        <?php foreach ($errores as $error): ?>
                            <li><?= escapar($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="registro.php" method="POST">
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

                <button type="submit" class="btn-boca">Crear cuenta</button>
            </form>

            <div class="auth-links">
                <p>¿Ya sos socio? <a href="login.php">Iniciá sesión</a></p>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 1rem; color: #64748b; font-size: 0.85rem;">
        &copy; 2026 Club Atlético Boca Juniors - Todos los derechos reservados.
    </footer>
</body>
</html>