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

// Mensaje de una acción anterior (se muestra una sola vez)
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Token CSRF para los botones de reactivar
$tokenCsrf = generar_token_csrf();

try {
    // Solo usuarios archivados, los últimos archivados primero
    $stmt = $conexion->query(
        'SELECT id, nombre, apellido, dni, email, rol, archivado_en
         FROM usuarios
         WHERE archivado_en IS NOT NULL
         ORDER BY archivado_en DESC'
    );
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudo consultar la lista de archivados.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios archivados</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <h1>Usuarios archivados</h1>
        <p>
            <a href="admin_usuarios.php">Volver al listado de activos</a> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </p>

        <?php if ($flash !== ''): ?>
            <p><strong><?= escapar($flash) ?></strong></p>
        <?php endif; ?>

        <p><strong>Total:</strong> <?= count($usuarios) ?></p>

        <?php if (empty($usuarios)): ?>
            <p>No hay usuarios archivados.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Archivado el</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= escapar((string) $usuario['id']) ?></td>
                            <td><?= escapar($usuario['nombre']) ?></td>
                            <td><?= escapar($usuario['apellido']) ?></td>
                            <td><?= escapar($usuario['dni']) ?></td>
                            <td><?= escapar($usuario['email']) ?></td>
                            <td><?= escapar($usuario['rol']) ?></td>
                            <td><?= escapar($usuario['archivado_en']) ?></td>
                            <td>
                                <form method="POST" action="reactivar_usuario.php" style="display:inline"
                                      onsubmit="return confirm('¿Reactivar a este usuario?');">
                                    <input type="hidden" name="csrf_token" value="<?= escapar($tokenCsrf) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                    <button type="submit">Reactivar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>