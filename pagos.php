<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexion.php';

$stmt =$conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' =>$_SESSION['usuario_id']]);
$user =$stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestioná tu Cuenta - Club Atlético Boca Juniors</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #001233;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-bottom: 3rem;
            display: block !important; /* Anula centrado vertical global */
        }
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

        .main-container {
            max-width: 1000px !important;
            margin: 2.5rem auto !important; /* Forzamos que quede arriba y centrado horizontalmente */
            padding: 0 1.5rem !important;
        }
        .card-seccion {
            background: #001f5b;
            border-radius: 14px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-left: 6px solid #003087;
        }
        .badge-estado {
            background-color: #dcfce7;
            color: #166534;
            padding: 0.35rem 0.9rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            float: right;
        }
        .btn-boca-custom {
            background-color: #f3b229;
            color: #001f5b;
            padding: 0.7rem 1.4rem;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn-boca-custom:hover { background-color: #dca020; }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR -->
    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="panel.php">Inicio</a>
            <a href="partidos.php">Partidos</a>
            <a href="pagos.php" style="color: #f3b229;">Pagos</a>
            <a href="beneficios.php">Beneficios</a>
            <?php if (($user['rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="color: #f3b229;">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <h1 style="font-size: 2.2rem; margin-bottom: 0.4rem; color: #ffffff;">Gestioná tu Cuenta</h1>
        <p style="color: #94a3b8; font-size: 1.05rem; margin-bottom: 2.5rem;">Cuotas sociales, aranceles y planes de pago.</p>
            
        <div class="card-seccion">
            <span class="badge-estado">ESTÁS AL DÍA ✔</span>
            <h3 style="color: #ffffff; margin-top:0; font-size: 1.3rem;">Estado de Cuota Social</h3>
            <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.5;">
                <strong>Socio:</strong> <?= htmlspecialchars($user['nombre'] . ' ' .$user['apellido'], ENT_QUOTES, 'UTF-8') ?><br>
                Última cuota paga: <strong>Noviembre 2026 (Sin deuda)</strong>
            </p>

            <button class="btn-boca-custom" onclick="alert('Redirigiendo a pasarela de pagos segura...');">Realizar Pagos</button>
            <button class="btn-boca-custom" style="background-color: #475569; color: #ffffff; margin-left: 0.5rem;" onclick="alert('Débito automático activo.');">Dar de baja débito</button>
        </div>

        <div class="card-seccion" style="border-left-color: #f3b229;">
            <h3 style="color: #ffffff; font-size: 1.3rem;">Mis pagos en curso</h3>
            <p style="color: #94a3b8; font-size: 0.95rem; margin: 0;">No hay pagos pendientes en este momento. Tu cuota mensual se procesa automáticamente.</p>
        </div>
    </main>

</body>
</html>