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

// Detectamos la página actual para el menú dinámico de la barra superior
$pagina_actual = basename($_SERVER['PHP_SELF']);

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
    <title>Panel administrativo - Club Atlético Boca Juniors</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #001233;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-bottom: 3rem;
        }
        /* Barra superior unificada */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 2.5rem;
            background-color: #001f5b;
            border-bottom: 2px solid #f3b229;
        }
        .top-nav .logo-club {
            font-weight: bold;
            color: #f3b229;
            font-size: 1.2rem;
        }
        .top-nav-links {
            display: flex;
            gap: 1.8rem;
        }
        .top-nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: color 0.2s;
        }
        .top-nav-links a:hover {
            color: #f3b229;
        }

        /* Contenedor Principal */
        .main-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .admin-header-title {
            font-size: 2.2rem;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .admin-nav-links {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .admin-nav-links a {
            background-color: #001f5b;
            color: #f3b229;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid rgba(243, 178, 41, 0.3);
            transition: all 0.2s;
        }
        .admin-nav-links a:hover {
            background-color: #f3b229;
            color: #001f5b;
        }

        /* Alerta Flash */
        .alert-flash {
            background-color: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            color: #4ade80;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        /* Buscador */
        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background-color: #001f5b;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            align-items: center;
            flex-wrap: wrap;
        }
        .search-form input[type="search"] {
            flex: 1;
            min-width: 250px;
            background-color: #001233;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.8rem 1rem;
            color: #ffffff;
            font-size: 1rem;
        }
        .search-form input[type="search"]:focus {
            outline: none;
            border-color: #f3b229;
        }
        .search-form button {
            background-color: #f3b229;
            color: #001f5b;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .search-form button:hover {
            opacity: 0.9;
        }
        .search-form a {
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
        }
        .search-form a:hover {
            color: #ffffff;
        }

        /* Sección y Tabla */
        h2 {
            color: #f3b229;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .table-container {
            background-color: #001f5b;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            margin-top: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            color: #ffffff;
        }

        th {
            background-color: rgba(0, 18, 51, 0.9);
            color: #f3b229;
            padding: 1.2rem 1rem;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f3b229;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.95rem;
            color: #e2e8f0;
        }

        tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }

        /* Botones de acción en tabla */
        .btn-editar {
            background-color: #0284c7;
            color: #ffffff;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn-editar:hover {
            background-color: #0369a1;
        }

        .btn-archivar {
            background-color: #ef4444;
            color: #ffffff;
            border: none;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-archivar:hover {
            background-color: #dc2626;
        }

        .total-socios {
            color: #94a3b8;
            margin-bottom: 1rem;
            font-size: 1.05rem;
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR DINÁMICA -->
    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="panel.php" style="<?= ($pagina_actual == 'panel.php') ? 'color: #f3b229;' : '' ?>">Inicio</a>
            <a href="partidos.php" style="<?= ($pagina_actual == 'partidos.php') ? 'color: #f3b229;' : '' ?>">Partidos</a>
            <a href="pagos.php" style="<?= ($pagina_actual == 'pagos.php') ? 'color: #f3b229;' : '' ?>">Pagos</a>
            <a href="beneficios.php" style="<?= ($pagina_actual == 'beneficios.php') ? 'color: #f3b229;' : '' ?>">Beneficios</a>
            <a href="categoria.php" style="<?= ($pagina_actual == 'categoria.php') ? 'color: #f3b229;' : '' ?>">Mi Categoría</a>
            <?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="<?= ($pagina_actual == 'admin_usuarios.php') ? 'color: #f3b229;' : '' ?>">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        
        <h1 class="admin-header-title">Panel Administrativo</h1>
        
        <!-- Enlaces internos de administración estilizados -->
        <div class="admin-nav-links">
            <a href="panel.php">Mi cuenta</a>
            <a href="admin_crear_usuario.php">Registrar nuevo usuario</a>
            <a href="usuarios_archivados.php">Ver archivados</a>
        </div>

        <?php if ($flash !== ''): ?>
            <div class="alert-flash">✓ <?= escapar($flash) ?></div>
        <?php endif; ?>

        <h2>Socios Activos</h2>

        <!-- Formulario de búsqueda -->
        <form method="GET" action="admin_usuarios.php" class="search-form">
            <input type="search" name="buscar" maxlength="100"
                   placeholder="Buscar por nombre, apellido, DNI o correo..."
                   value="<?= escapar($buscar) ?>">
            <button type="submit">Buscar</button>
            <?php if ($buscar !== ''): ?>
                <a href="admin_usuarios.php">Ver todos</a>
            <?php endif; ?>
        </form>

        <p class="total-socios"><strong>Total de socios encontrados:</strong> <?= count($usuarios) ?></p>

        <?php if (empty($usuarios)): ?>
            <div style="background-color: #001f5b; padding: 2rem; border-radius: 12px; text-align: center; color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);">
                No se encontraron usuarios activos con ese criterio de búsqueda.
            </div>
        <?php else: ?>
            <div class="table-container">
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
                                <td><span style="background: rgba(243,178,41,0.15); color: #f3b229; padding: 0.2rem 0.6rem; border-radius: 4px; font-weight: bold; font-size: 0.85rem;"><?= escapar($usuario['rol']) ?></span></td>
                                <td><?= escapar($usuario['creado_en']) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <a href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>" class="btn-editar">Editar</a>

                                        <?php if ((int) $usuario['id'] !== (int) $_SESSION['usuario_id']): ?>
                                            <form method="POST" action="archivar_usuario.php" style="display:inline; margin:0;"
                                                  onsubmit="return confirm('¿Seguro que querés archivar a este usuario?');">
                                                <input type="hidden" name="csrf_token" value="<?= escapar($tokenCsrf) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                                <button type="submit" class="btn-archivar">Archivar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>