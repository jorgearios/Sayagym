<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM entrenadores WHERE id_entrenador = $id";
    
    if ($conexion->query($sql)) {
        header("Location: entrenadores.php?res=eliminado");
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
} else {
    header("Location: entrenadores.php");
}
exit;
