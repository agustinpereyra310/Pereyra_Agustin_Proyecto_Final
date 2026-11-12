<?php
// Datos de conexión (XAMPP local: usuario root sin contraseña)
$host     = 'localhost';
$dbname   = 'boca_socios';
$usuario  = 'root';
$password = '';

try {
    // Se crea la conexión PDO con charset utf8mb4 (acentos y ñ)
    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $password
    );

    // Que PDO lance excepciones cuando hay un error de SQL
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Que los resultados vengan como arrays asociativos: $fila['nombre']
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // El detalle técnico va al log del servidor, no a la pantalla
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}