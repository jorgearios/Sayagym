<?php
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conexion->query("DELETE FROM socio_rutina    WHERE id_rutina=$id");
    $conexion->query("DELETE FROM rutina_ejercicio WHERE id_rutina=$id");
    $conexion->query("DELETE FROM rutinas          WHERE id_rutina=$id");
    header("Location: rutinas.php?res=eliminada");
} else {
    header("Location: rutinas.php");
}
exit;
?>
