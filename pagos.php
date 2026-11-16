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

// Inicializar el historial de pagos en la sesión si todavía no existe
if (!isset($_SESSION['pagos_usuario'])) {
    $_SESSION['pagos_usuario'] = [
        ['concepto' => 'Cuota Social - Septiembre 2026', 'fecha' => '04/09/2026', 'monto' => '$18.500', 'estado' => 'Acreditado'],
        ['concepto' => 'Cuota Social - Agosto 2026', 'fecha' => '03/08/2026', 'monto' => '$18.500', 'estado' => 'Acreditado']
    ];
}

$mensaje = "";

// Si se presiona el botón de pagar, agregamos un nuevo pago al arreglo de la sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar_cuota'])) {
    $nuevoPago = [
        'concepto' => 'Cuota Social - ' . date('F Y'),
        'fecha' => date('d/m/Y'),
        'monto' => '$18.500',
        'estado' => 'Acreditado'
    ];
    
    // Lo sumamos al principio del arreglo para que aparezca primero
    array_unshift($_SESSION['pagos_usuario'], $nuevoPago);
    $mensaje = "¡Pago registrado y procesado con éxito!";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuota Social y Pagos - Club Atlético Boca Juniors</title>
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
            margin-bottom: 2.5rem;
        }

        /* ESTADO DE CUENTA CARD CON ESCUDO/IMAGEN */
        .estado-cuenta {
            background: linear-gradient(135deg, #001f5b 0%, #001233 100%);
            border: 2px solid #f3b229;
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            gap: 2rem;
        }
        .estado-contenido-izquierda {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .escudo-pagos {
            width: 70px;
            height: 70px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        .estado-info h3 {
            color: #f3b229;
            font-size: 1.4rem;
            margin-bottom: 0.4rem;
        }
        .estado-info p {
            color: #cbd5e1;
            font-size: 1rem;
            margin: 0;
        }
        .btn-pagar {
            background-color: #f3b229;
            color: #001f5b;
            border: none;
            padding: 0.8rem 1.6rem;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            white-space: nowrap;
        }
        .btn-pagar:hover {
            background-color: #dca020;
            transform: translateY(-2px);
        }

        /* ALERTA DE ÉXITO */
        .alert-success {
            background-color: rgba(34, 197, 94, 0.15);
            border: 1px solid #22c55e;
            color: #4ade80;
            padding: 1rem 1.2rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        /* HISTORIAL DE PAGOS (TABLA) */
        .historial-section h2 {
            color: #f3b229;
            font-size: 1.4rem;
            margin-bottom: 1.2rem;
            border: none;
            padding: 0;
        }
        .tabla-pagos {
            width: 100%;
            border-collapse: collapse;
            background-color: #001f5b;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .tabla-pagos th, .tabla-pagos td {
            padding: 1rem 1.5rem;
            text-align: left;
        }
        .tabla-pagos th {
            background-color: #00163f;
            color: #f3b229;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tabla-pagos tr:not(:last-child) td {
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .tabla-pagos td {
            color: #e2e8f0;
            font-size: 0.95rem;
        }
        .pago-ok {
            color: #22c55e;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .estado-cuenta {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }
            .estado-contenido-izquierda {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
            <a href="pagos.php" style="color: #f3b229;">Pagos</a>
            <a href="beneficios.php">Beneficios</a>
            <a href="categoria.php">Mi Categoría</a>
            <?php if (($user['rol'] ?? '') === 'admin'): ?>
                <a href="admin_usuarios.php" style="color: #f3b229;">Admin</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php" style="color: #ef4444;">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Cuota Social y Pagos</h1>
        <p class="page-subtitle">Gestioná tus vencimientos y consultá el historial completo de transacciones de tu cuenta.</p>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-success"><?= $mensaje ?></div>
        <?php endif; ?>

        <!-- TARJETA DE ESTADO CON EL ESCUDO / IMAGEN -->
        <div class="estado-cuenta">
            <div class="estado-contenido-izquierda">
                <img src="img/escudo_boca.png" alt="Escudo Boca Juniors" class="escudo-pagos">
                <div class="estado-info">
                    <h3>Estado de Cuenta: Al Día</h3>
                    <p>Titular: <strong><?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?></strong> — Categoría: Adherente</p>
                </div>
            </div>
            <div>
                <form method="POST">
                    <button type="submit" name="pagar_cuota" class="btn-pagar">Pagar Cuota ($18.500)</button>
                </form>
            </div>
        </div>

        <!-- HISTORIAL DE TRANSACCIONES -->
        <div class="historial-section">
            <h2>Historial de Pagos Recientes</h2>
            <table class="tabla-pagos">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['pagos_usuario'] as $pago): ?>
                        <tr>
                            <td><?= htmlspecialchars($pago['concepto'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($pago['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($pago['monto'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="pago-ok"><?= htmlspecialchars($pago['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>