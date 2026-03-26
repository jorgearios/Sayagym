<?php
$conexion = new mysqli('localhost', 'root', '', 'sistema_gym');
$res = $conexion->query("SELECT cedula, password FROM socios WHERE rol='Administrador' LIMIT 1");
if ($row = $res->fetch_assoc()) {
    echo $row['cedula'] . ":" . $row['password'];
}
?>
