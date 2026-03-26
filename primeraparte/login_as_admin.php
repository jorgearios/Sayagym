<?php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['nombre'] = 'Admin Principal';
$_SESSION['rol'] = 'Administrador';
header("Location: gestionMembresias.php");
exit();
?>
