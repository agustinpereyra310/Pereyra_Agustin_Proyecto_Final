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
    <title>Inicio - Club Atlético Boca Juniors</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #001233;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-bottom: 3rem;
        }
        /* Barra superior */
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
        
        /* Contenedor Principal ancho */
        .main-container {
            max-width: 1000px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
        }
        .welcome-title {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 0.4rem;
            color: #ffffff;
        }
        .welcome-subtitle {
            color: #94a3b8;
            font-size: 1.05rem;
            margin-bottom: 2.5rem;
        }

        /* Banner principal ancho */
        .hero-banner {
            background: linear-gradient(135deg, #003087 0%, #001f5b 100%);
            border-radius: 14px;
            padding: 2.5rem 3rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            border: 1px solid rgba(243, 178, 41, 0.4);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .hero-banner h2 {
            font-size: 2rem;
            margin: 0;
            color: #f3b229;
        }
        .hero-banner p {
            color: #e2e8f0;
            margin: 0;
            max-width: 800px;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        .btn-banner {
            display: inline-block;
            background-color: #f3b229;
            color: #001f5b;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.95rem;
            text-decoration: none;
            width: fit-content;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-banner:hover {
            background-color: #dca020;
            transform: translateY(-2px);
        }

        /* Contenedor de cajas en línea horizontal */
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
            transition: transform 0.2s, border-color 0.2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            padding: 1.5rem 2rem;
            gap: 2rem;
        }
        .card-item-wide:hover {
            transform: translateX(4px);
            border-color: #f3b229;
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
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR -->
    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="panel.php" style="color: #f3b229;">Inicio</a>
            <a href="partidos.php">Partidos</a>
            <a href="pagos.php">Pagos</a>
            <a href="beneficios.php">Beneficios</a>
            <?php if (($user['rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="color: #f3b229;">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <h1 class="welcome-title">¡Hola, <?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p class="welcome-subtitle">Encontrá los próximos partidos, beneficios y gestioná tu cuenta de socio.</p>

        <!-- BANNER PRINCIPAL ANCHO -->
        <div class="hero-banner">
            <h2>La pasión se vive en vivo</h2>
            <p>Accedé a los próximos encuentros, canjes de entradas y habilitaciones exclusivas para socios en La Bombonera.</p>
            <a href="partidos.php" class="btn-banner">Ver próximos partidos</a>
        </div>

        <h2 style="font-size: 1.4rem; margin-bottom: 1.2rem; color: #f3b229;">Accesos Principales</h2>

        <!-- LISTA DE CAJAS ANCHAS EN LÍNEA -->
        <div class="section-list">
            <a href="partidos.php" class="card-item-wide">
                <div class="card-icon-box">⚽</div>
                <div class="card-info">
                    <h3>Próximos Partidos</h3>
                    <p>Consultá la disponibilidad de ubicaciones, abonos y el estado de habilitación para el próximo encuentro.</p>
                </div>
            </a>

            <a href="pagos.php" class="card-item-wide">
                <div class="card-icon-box">💳</div>
                <div class="card-info">
                    <h3>Cuota Social y Pagos</h3>
                    <p>Revisá el estado de tus cuotas mensuales, el débito automático asociado y el historial completo de transacciones.</p>
                </div>
            </a>

            <a href="beneficios.php" class="card-item-wide">
                <div class="card-icon-box">🎁</div>
                <div class="card-info">
                    <h3>Beneficios Boca</h3>
                    <p>Accedé a descuentos imperdibles, sorteos oficiales y promociones exclusivas en la tienda y servicios del club.</p>
                </div>
            </a>
        </div>
    </main>

</body>
</html>