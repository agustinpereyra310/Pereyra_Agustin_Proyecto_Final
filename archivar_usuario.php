<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

// PROTECCIÓN: solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

// Solo se acepta POST (nunca archivar con un link GET)
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

// Un admin no puede archivarse a sí mismo (quedaría bloqueado)
if ($id === (int) $_SESSION['usuario_id']) {
    $_SESSION['flash'] = 'No podés archivar tu propia cuenta.';
    header('Location: admin_usuarios.php');
    exit;
}

try {
    $stmt = $conexion->prepare(
        'UPDATE usuarios
         SET archivado_en = CURRENT_TIMESTAMP
         WHERE id = :id AND archivado_en IS NULL'
    );
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 1) {
        $_SESSION['flash'] = 'Usuario archivado correctamente.';
    } else {
        $_SESSION['flash'] = 'No se encontró un usuario activo con ese identificador.';
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['flash'] = 'Ocurrió un error al archivar el usuario.';
}

header('Location: admin_usuarios.php');
exit;