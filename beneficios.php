<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexion.php';

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficios Boca - Club Atlético Boca Juniors</title>
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
        .section-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .card-item-wide {
            background-color: #001f5b;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            padding: 1.5rem 2rem;
            gap: 2rem;
        }
        .card-icon-box {
            min-width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #002266, #0044cc);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        .card-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            flex-grow: 1;
        }
        .card-info h3 {
            margin: 0;
            font-size: 1.25rem;
            color: #ffffff;
        }
        .card-info p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        .btn-beneficio {
            background-color: #f3b229;
            color: #001f5b;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-beneficio:hover { background-color: #dca020; }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR -->
    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="panel.php">Inicio</a>
            <a href="partidos.php">Partidos</a>
            <a href="pagos.php">Pagos</a>
            <a href="beneficios.php" style="color: #f3b229;">Beneficios</a>
            <?php if (($user['rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="color: #f3b229;">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <h1 style="font-size: 2.2rem; margin-bottom: 0.4rem; color: #ffffff;">Beneficios Exclusivos</h1>
        <p style="color: #94a3b8; font-size: 1.05rem; margin-bottom: 2.5rem;">Aprovechá descuentos y promociones únicas por ser socio.</p>

        <div class="section-list">
            <div class="card-item-wide">
                <div class="card-icon-box">👕</div>
                <div class="card-info">
                    <h3>Tienda Oficial (Boca Shop)</h3>
                    <p>15% de descuento acumulable en indumentaria oficial y productos retro presentando tu carnet.</p>
                </div>
                <button class="btn-beneficio" onclick="alert('Código de descuento copiado: BOCA2026');">Canjear</button>
            </div>

            <div class="card-item-wide">
                <div class="card-icon-box">🍔</div>
                <div class="card-info">
                    <h3>Gastronomía y Combos</h3>
                    <p>Descuentos especiales en locales adheridos en inmediaciones de Brandsen 805 los días de partido.</p>
                </div>
                <button class="btn-beneficio" onclick="alert('Viendo locales adheridos...');">Ver locales</button>
            </div>

            <div class="card-item-wide">
                <div class="card-icon-box">🎟️</div>
                <div class="card-info">
                    <h3>Sorteos y Experiencias</h3>
                    <p>Participá por visitas guiadas al Museo de la Pasión Boquense y plateas preferenciales.</p>
                </div>
                <button class="btn-beneficio" onclick="alert('¡Ya estás participando en el último sorteo!');">Participar</button>
            </div>
        </div>
    </main>

</body>
</html>