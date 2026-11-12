<?php
session_start();

// Vaciamos los datos de la sesión
$_SESSION = [];

// Borramos también la cookie de sesión del navegador
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Destruimos la sesión en el servidor
session_destroy();

header('Location: login.php');
exit;