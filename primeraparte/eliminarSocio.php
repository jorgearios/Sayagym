<?php
// Incluimos la configuración para conectar a la base de datos
include 'config.php';

// Verificamos si se envió un ID (identificador) por la URL
if (isset($_GET['id'])) {

    // Convertimos el ID a un número entero por seguridad
    $id = (int)$_GET['id'];

    // Preparamos la orden (consulta) para borrar al socio con ese ID de la tabla "socios"
    $sql = "DELETE FROM socios WHERE id_socio = $id";

    // Ejecutamos la orden en la base de datos
    // Si funciona correctamente ($conexion->query devuelve true)
    if ($conexion->query($sql)) {
        // Redirigimos al usuario a la lista de socios con un mensaje de éxito ("res=eliminado")
        header("Location: /Sayagym/primeraparte/socios.php?res=eliminado");
    }
    else {
        // Si hubo un error en la base de datos, lo mostramos en pantalla
        echo "Error al intentar eliminar el registro: " . $conexion->error;
    }
}
else {
    // Si no se envió ningún ID a eliminar, simplemente regresamos a la lista de socios
    header("Location: /Sayagym/primeraparte/socios.php");
}

// Detenemos la ejecución del programa aquí, ya que no necesitamos procesar nada más
exit;
?>
