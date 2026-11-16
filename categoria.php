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

// Número de socio simulado a partir del ID
$numero_socio = str_pad((437000 + (int)$user['id']), 6, '0', STR_PAD_LEFT);
$numero_socio_formateado = substr($numero_socio, 0, 3) . '.' . substr($numero_socio, 3);

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_pase'])) {
    $mensaje = "¡Solicitud de cambio a Socio Activo registrada correctamente! Tu trámite se encuentra en lista de espera por antigüedad.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Categoría - Club Atlético Boca Juniors</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #001233;
            color: #ffffff;
            display: block;
            padding-bottom: 3rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
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
            max-width: 1000px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
        }
        .page-title {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 0.4rem;
            color: #ffffff;
            text-transform: uppercase;
        }
        .page-subtitle {
            color: #94a3b8;
            font-size: 1.05rem;
            margin-bottom: 2.5rem;
        }

        /* BANNER SUPERIOR DE IDENTIDAD */
        .categoria-banner {
            background: linear-gradient(135deg, #001f5b 0%, #000c1e 100%);
            border: 2px solid #f3b229;
            border-radius: 16px;
            padding: 2.2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .banner-info h2 {
            color: #ffffff;
            font-size: 1.6rem;
            margin: 0 0 0.4rem 0;
            text-transform: uppercase;
        }
        .banner-info p {
            color: #94a3b8;
            margin: 0;
            font-size: 1rem;
        }
        .badge-adherente {
            background-color: #f3b229;
            color: #001f5b;
            font-weight: 900;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ALERTA */
        .alert-solicitud {
            background-color: rgba(34, 197, 94, 0.15);
            border: 2px solid #22c55e;
            color: #4ade80;
            padding: 1rem 1.2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 600;
            text-align: center;
        }

        /* DISEÑO DE DOS COLUMNAS PRINCIPALES */
        .grid-dos-columnas {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 2rem;
        }
        @media (max-width: 850px) {
            .grid-dos-columnas {
                grid-template-columns: 1fr;
            }
        }

        .panel-principal {
            background-color: #001f5b;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 2.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .panel-principal h3 {
            color: #f3b229;
            font-size: 1.4rem;
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .panel-principal p {
            color: #94a3b8;
            font-size: 0.98rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        /* ESTADÍSTICAS / DATOS LATERALES */
        .panel-lateral {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .stat-box {
            background-color: #001f5b;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 1.5rem 2rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .stat-box h4 {
            color: #f3b229;
            font-size: 1.05rem;
            margin: 0 0 0.5rem 0;
        }
        .stat-box p {
            color: #e2e8f0;
            font-size: 1.1rem;
            margin: 0;
            font-weight: 600;
        }

        /* BARRA DE PROGRESO */
        .progreso-container {
            background-color: #001233;
            border-radius: 10px;
            height: 14px;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(243, 178, 41, 0.3);
            margin: 1.2rem 0 0.6rem 0;
        }
        .progreso-fill {
            width: 45%;
            height: 100%;
            background: linear-gradient(90deg, #dca020, #f3b229);
            border-radius: 10px;
        }
        .progreso-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        /* BOTÓN ESTILO BOCA */
        .btn-boca {
            background-color: #f3b229;
            color: #001f5b;
            border: none;
            width: 100%;
            padding: 0.9rem;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-boca:hover {
            background-color: #dca020;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="panel.php">Inicio</a>
            <a href="partidos.php">Partidos</a>
            <a href="pagos.php">Pagos</a>
            <a href="beneficios.php">Beneficios</a>
            <a href="categoria.php" style="color: #f3b229;">Mi Categoría</a>
            <?php if (($user['rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="color: #f3b229;">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Mi Categoría</h1>
        <p class="page-subtitle">Panel de control de antigüedad, jerarquía societaria y estado en el padrón oficial.</p>

        <!-- BANNER DE IDENTIDAD -->
        <div class="categoria-banner">
            <div class="banner-info">
                <h2><?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p>N° de Socio: <strong style="color: #f3b229; font-family: monospace; font-size: 1.1rem;"><?= $numero_socio_formateado ?></strong> &nbsp;|&nbsp; Alta: <strong>12/2025</strong></p>
            </div>
            <div>
                <div class="badge-adherente">⭐ Socio Adherente</div>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-solicitud"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- DISTRIBUCIÓN EN DOS COLUMNAS -->
        <div class="grid-dos-columnas">
            <!-- COLUMNA IZQUIERDA: ANTIGÜEDAD Y ASCENSO -->
            <div class="panel-principal">
                <div>
                    <h3>📈 Ranking y Trámite de Ascenso</h3>
                    <p>Tu posición en el padrón de adherentes avanza de forma automática según los años de antigüedad registrados en el club para acceder al estatus de Socio Activo Pleno.</p>
                    
                    <div class="progreso-info">
                        <span>Progreso hacia Socio Activo</span>
                        <strong style="color: #f3b229;">45% completado</strong>
                    </div>
                    <div class="progreso-container">
                        <div class="progreso-fill"></div>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <form method="POST">
                        <button type="submit" name="solicitar_pase" class="btn-boca">Solicitar Estado de Trámite</button>
                    </form>
                </div>
            </div>

            <!-- COLUMNA DERECHA: ESTADÍSTICAS Y NORMATIVA -->
            <div class="panel-lateral">
                <div class="stat-box">
                    <h4>🛡️ Estado Administrativo</h4>
                    <p style="color: #22c55e;">✔ Habilitado y al día</p>
                </div>

                <div class="stat-box">
                    <h4>🏟️ Participación en Partidos</h4>
                    <p>Habilitado para filtros de ranking</p>
                </div>

                <div class="stat-box">
                    <h4>📋 Tribunal de Disciplina</h4>
                    <p style="color: #cbd5e1; font-weight: normal; font-size: 0.95rem;">Sin sanciones ni apercibimientos vigentes.</p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>