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

// Generamos un número de socio formateado a partir del ID del usuario
$numero_socio = str_pad((437000 + (int)$user['id']), 6, '0', STR_PAD_LEFT);
$numero_socio_formateado = substr($numero_socio, 0, 3) . '.' . substr($numero_socio, 3);
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
            display: block;
            padding-bottom: 3rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
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
        
        /* Contenedor Principal Amplio */
        .main-container {
            max-width: 1250px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .welcome-title {
            font-size: 2.3rem;
            font-weight: bold;
            margin-bottom: 0.4rem;
            color: #ffffff;
            text-transform: uppercase;
        }
        .welcome-subtitle {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* TARJETA CARNET DIGITAL (ESTILO APP OFICIAL) */
        .carnet-card {
            background: linear-gradient(135deg, #001f5b 0%, #001338 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(243, 178, 41, 0.35);
            margin-bottom: 2.5rem;
            position: relative;
        }
        .carnet-top {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 2.5rem 3rem;
            position: relative;
        }
        .carnet-watermark {
            position: absolute;
            right: 2.5rem;
            top: 50%;
            transform: translateY(-50%) rotate(-8deg);
            font-size: 5.5rem;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.04);
            pointer-events: none;
            user-select: none;
            letter-spacing: 4px;
        }
        .carnet-foto-pendiente {
            width: 115px;
            height: 140px;
            background-color: rgba(0, 18, 51, 0.85);
            border: 2px dashed rgba(243, 178, 41, 0.6);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0.75rem;
            flex-shrink: 0;
            gap: 0.4rem;
        }
        .carnet-foto-pendiente .icono-camara {
            font-size: 1.8rem;
            opacity: 0.85;
        }
        .carnet-foto-pendiente span {
            font-size: 0.72rem;
            font-weight: 600;
            color: #cbd5e1;
            line-height: 1.25;
        }
        .carnet-foto-pendiente small {
            font-size: 0.65rem;
            color: #f3b229;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .carnet-datos {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            z-index: 1;
        }
        .carnet-nombre {
            font-size: 1.9rem;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .carnet-numero {
            font-size: 1.2rem;
            color: #cbd5e1;
            font-family: monospace;
            letter-spacing: 1px;
            margin-top: 0.3rem;
        }
        .carnet-aviso-foto {
            display: inline-block;
            margin-top: 0.6rem;
            background-color: rgba(243, 178, 41, 0.15);
            border: 1px solid rgba(243, 178, 41, 0.4);
            color: #f3b229;
            font-size: 0.8rem;
            padding: 0.3rem 0.75rem;
            border-radius: 6px;
            width: fit-content;
        }
        .carnet-bottom {
            background-color: #f3b229;
            color: #001f5b;
            padding: 1.2rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .carnet-dato-footer {
            display: flex;
            flex-direction: column;
        }
        .carnet-dato-footer span {
            font-size: 0.78rem;
            font-weight: 600;
            opacity: 0.85;
        }
        .carnet-dato-footer strong {
            font-size: 1.05rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        /* BANNER GRANDE DE JUGADORES FESTEJANDO (ABAJO DEL CARNET) */
        .panel-banner-grande {
            position: relative;
            width: 100%;
            height: 280px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2.5rem;
            border: 2px solid #f3b229;
            box-shadow: 0 10px 35px rgba(0,0,0,0.5);
        }
        .panel-banner-grande img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .panel-banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(0,31,91,0.92) 0%, rgba(0,18,51,0.4) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 3rem;
        }
        .panel-banner-overlay h3 {
            color: #f3b229;
            font-size: 2rem;
            margin: 0 0 0.4rem 0;
            text-transform: uppercase;
        }
        .panel-banner-overlay p {
            color: #e2e8f0;
            font-size: 1.05rem;
            margin: 0;
            max-width: 550px;
        }

        /* Títulos de sección */
        .section-heading {
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            color: #f3b229;
            border: none;
            padding: 0;
        }

        /* LAS 4 CAJAS EN LÍNEA RECTA HORIZONTAL */
        .section-list {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            justify-content: space-between;
            overflow-x: auto;
            padding-bottom: 1rem;
        }
        .card-item-wide {
            background-color: #001f5b;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.8rem;
            flex: 1;
            min-width: 240px;
        }
        .card-item-wide:hover {
            transform: translateY(-5px);
            border-color: #f3b229;
            box-shadow: 0 10px 25px rgba(243, 178, 41, 0.15);
        }
        .card-icon-box {
            width: 55px;
            height: 55px;
            background: linear-gradient(45deg, #002266, #0044cc);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }
        .card-info {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .card-info h3 {
            margin: 0;
            font-size: 1.15rem;
            color: #ffffff;
        }
        .card-info p {
            margin: 0 0 1.5rem 0;
            color: #94a3b8;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        .link-accion {
            color: #f3b229;
            font-weight: bold;
            font-size: 0.9rem;
        }

        @media (max-width: 900px) {
            .section-list {
                flex-direction: column;
            }
            .carnet-top {
                flex-direction: column;
                text-align: center;
                padding: 1.8rem 1.5rem;
            }
            .carnet-datos {
                align-items: center;
            }
            .carnet-watermark {
                display: none;
            }
            .panel-banner-grande {
                height: 200px;
            }
            .panel-banner-overlay {
                padding: 0 1.5rem;
            }
        }
    </style>
</head>
<body>

    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="panel.php" style="color: #f3b229;">Inicio</a>
            <a href="partidos.php">Partidos</a>
            <a href="pagos.php">Pagos</a>
            <a href="beneficios.php">Beneficios</a>
            <a href="categoria.php">Mi Categoría</a>
            <?php if (($user['rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="color: #f3b229;">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        
        <!-- 1. SALUDO INICIAL ARRIBA -->
        <h1 class="welcome-title">¡HOLA, <?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p class="welcome-subtitle">Encontrá los próximos partidos, beneficios y gestioná tu cuenta de socio.</p>

        <!-- 2. CREDENCIAL DE SOCIO (CARNET) -->
        <section class="carnet-card">
            <div class="carnet-top">
                <div class="carnet-watermark">CABJ</div>
                
                <div class="carnet-foto-pendiente">
                    <div class="icono-camara">📷</div>
                    <span>Todavía no sacaste tu foto</span>
                    <small>Pendiente</small>
                </div>

                <div class="carnet-datos">
                    <h2 class="carnet-nombre"><?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="carnet-numero">N° <?= $numero_socio_formateado ?></div>
                    <div class="carnet-aviso-foto">⚠️ Foto de carnet pendiente de registro</div>
                </div>
            </div>

            <div class="carnet-bottom">
                <div class="carnet-dato-footer">
                    <span>Socio/a</span>
                    <strong>ADHERENTE</strong>
                </div>
                <div class="carnet-dato-footer">
                    <span>Estado</span>
                    <strong>ACTIVO</strong>
                </div>
                <div class="carnet-dato-footer">
                    <span>Desde</span>
                    <strong>12/2025</strong>
                </div>
            </div>
        </section>

        <!-- 3. FOTO DE LOS JUGADORES FESTEJANDO ABAJO DEL CARNET -->
        <div class="panel-banner-grande">
            <img src="img/jugadores_festejo.jpg" alt="Jugadores de Boca Juniors festejando">
            <div class="panel-banner-overlay">
                <h3>Sentimiento Xeneize</h3>
                <p>Pasión sin límites. Viví cada partido y accedé a todos los beneficios exclusivos del club.</p>
            </div>
        </div>

        <h2 class="section-heading">Accesos Principales</h2>

        <!-- 4. LAS 4 CAJAS EN LÍNEA RECTA HORIZONTAL -->
        <div class="section-list">
            <a href="partidos.php" class="card-item-wide">
                <div>
                    <div class="card-icon-box">⚽</div>
                    <div class="card-info">
                        <h3>Próximos Partidos</h3>
                        <p>Consultá la disponibilidad de ubicaciones y abonos.</p>
                    </div>
                </div>
                <span class="link-accion">Ir a Partidos &rarr;</span>
            </a>

            <a href="pagos.php" class="card-item-wide">
                <div>
                    <div class="card-icon-box">💳</div>
                    <div class="card-info">
                        <h3>Cuota Social y Pagos</h3>
                        <p>Revisá el estado de tus cuotas e historial.</p>
                    </div>
                </div>
                <span class="link-accion">Ir a Pagos &rarr;</span>
            </a>

            <a href="beneficios.php" class="card-item-wide">
                <div>
                    <div class="card-icon-box">🎁</div>
                    <div class="card-info">
                        <h3>Beneficios Boca</h3>
                        <p>Generá tus cupones de descuento exclusivos.</p>
                    </div>
                </div>
                <span class="link-accion">Ir a Beneficios &rarr;</span>
            </a>

            <a href="categoria.php" class="card-item-wide">
                <div>
                    <div class="card-icon-box">⭐</div>
                    <div class="card-info">
                        <h3>Mi Categoría y Estado</h3>
                        <p>Revisá tu antigüedad y membresía societaria.</p>
                    </div>
                </div>
                <span class="link-accion">Ir a Categoría &rarr;</span>
            </a>
        </div>
    </main>

</body>
</html>