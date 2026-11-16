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

$mensaje = "";
$codigoGenerado = "";

// Si el usuario hace clic en activar un beneficio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activar_beneficio'])) {
    $beneficioNombre = $_POST['beneficio_nombre'];
    $codigoGenerado = "CABJ-" . strtoupper(substr(md5(rand()), 0, 6));
    $mensaje = "¡Cupón generado con éxito para: " . htmlspecialchars($beneficioNombre, ENT_QUOTES, 'UTF-8') . "!";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficios Exclusivos - Club Atlético Boca Juniors</title>
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
        }
        .page-subtitle {
            color: #94a3b8;
            font-size: 1.05rem;
            margin-bottom: 2rem;
        }

        /* BANNER GRANDE DE TIENDA / ROPA */
        .beneficios-banner-grande {
            position: relative;
            width: 100%;
            height: 260px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2.5rem;
            border: 2px solid #f3b229;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .beneficios-banner-grande img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .beneficios-banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(0,31,91,0.9) 0%, rgba(0,18,51,0.6) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 3rem;
        }
        .beneficios-banner-overlay h2 {
            color: #f3b229;
            font-size: 2.2rem;
            margin: 0 0 0.5rem 0;
            text-transform: uppercase;
        }
        .beneficios-banner-overlay p {
            color: #e2e8f0;
            font-size: 1.1rem;
            margin: 0;
            max-width: 550px;
        }

        /* TARJETA DE MEMBRESÍA DIGITAL EXCLUSIVA */
        .credencial-digital {
            background: linear-gradient(135deg, #001f5b 0%, #000c1e 100%);
            border: 2px solid #f3b229;
            border-radius: 16px;
            padding: 2.2rem 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 12px 35px rgba(0,0,0,0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
        }
        .credencial-info h3 {
            color: #f3b229;
            font-size: 1.5rem;
            margin: 0 0 0.4rem 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .credencial-info p {
            color: #cbd5e1;
            font-size: 1rem;
            margin: 0.2rem 0;
        }
        .badge-vip {
            background: rgba(243, 178, 41, 0.2);
            border: 1px solid #f3b229;
            color: #f3b229;
            padding: 0.7rem 1.4rem;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.95rem;
            text-align: center;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        /* ALERTA DE CUPÓN GENERADO */
        .cupon-box {
            background-color: rgba(34, 197, 94, 0.15);
            border: 2px dashed #22c55e;
            color: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .cupon-box h4 {
            color: #4ade80;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .codigo-grande {
            font-size: 2rem;
            font-family: monospace;
            color: #f3b229;
            background: #001233;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
            margin-top: 0.5rem;
            letter-spacing: 2px;
        }

        /* GRILLA DE OFERTAS DESTACADAS */
        .ofertas-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .oferta-card {
            background-color: #001f5b;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        .oferta-card:hover {
            transform: translateY(-5px);
            border-color: #f3b229;
            box-shadow: 0 10px 25px rgba(243, 178, 41, 0.15);
        }
        .oferta-tag {
            background-color: #f3b229;
            color: #001f5b;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            width: fit-content;
            margin-bottom: 1rem;
        }
        .oferta-card h4 {
            color: #ffffff;
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
        }
        .oferta-card p {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }
        .btn-activar {
            background-color: transparent;
            border: 2px solid #f3b229;
            color: #f3b229;
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-activar:hover {
            background-color: #f3b229;
            color: #001f5b;
        }

        @media (max-width: 768px) {
            .credencial-digital {
                flex-direction: column;
                align-items: flex-start;
            }
            .beneficios-banner-grande {
                height: 200px;
            }
            .beneficios-banner-overlay {
                padding: 0 1.5rem;
            }
            .beneficios-banner-overlay h2 {
                font-size: 1.6rem;
            }
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
            <a href="beneficios.php" style="color: #f3b229;">Beneficios</a>
            <a href="categoria.php">Mi Categoría</a>
            <?php if (($user['rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="color: #f3b229;">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Club de Beneficios Xeneize</h1>
        <p class="page-subtitle">Disfrutá de ventajas exclusivas, descuentos y accesos culturales únicos por ser parte de la familia boquense.</p>

        <!-- BANNER GRANDE DE TIENDA Y ROPA -->
        <div class="beneficios-banner-grande">
            <img src="img/tienda_boca.jpg" alt="Boca Shop e Indumentaria Oficial">
            <div class="beneficios-banner-overlay">
                <h2>Boca Shop & Lifestyle</h2>
                <p>Llevá los colores siempre con vos. Beneficios exclusivos en indumentaria oficial y accesorios para socios.</p>
            </div>
        </div>

        <!-- TARJETA DE MEMBRESÍA -->
        <div class="credencial-digital">
            <div class="credencial-info">
                <h3>Credencial Digital Activa</h3>
                <p>Socio: <strong><?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <p style="font-size: 0.9rem; color: #94a3b8;">Presentá este beneficio junto a tu carnet en los locales adheridos.</p>
            </div>
            <div>
                <div class="badge-vip">⭐ SOCIO ADHERENTE</div>
            </div>
        </div>

        <!-- SI SE ACTIVÓ UN BENEFICIO, APARECE ESTE CARTEL DESTACADO -->
        <?php if (!empty($mensaje)): ?>
            <div class="cupon-box">
                <h4><?= $mensaje ?></h4>
                <p>Mostrá este código al momento de realizar tu compra o canje:</p>
                <div class="codigo-grande"><?= $codigoGenerado ?></div>
            </div>
        <?php endif; ?>

        <!-- GRILLA DE OFERTAS VISUALES -->
        <div class="ofertas-container">
            <div class="oferta-card">
                <div>
                    <span class="oferta-tag">Indumentaria</span>
                    <h4>20% OFF en Boca Shop</h4>
                    <p>Descuento exclusivo en camisetas oficiales, camperas de entrenamiento y toda la indumentaria de la temporada actual.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="beneficio_nombre" value="20% OFF en Boca Shop">
                    <button type="submit" name="activar_beneficio" class="btn-activar">Generar Cupón</button>
                </form>
            </div>

            <div class="oferta-card">
                <div>
                    <span class="oferta-tag">Cultura y Museo</span>
                    <h4>50% OFF - Museo de la Pasión Boquense</h4>
                    <p>Entrada con mitad de precio para recorrer la historia del club, la sala de trofeos y el tour oficial por las instalaciones de La Bombonera.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="beneficio_nombre" value="50% OFF Museo de la Pasion Boquense">
                    <button type="submit" name="activar_beneficio" class="btn-activar">Generar Cupón</button>
                </form>
            </div>

            <div class="oferta-card">
                <div>
                    <span class="oferta-tag">Gastronomía</span>
                    <h4>Beneficios Zona Brandsen</h4>
                    <p>Promociones y menús especiales con descuentos en bares y restaurantes adheridos en los alrededores de La Bombonera.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="beneficio_nombre" value="Gastronomia Zona Brandsen">
                    <button type="submit" name="activar_beneficio" class="btn-activar">Generar Cupón</button>
                </form>
            </div>
        </div>
    </main>

</body>
</html>