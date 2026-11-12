<?php
session_start();
require_once __DIR__ . '/conexion.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Corregido: usamos $conexion y la columna correcta 'archivado_en'
        $sql = "SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = :email AND archivado_en IS NULL LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        // Corregido: validamos contra 'password_hash'
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            // Corregido: guardamos como 'usuario_rol' para que coincida con admin_usuarios.php
            $_SESSION['usuario_rol'] = $usuario['rol'];

            if ($usuario['rol'] == 'admin') {
                header("Location: admin_usuarios.php");
            } else {
                header("Location: panel.php");
            }
            exit();
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Portal de Socios Boca Juniors</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="hero">
        <h1>Club Atlético Boca Juniors</h1>
        <p>Portal Oficial de Socios</p>
    </header>

    <main>
        <div class="contenedor-auth">
            <h2>Iniciar Sesión</h2>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="tu_correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-boca">Ingresar al Portal</button>
            </form>

            <div class="auth-links">
                <p>¿Todavía no sos socio? <a href="registro.php">Hacete socio acá</a></p>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 1rem; color: #64748b; font-size: 0.85rem;">
        &copy; 2026 Club Atlético Boca Juniors - Todos los derechos reservados.
    </footer>

</body>
</html>