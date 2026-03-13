<?php
// -------------------------------------------------------
//  Configuración de conexión — XAMPP (entorno local)
//  Usuario por defecto de XAMPP: root / sin contraseña
// -------------------------------------------------------
$host = 'localhost';
$user = 'root';       // Usuario por defecto en XAMPP
$pass = '';           // XAMPP no tiene contraseña por defecto
$db   = 'sistema_gym';

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");


?>