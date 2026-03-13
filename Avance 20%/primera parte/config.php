<?php

$host = 'localhost';
$user = 'admin';
$pass = 'e11a5294076c2dca0c0117243bb6dcff951d5cb5da79f0ca';
$db = 'RepoSayagym';

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");


?>