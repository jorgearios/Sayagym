<?php
// Primero traemos toda nuestra principal herramienta conectora a la matriz web local de bases de datos central 
include 'config.php';
if (!esAdministrador()) {
    header("Location: login.php");
    exit();
}

// Observamos meticulosamente si entre las variables proveídas por el URL o barra de hipervínculos navegable (GET) existe el atributo singularizado 'id' (!Si un botón nos lo inyectó)
if (isset($_GET['id'])) {

    // De ser real y existir, solidificamos rigurosamente sin posibles textos inyectados de hackers, a una identidad numérica limpia (int). (Nos desharemos de cualquier charlar de palabras mal intencionadas)
    $id = (int) $_GET['id'];

    // Entonamos la poderosa sentencia y rígida en formato de Texto puro inquebrantable para despachar registros viejos e inservibles por número de identificador de la extensa tabla base. (DELETE FROM / ELIMINAR DE)
    $sql = "DELETE FROM entrenadores WHERE id_entrenador = $id";

    // Desplegamos y forzamos el intento y agresividad constructiva contra la propia base matriz "query" de manera activa.. Y posteriormente revisaremos tras un filtro con condicional la realidad y éxito puro (TRUE o FALSE) del borroso registro.
    if ($conexion->query($sql)) {
        // Redirigiremos elegantemente con este código especial al lugar general de proveniencia donde la lista original subyacía, no sin antes, colarle un pequeño trofeo de validación de etiqueta con marca ("?res=eliminado") para iluminar un aviso rojo informativo general al usuario ciego
        header("Location: entrenadores.php?res=eliminado");
    } else {
        // Y en su debido contrastante o polar efecto, si una fatalidad o vínculo relacional prohíbe el corte neto inquebrantable entre el maestro de este individuo a eliminar, presentaremos un seco tropiezo informativo extraído desde el propio y original núcleo de MySQL y lo imprimimos al descubierto total de pantalla
        echo "Ocurrió un grave error interno insalvable al intentar eliminar rudimentariamente este rubro de registro particular sistémico: " . $conexion->error;
    }
} else {
    // Escolta normal y rebote pasivo y pacifico a una zona estable si alguien teclea mágicamente 'eliminar' u oscurece la url erróneamente sin justificaciones de ID. 
    header("Location: entrenadores.php");
}

// Interrumpimos y abandonamos lícitamente inmiscuidas las labores previas de red tras la línea actual culminando el total alcance y asegurando que nuestro documento ya no intentará procesar mas lineas estrafalarias o raras ni HTML
exit;
?>