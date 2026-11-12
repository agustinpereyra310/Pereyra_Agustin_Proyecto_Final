<?php
session_start();
// Si el usuario ya inició sesión, lo redirigimos directamente a su panel personal
if (isset($_SESSION['usuario_id'])) {
    header("Location: panel.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Atlético Boca Juniors - Portal de Socios</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #001233;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        /* Barra de navegación pública */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.4rem 3rem;
            background-color: #001f5b;
            border-bottom: 2px solid #f3b229;
        }
        .top-nav .logo-club {
            font-weight: bold;
            color: #f3b229;
            font-size: 1.3rem;
        }
        .top-nav-links {
            display: flex;
            gap: 1.5rem;
        }
        .btn-nav-outline {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-nav-outline:hover {
            border-color: #f3b229;
            color: #f3b229;
        }
        .btn-nav-solid {
            background-color: #f3b229;
            color: #001f5b;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: bold;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .btn-nav-solid:hover {
            background-color: #dca020;
        }

        /* Contenido principal de bienvenida */
        .hero-section {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            background: linear-gradient(rgba(0, 18, 51, 0.8), rgba(0, 31, 91, 0.9)), url('https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            text-align: center;
        }
        .hero-content {
            max-width: 800px;
            background: rgba(0, 31, 91, 0.85);
            padding: 3.5rem 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border: 1px solid rgba(243, 178, 41, 0.3);
        }
        .hero-content h1 {
            font-size: 2.8rem;
            color: #f3b229;
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .hero-content p {
            color: #e2e8f0;
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .btn-grande {
            padding: 1rem 2.5rem;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            text-decoration: none;
            transition: transform 0.2s, background 0.2s;
        }
        .btn-grande-amarillo {
            background-color: #f3b229;
            color: #001f5b;
        }
        .btn-grande-amarillo:hover {
            background-color: #dca020;
            transform: translateY(-2px);
        }
        .btn-grande-azul {
            background-color: #003087;
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-grande-azul:hover {
            background-color: #00225f;
            transform: translateY(-2px);
            border-color: #f3b229;
        }

        footer {
            text-align: center;
            padding: 1.5rem;
            background-color: #000c1e;
            color: #94a3b8;
            font-size: 0.9rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR PÚBLICA -->
    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="login.php" class="btn-nav-outline">Iniciar Sesión</a>
            <a href="registro.php" class="btn-nav-solid">Registrarse</a>
        </div>
    </header>

    <!-- SECCIÓN PRINCIPAL DE PORTADA -->
    <main class="hero-section">
        <div class="hero-content">
            <h1>Club Atlético Boca Juniors</h1>
            <p>Bienvenido al portal oficial de gestión para socios. Accedé a tu cuenta, consultá tu cuota social, gestioná tus ubicaciones y viví la pasión de La Bombonera.</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-grande btn-grande-amarillo">Iniciar Sesión</a>
                <a href="registro.php" class="btn-grande btn-grande-azul">Crear una Cuenta</a>
            </div>
        </div>
    </main>

    <!-- PIE DE PÁGINA -->
    <footer>
        <p>&copy; 2026 Club Atlético Boca Juniors - Sistema de Gestión de Socios</p>
    </footer>

</body>
</html>