<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexion.php';

// Procesar la solicitud de entrada
$mensaje = '';
$tipoMensaje = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partido_id'], $_POST['ubicacion'])) {
    $partidoId = (int) $_POST['partido_id'];
    $ubicacion = trim($_POST['ubicacion']);
    $usuarioId = $_SESSION['usuario_id'];

    $stmtCheck = $conexion->prepare("SELECT id FROM entradas_socios WHERE usuario_id = :u_id AND partido_id = :p_id");
    $stmtCheck->execute(['u_id' => $usuarioId, 'p_id' => $partidoId]);
    
    if ($stmtCheck->fetch()) {
        $mensaje = '¡Ya tenés una entrada reservada para este partido!';
        $tipoMensaje = 'aviso';
    } else {
        $stmtInsert = $conexion->prepare("INSERT INTO entradas_socios (usuario_id, partido_id, ubicacion) VALUES (:u_id, :p_id, :ubicacion)");
        $stmtInsert->execute(['u_id' => $usuarioId, 'p_id' => $partidoId, 'ubicacion' => $ubicacion]);
        $mensaje = '¡Entrada reservada con éxito! Ya podés verla en "Mis Autorizaciones / Abonos".';
    }
}

// Datos del usuario
$stmtUser = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmtUser->execute(['id' => $_SESSION['usuario_id']]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Partidos habilitados
$stmtPartidos = $conexion->query("SELECT * FROM partidos WHERE estado = 'habilitado' ORDER BY fecha_partido ASC");
$partidos = $stmtPartidos->fetchAll(PDO::FETCH_ASSOC);

// Entradas ya sacadas por el usuario
$stmtMisEntradas = $conexion->prepare("SELECT p.rival, p.competencia, p.fecha_partido, e.ubicacion FROM entradas_socios e JOIN partidos p ON e.partido_id = p.id WHERE e.usuario_id = :id ORDER BY e.fecha_reserva DESC");
$stmtMisEntradas->execute(['id' => $_SESSION['usuario_id']]);
$misEntradas = $stmtMisEntradas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partidos y Entradas - Club Atlético Boca Juniors</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #001233;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-bottom: 4rem;
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

        /* Contenedor mucho más ancho y espacioso */
        .main-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        /* Cajas grandes con mucha presencia */
        .card-seccion-grande {
            background: #001f5b;
            border-radius: 16px;
            padding: 3rem 3.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-left: 8px solid #003087;
        }
        .card-seccion-grande h3 {
            margin-top: 0;
            color: #ffffff;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        /* Contenedor y estilo para la foto de La Bombonera */
        .estadio-imagen-container {
            width: 100%;
            max-height: 300px;
            overflow: hidden;
            border-radius: 12px;
            margin: 1.5rem 0 2rem 0;
            border: 1px solid rgba(243, 178, 41, 0.4);
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
        }
        .img-bombonera {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }
        .img-bombonera:hover {
            transform: scale(1.02);
        }

        .texto-destacado {
            text-align: center;
            color: #e2e8f0;
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-boca-grande {
            background-color: #f3b229;
            color: #001f5b;
            padding: 1rem 2.2rem;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-boca-grande:hover {
            background-color: #dca020;
            transform: translateY(-2px);
        }

        /* Panel desplegable para sacar la entrada */
        .panel-sacar-entrada {
            display: none;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 2px solid rgba(243, 178, 41, 0.3);
        }
        .fila-partido {
            background: #001233;
            padding: 2rem;
            border-radius: 12px;
            border: 1px solid #f3b229;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        .select-ubicacion-grande {
            padding: 0.9rem 1.2rem;
            border-radius: 8px;
            border: none;
            background: #ffffff;
            color: #001f5b;
            font-weight: bold;
            font-size: 1rem;
            min-width: 240px;
        }

        .alert-ok { background: #dcfce7; color: #166534; padding: 1.2rem; border-radius: 10px; margin-bottom: 2rem; border-left: 6px solid #22c55e; font-weight: bold; font-size: 1.1rem; }
        .alert-aviso { background: #fef3c7; color: #92400e; padding: 1.2rem; border-radius: 10px; margin-bottom: 2rem; border-left: 6px solid #f59e0b; font-weight: bold; font-size: 1.1rem; }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR UNIFICADA -->
    <header class="top-nav">
        <div class="logo-club">CABJ | Portal de Socios</div>
        <div class="top-nav-links">
            <a href="panel.php">Inicio</a>
            <a href="partidos.php" style="color: #f3b229;">Partidos</a>
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
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem; color: #ffffff;">Próximos Partidos</h1>
        <p style="color: #94a3b8; font-size: 1.15rem; margin-bottom: 2.5rem;">Gestioná tus ubicaciones y el acceso a La Bombonera.</p>

        <?php if ($mensaje !== ''): ?>
            <div class="<?= $tipoMensaje === 'ok' ? 'alert-ok' : 'alert-aviso' ?>">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- CAJA 1: VENÍ A ALENTAR -->
        <div class="card-seccion-grande">
            <h3>Vení a alentar</h3>
            
            <!-- IMAGEN DE LA BOMBONERA -->
            <div class="estadio-imagen-container">
                <img src="img/bombonera.jpg" alt="La Bombonera - Club Atlético Boca Juniors" class="img-bombonera">
            </div>
            
            <?php if (!empty($partidos)): ?>
                <p class="texto-destacado">
                    <strong style="color: #f3b229; font-size: 1.35rem;">¡Ya hay un partido habilitado para sacar entrada!</strong><br>
                    El filtro y la reserva de lugares en La Bombonera se encuentran activos.
                </p>
                <div style="text-align: center;">
                    <button type="button" class="btn-boca-grande" onclick="document.getElementById('caja-entradas').style.display='block'; this.style.display='none';">
                        Buscar y sacar entradas 🎟️
                    </button>
                </div>

                <!-- PANEL QUE SE ABRE AL TOCAR EL BOTÓN -->
                <div id="caja-entradas" class="panel-sacar-entrada">
                    <h4 style="color: #f3b229; font-size: 1.3rem; margin-top: 0; margin-bottom: 1.5rem;">Seleccioná tu partido y ubicación:</h4>
                    <?php foreach ($partidos as $p): ?>
                        <div class="fila-partido">
                            <div>
                                <span style="background: rgba(243,178,41,0.2); color: #f3b229; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: bold;">
                                    <?= htmlspecialchars($p['competencia'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <h4 style="color: #ffffff; font-size: 1.5rem; margin: 0.6rem 0;">Boca Juniors vs <?= htmlspecialchars($p['rival'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p style="color: #94a3b8; font-size: 1rem; margin: 0;">📅 <?= date('d/m/Y - H:i', strtotime($p['fecha_partido'])) ?> hs | 📍 <?= htmlspecialchars($p['estadio'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>

                            <form method="POST" action="partidos.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                                <input type="hidden" name="partido_id" value="<?= $p['id'] ?>">
                                <select name="ubicacion" class="select-ubicacion-grande" required>
                                    <option value="">Elegir Ubicación...</option>
                                    <option value="Popular General">Popular (Socio)</option>
                                    <option value="Platea Baja Sector L">Platea Baja Sector L</option>
                                    <option value="Platea Media / Alta">Platea Media / Alta</option>
                                </select>
                                <button type="submit" class="btn-boca-grande">Confirmar Entrada</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <p class="texto-destacado">
                    Aún no hay partidos habilitados.<br>Cuando se habiliten, podrás obtener tu lugar en La Bombonera.
                </p>
            <?php endif; ?>
        </div>

        <!-- CAJA 2: MIS AUTORIZACIONES / ABONOS -->
        <div class="card-seccion-grande" style="border-left-color: #f3b229;">
            <h3>Mis Autorizaciones / Abonos</h3>
            <p style="color: #e2e8f0; font-size: 1.1rem; margin: 0.6rem 0;"><strong>Titular:</strong> <?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
            <p style="color: #e2e8f0; font-size: 1.1rem; margin: 0.6rem 0;"><strong>Categoría:</strong> Socio Adherente</p>
            <p style="margin: 0.6rem 0 1.5rem 0; color: #22c55e; font-size: 1.1rem; font-weight: bold;">✔ Estado: Cuenta habilitada para ranking y filtros.</p>

            <?php if (!empty($misEntradas)): ?>
                <div style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 1.5rem; margin-top: 1.5rem;">
                    <h4 style="color: #f3b229; font-size: 1.2rem; margin-top: 0;">Entradas Reservadas:</h4>
                    <?php foreach ($misEntradas as $entrada): ?>
                        <div style="background: #001233; padding: 1.2rem 1.5rem; border-radius: 10px; border-left: 5px solid #22c55e; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                            <div>
                                <strong style="color: #ffffff; font-size: 1.15rem;">Boca Juniors vs <?= htmlspecialchars($entrada['rival'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <p style="color: #94a3b8; margin: 0.3rem 0 0 0; font-size: 1rem;">Ubicación: <strong style="color: #f3b229;"><?= htmlspecialchars($entrada['ubicacion'], ENT_QUOTES, 'UTF-8') ?></strong> | <?= date('d/m/Y H:i', strtotime($entrada['fecha_partido'])) ?> hs</p>
                            </div>
                            <span style="background: #dcfce7; color: #166534; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: bold;">CONFIRMADA ✔</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- CAJA 3: ASISTENCIA A PARTIDOS -->
        <div class="card-seccion-grande">
            <h3>Asistencia a partidos</h3>
            <p style="color: #94a3b8; font-size: 1.1rem; margin-bottom: 1.5rem;">Revisá a qué partidos viniste a alentar a La Bombonera por el torneo local.</p>
            <button class="btn-boca-grande" onclick="alert('Historial de asistencia al día (100% de efectividad).');">Ver mi asistencia</button>
        </div>
    </main>

</body>
</html>