<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

// PROTECCIÓN: solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

// Solo se acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

// Verificamos el token CSRF
if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Solicitud no válida.');
}

// Validamos que el ID sea un entero positivo
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Identificador inválido.');
}

try {
    // Quitamos la fecha de archivado: la cuenta vuelve a estar activa
    $stmt = $conexion->prepare(
        'UPDATE usuarios
         SET archivado_en = NULL
         WHERE id = :id AND archivado_en IS NOT NULL'
    );
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 1) {
        $_SESSION['flash'] = 'Usuario reactivado correctamente.';
    } else {
        $_SESSION['flash'] = 'No se encontró un usuario archivado con ese identificador.';
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['flash'] = 'Ocurrió un error al reactivar el usuario.';
}

header('Location: usuarios_archivados.php');
exit;