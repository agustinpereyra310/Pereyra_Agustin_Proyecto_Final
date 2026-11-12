<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// PROTECCIÓN: solo entra un usuario logueado con rol admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

// Mensaje de una acción anterior (archivar, etc.): se muestra una sola vez
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Token CSRF para los botones de archivar
$tokenCsrf = generar_token_csrf();

// Texto que escribió el admin en el buscador (llega por GET)
$buscar = trim($_GET['buscar'] ?? '');

// Consulta base: solo usuarios activos (no archivados)
$sql = 'SELECT id, nombre, apellido, dni, email, rol, creado_en
        FROM usuarios
        WHERE archivado_en IS NULL';
$parametros = [];

// Si hay texto de búsqueda, agregamos el filtro con parámetros (sin concatenar el texto)
if ($buscar !== '') {
    $sql .= ' AND (nombre LIKE :nombre
                OR apellido LIKE :apellido
                OR dni LIKE :dni
                OR email LIKE :email)';
    $termino = '%' . $buscar . '%';
    $parametros = [
        'nombre'   => $termino,
        'apellido' => $termino,
        'dni'      => $termino,
        'email'    => $termino,
    ];
}

$sql .= ' ORDER BY apellido, nombre';

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudo consultar la lista de socios.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel administrativo</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <h1>Panel administrativo</h1>
        <p>
            <a href="panel.php">Mi cuenta</a> |
            <a href="admin_crear_usuario.php">Registrar nuevo usuario</a> |
            <a href="usuarios_archivados.php">Ver archivados</a> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </p>

        <?php if ($flash !== ''): ?>
            <p><strong><?= escapar($flash) ?></strong></p>
        <?php endif; ?>

        <h2>Socios activos</h2>

        <form method="GET" action="admin_usuarios.php">
            <input type="search" name="buscar" maxlength="100"
                   placeholder="Nombre, apellido, DNI o correo"
                   value="<?= escapar($buscar) ?>">
            <button type="submit">Buscar</button>
            <?php if ($buscar !== ''): ?>
                <a href="admin_usuarios.php">Ver todos</a>
            <?php endif; ?>
        </form>

        <p><strong>Total:</strong> <?= count($usuarios) ?></p>

        <?php if (empty($usuarios)): ?>
            <p>No se encontraron usuarios.</p>
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
                        <th>Alta</th>
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
                            <td><?= escapar($usuario['creado_en']) ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>">Editar</a>

                                <?php if ((int) $usuario['id'] !== (int) $_SESSION['usuario_id']): ?>
                                    <form method="POST" action="archivar_usuario.php" style="display:inline"
                                          onsubmit="return confirm('¿Seguro que querés archivar a este usuario?');">
                                        <input type="hidden" name="csrf_token" value="<?= escapar($tokenCsrf) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                        <button type="submit">Archivar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>