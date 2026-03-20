<?php
$conexion = new mysqli('localhost', 'root', '', 'sistema_gym');
$stmt_socio = $conexion->prepare("SELECT id_socio, nombre, apellido, password FROM socios WHERE correo = ? AND estado != 'inactivo'");
if (!$stmt_socio) {
    echo "ERROR 1: " . $conexion->error . "\n";
} else {
    echo "Stmt socio OK\n";
}

$stmt_ins = $conexion->prepare("INSERT INTO socios (nombre, apellido, correo, telefono, password, estado, fecha_registro, fecha_vencimiento) VALUES (?, ?, ?, ?, ?, 'activo', ?, ?)");
if (!$stmt_ins) {
    echo "ERROR 2: " . $conexion->error . "\n";
} else {
    echo "Stmt ins OK\n";
}
?>
