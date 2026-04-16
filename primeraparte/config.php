<?php

// ── CREDENCIALES DE BASE DE DATOS ──
$host = 'localhost';   // Servidor de base de datos
$user = 'root';        // Usuario de la base de datos (por defecto 'root' en XAMPP)
$pass = '';            // Contraseña de la base de datos (por defecto vacía en XAMPP)
$db = 'sistema_gym';   // Nombre de la base de datos del proyecto

// ── CONEXIÓN ──
// Intentamos establecer la conexión con la base de datos utilizando la extensión MySQLi
$conexion = new mysqli($host, $user, $pass, $db);

// Si existe un error al conectar, detenemos la ejecución del script y mostramos el error
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configuramos el conjunto de caracteres a UTF-8 para soportar tildes, ñ y emojis
$conexion->set_charset("utf8mb4");

// Incluimos el archivo de autenticación que gestiona las sesiones y rutas protegidas
require_once 'auth.php';
?>