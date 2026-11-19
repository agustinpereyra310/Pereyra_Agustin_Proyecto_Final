<?php
session_start();

// Guardia de seguridad: solo entra el admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// Mensaje de una acción anterior (reactivar): se muestra una sola vez
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Token CSRF para los botones de reactivar
$tokenCsrf = generar_token_csrf();

// Traemos únicamente a los usuarios que SÍ tienen fecha de archivo (dados de baja)
$stmt = $conexion->prepare("SELECT id, nombre, apellido, dni, email, rol, archivado_en FROM usuarios WHERE archivado_en IS NOT NULL ORDER BY archivado_en DESC");
$stmt->execute();
$archivados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Socios Archivados - Panel Admin</title>
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
        <?php if ($flash !== ''): ?>
            <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid #22c55e; color: #22c55e; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem;">✓ <?= escapar($flash) ?></div>
        <?php endif; ?>

        <h2 style="margin-bottom: 1rem; color: #f3b229;">Socios Archivados (Dados de baja)</h2>
        <p style="color: #94a3b8; margin-bottom: 2rem;">Acá se listan todos los usuarios que fueron archivados. Podés reactivarlos en cualquier momento para que recuperen el acceso.</p>

        <?php if (count($archivados) === 0): ?>
            <div style="background: rgba(255, 255, 255, 0.05); padding: 2rem; border-radius: 12px; text-align: center; color: #94a3b8;">
                No hay socios archivados actualmente.
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="tabla-usuarios" style="width: 100%; border-collapse: collapse; background: rgba(0, 18, 51, 0.6); border-radius: 12px; overflow: hidden;">
                    <thead>
                        <tr style="background: #001233; border-bottom: 2px solid #f3b229; text-align: left;">
                            <th style="padding: 1rem;">ID</th>
                            <th style="padding: 1rem;">NOMBRE</th>
                            <th style="padding: 1rem;">APELLIDO</th>
                            <th style="padding: 1rem;">DNI</th>
                            <th style="padding: 1rem;">CORREO</th>
                            <th style="padding: 1rem;">FECHA DE BAJA</th>
                            <th style="padding: 1rem; text-align: center;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivados as $u): ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <td style="padding: 1rem;"><?= $u['id'] ?></td>
                            <td style="padding: 1rem;"><?= escapar($u['nombre']) ?></td>
                            <td style="padding: 1rem;"><?= escapar($u['apellido']) ?></td>
                            <td style="padding: 1rem;"><?= escapar($u['dni']) ?></td>
                            <td style="padding: 1rem;"><?= escapar($u['email']) ?></td>
                            <td style="padding: 1rem; color: #94a3b8;"><?= date('d/m/Y H:i', strtotime($u['archivado_en'])) ?></td>
                            <td style="padding: 1rem; text-align: center;">
                                <form action="reactivar_usuario.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de reactivar a este socio?');">
                                    <input type="hidden" name="csrf_token" value="<?= escapar($tokenCsrf) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" style="background-color: #22c55e; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: bold; cursor: pointer;">Reactivar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer style="text-align: center; padding: 2rem; color: #64748b; font-size: 0.85rem; margin-top: 4rem;">
        &copy; 2026 Club Atlético Boca Juniors - Todos los derechos reservados.
    </footer>
</body>
</html>