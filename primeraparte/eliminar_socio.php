<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM socios WHERE id_socio = $id";
    
    if ($conexion->query($sql)) {
        header("Location: socios.php?mensaje=eliminado");
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
}
?>