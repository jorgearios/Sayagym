<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM socios WHERE id_socio = $id";
    
    if ($conexion->query($sql)) {
        header("Location: socios.php?res=eliminado");
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
} else {
    header("Location: socios.php");
}
exit;
