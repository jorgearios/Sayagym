<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "DELETE FROM entrenadores WHERE id_entrenador = $id";
    
    if ($conexion->query($sql)) {
        header("Location: entrenadores.php?res=eliminado");
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
}
?>