<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'sistema_gym';

$conexion = new mysqli($host, $user, $pass, $db);


if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");


// Incluimos el control de sesiones y protección de páginas automáticamente
require_once 'auth.php';
?>