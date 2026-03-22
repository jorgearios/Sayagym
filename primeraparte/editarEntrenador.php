<?php 
// 1. Configuraciones previas y cabeceras operativas del visual y conexion base de datos
include 'config.php';
include 'header.php'; 

// Preparamos nuestro recipiente general "$e" ($Entrenador) que es inicializado enteramente en blanco momentáneo con un texto que simboliza "null" (nada).
$e = null;


// Verificamos si en la barra de direcciones superior URL el portal traía adjudicado la variable "?id="
if (isset($_GET['id'])) {
    
    // Lo purificamos contra fallas e infiltraciones guardándolo de número texto engañoso a número seguro inoficioso de sistema "(int)"
    $id = (int)$_GET['id'];
    
    // Tratamos ahora sí de invocar al perfil solicitado usando nuestro conector a MySQL de búsqueda generalizada
    $resultado = $conexion->query("SELECT * FROM entrenadores WHERE id_entrenador = $id");
    
    // Si la invocación arrojó buen volumen de texto o información, guardarlo desmenuzado y preparado dentro de un cajón arreglo ($e = Entrenador).
    $e = $resultado->fetch_assoc();
    
    // Y un gran percatado, en caso raro o desafortunado sin registro actual a editar: retornarnos (huir rápidamente atrás).
    if (!$e) {
        echo "<script>window.location='entrenadores.php';</script>";
        exit;
    }
}

// Filtrajes para el "Sabelotodo" (Hacker o curioso) sin propósitos claros. De no mandar petición oficial o su nombre de ID, botaremos a dicho explorador devuelto a la anterior etapa pasiva 
if (!$e && !$_POST) {
    echo "<script>window.location='entrenadores.php';</script>";
    exit;
}

// === SECCIÓN DE VALIDACIÓN Y CUMPLIMENTO DE ESCRITURAS NUEVAS (CUANDO LE DAMOS CLICK AL FORMULARIO ACTUALIZAR (POST)) ===
if ($_POST) {
    // Jalamos y escondemos astutamente nuestra clave id del profesor escondida atrás como Input "id_entrenador"
    $id_ent = (int)$_POST['id_entrenador'];
    
    // Atrapamos un listado inmenso de recuadros. Con "real_escape_string" destruimos todo código raro ("Inyección SQL" cruzada) intentado. Protegiendo nuestra arquitectura.
    $nom = $conexion->real_escape_string($_POST['nombre']);
    $esp = $conexion->real_escape_string($_POST['especialidad']);
    $tel = $conexion->real_escape_string($_POST['telefono']);
    $cor = $conexion->real_escape_string($_POST['correo']);
    
    // Lo forzamos a una magnitud de valor con comas usando punto (float) porque retendrá dinero fraccionable
    $com = (float)$_POST['tarifa_comision'];
    $tur = $conexion->real_escape_string($_POST['turno']);
    $est = $conexion->real_escape_string($_POST['estado']);

    // Ensamblaremos una gigante estructura SQL de reconstrucción general masiva; la técnica se apoda "UPDATE", de esta manera apuntando al $id_ent sustituido
    $sql = "UPDATE entrenadores SET 
            nombre = '$nom', especialidad = '$esp', telefono = '$tel', 
            correo = '$cor', tarifa_comision = '$com', turno = '$tur', estado = '$est'
            WHERE id_entrenador = $id_ent";
    
    // Mandamos el comando decisivo  y aguardemos la validación o rechazo 
    if ($conexion->query($sql)) {
        // Victoria: Emitiremos una rápida transición javascript regresando al origen de inicio, anexando la variable estigmatizada "?res=editado", aclamando la victoria del recuadro
        echo "<script>window.location='entrenadores.php?res=editado';</script>";
        exit;
    } else {
        // En adversidad, desplegaremos la falla original, mostramos este cajón o alert (Alerta roja clásica danger de componente HTML y Bootstrap). 
        echo "<div class='alert alert-danger'>Ocurrió un severo o pequeño error de la red: " . $conexion->error . "</div>";
    }

    // Por prevención, actualizamos todo rastro del actual si fracaso con una vista refrescada. Y seguimos adelante mostrando la página vieja de abajo
    $resultado = $conexion->query("SELECT * FROM entrenadores WHERE id_entrenador = $id_ent");
    $e = $resultado->fetch_assoc();
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- Cuestionario con una amplitud estética máxima reducida y enfocada centralmente por 'margin 0 auto' -->
    <form method="POST" class="card" style="max-width:760px; margin:0 auto;">
      
      <!-- Listón de título encabezado de temática vistosa morada oscura entonada para el nivel del Entrenador  -->
      <div class="card-header" style="background: linear-gradient(135deg, #4A148C, #7B1FA2);">
        <span class="card-title" style="color:#fff;">
          <i class="ti ti-barbell me-2"></i>Editar Entrenador — <?php echo htmlspecialchars($e['nombre']); ?>
        </span>
      </div>
      
      <div class="card-body">
        <!-- ¡ATENCION! Nuestro campo totalmente ocultado vital y preciado "id_entrenador". De él dependerá encontrar esto despistadamente al momento de retornar el cuestionario actualizado bajo el rubro $_POST. Si desaparece o se corroe, todo el plan maestro de actualizar cae de facto. -->
        <input type="hidden" name="id_entrenador" value="<?php echo $e['id_entrenador']; ?>">

        <!-- Empezaremos dos inmensas cuadrículas perfectas paralelas separando 16pixeles entre cajita -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          
          <div>
            <label class="form-label">Nombre Completo Registrado</label>
            <!-- Suministramos cada palabra antigua original para rellenar los hoyos ("value=") pasando cada caracter entre limpiezas para evitar sorpresas fatídicas de los mismos ("htmlspecialchars()") -->
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($e['nombre']); ?>" required>
          </div>
          
          <div>
            <label class="form-label">Especialidad Fuerte</label>
            <input type="text" name="especialidad" class="form-control" value="<?php echo htmlspecialchars($e['especialidad']); ?>">
          </div>
          
          <div>
            <label class="form-label">El Teléfono Actual</label>
            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($e['telefono']); ?>">
          </div>
          
          <div>
            <label class="form-label">Correo Electrónico Oficial</label>
            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($e['correo']); ?>">
          </div>
          
          <div>
            <label class="form-label">Turno Cursado a Cambiar o Vigente</label>
            <select name="turno" class="form-select">
              <!-- Comprobamos (if) entre cada opción. Y si concuerda al nombre previo "Matutino/Vespertino/etc." se anexiona una etiqueta fantasma "selected" logrando automáticamente ubicar la cajuela del Select donde le correspondía original desde antes. -->
              <option value="Matutino"   <?php if($e['turno']=='Matutino')   echo 'selected'; ?>>Matutino Corto</option>
              <option value="Vespertino" <?php if($e['turno']=='Vespertino') echo 'selected'; ?>>Vespertino Tarde</option>
              <option value="Completo"   <?php if($e['turno']=='Completo')   echo 'selected'; ?>>Completo Intersemanal</option>
            </select>
          </div>
          
          <div>
            <label class="form-label">Tarifa de Comisión base establecida ($)</label>
            <!-- Permite valores .10 centavos decimal -> ".01" -->
            <input type="number" step="0.01" name="tarifa_comision" class="form-control" value="<?php echo $e['tarifa_comision']; ?>">
          </div>
          
          <!-- Tomamos por fuerza absoluta e implacable los 2 anchos y espacios al completo reescribiendo la norma -->
          <div style="grid-column:1/-1;">
            <label class="form-label">Estado Laboral de Empleado Vigente</label>
            <select name="estado" class="form-select">
              <option value="activo"   <?php if($e['estado']=='activo')   echo 'selected'; ?>>Completamente Activo (En nómina laboral / Contratado temporal general)</option>
              <option value="inactivo" <?php if($e['estado']=='inactivo') echo 'selected'; ?>>Inactivo o Baja (Renuncia / Despedido / Permiso sin goce de horas laboradas)</option>
            </select>
          </div>
          
        </div>
      </div>
      
      <!-- Líneas inferiores de confirmación de botón -->
      <div class="card-footer">
        <!-- Vínculo retrovisor pasivo cancelador global de operaciones, huida.  -->
        <a href="entrenadores.php" class="btn btn-link">Cancelar Regreso Vacuo</a>
        
        <!-- Botón que desencadenará obligatoriamente la maquinaria procesadora (submit) de toda nuestra reconstrucción del perfil viejo de arriba en un perfil final -->
        <button type="submit" class="btn btn-red"><i class="ti ti-device-floppy me-1"></i>Actualizar Perfil Existente Integrado</button>
      </div>
    </form>

  </div>
</div>

<!-- Imposición de final de pie de página de la institución / empresa pre-hecha general -->
<?php include 'footer.php'; ?>
