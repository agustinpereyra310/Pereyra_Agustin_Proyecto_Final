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

// El ID llega por GET al abrir el formulario y por POST al guardarlo
$esPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = filter_input($esPost ? INPUT_POST : INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Identificador inválido.');
}

// Buscamos al usuario (solo si está activo)
$stmt = $conexion->prepare(
    'SELECT id, nombre, apellido, dni, email
     FROM usuarios
     WHERE id = :id AND archivado_en IS NULL
     LIMIT 1'
);
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch();

if ($usuario === false) {
    http_response_code(404);
    exit('Usuario no encontrado.');
}

$errores  = [];
$mensaje  = '';
$nombre   = $usuario['nombre'];
$apellido = $usuario['apellido'];
$dni      = $usuario['dni'];
$email    = $usuario['email'];

if ($esPost) {
    // Verificamos el token CSRF
    if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni      = trim($_POST['dni'] ?? '');
    $email    = trim($_POST['email'] ?? '');

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

    // Duplicados: se excluye al propio usuario (id <> :id)
    if (empty($errores)) {
        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE dni = :dni AND id <> :id LIMIT 1');
        $stmt->execute(['dni' => $dni, 'id' => $id]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese DNI ya está registrado.';
        }

        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute(['email' => $email, 'id' => $id]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese correo ya está registrado.';
        }
    }

    // Guardar cambios
    if (empty($errores)) {
        try {
            $stmt = $conexion->prepare(
                'UPDATE usuarios
                 SET nombre = :nombre, apellido = :apellido, dni = :dni, email = :email
                 WHERE id = :id AND archivado_en IS NULL'
            );
            $stmt->execute([
                'nombre'   => $nombre,
                'apellido' => $apellido,
                'dni'      => $dni,
                'email'    => $email,
                'id'       => $id,
            ]);
            $mensaje = 'Datos actualizados correctamente.';
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
    <title>Editar Usuario - Panel Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="admin_usuarios.php">Panel</a>
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <div class="contenedor-auth" style="margin: 2rem auto;">
            <h2>Editar Usuario</h2>
            <p style="margin-bottom: 1.5rem;"><a href="admin_usuarios.php" style="color: #f3b229; text-decoration: none;">&larr; Volver al listado</a></p>

            <?php if ($mensaje !== ''): ?>
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid #22c55e; color: #22c55e; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?= escapar($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        <?php foreach ($errores as $error): ?>
                            <li><?= escapar($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="editar_usuario.php?id=<?= (int)$id ?>" method="POST">
                <!-- Token CSRF y el id del usuario, escondidos -->
                <input type="hidden" name="csrf_token" value="<?= escapar(generar_token_csrf()) ?>">
                <input type="hidden" name="id" value="<?= (int) $id ?>">

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" maxlength="80"
                           value="<?= escapar($nombre) ?>" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" maxlength="80"
                           value="<?= escapar($apellido) ?>" required>
                </div>

                <div class="form-group">
                    <label for="dni">DNI</label>
                    <input type="text" id="dni" name="dni" inputmode="numeric"
                           pattern="[0-9]{7,8}" maxlength="8"
                           value="<?= escapar($dni) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" maxlength="254"
                           value="<?= escapar($email) ?>" required>
                </div>

                <button type="submit" class="btn-boca" style="margin-top: 1rem; width: 100%; padding: 0.75rem; border-radius: 8px; font-weight: bold; cursor: pointer;">Guardar cambios</button>
            </form>
        </div>
    </main>

    <footer style="text-align: center; padding: 2rem; color: #64748b; font-size: 0.85rem;">
        &copy; 2026 Club Atlético Boca Juniors - Todos los derechos reservados.
    </footer>
</body>
</html>