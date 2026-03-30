<?php
// === INICIO DEL PROGRAMA ===
// Incluimos nuestro archivo de configuración (conexión) y la cabecera del sitio (HTML principal)
include 'config.php';
if (!esAdministrador()) {
  header("Location: login.php");
  exit();
}
include 'header.php';

// Iniciamos una variable "$socio" con el texto null que significa "nada" o vacía.
// La usaremos para guardar todos los datos antiguos o básicos del participante al cargar por primera vez
$socio = null;

// === SECCIÓN 1: REVISAMOS SI YA NOS DIERON UN SOCIO PARA EDITAR (POR LA RUTA URL "?id=") ===
// El código evalúa si detectamos la llegada de un valor de identidad o 'id' desde el enlace de la barra del navegador
if (isset($_GET['id'])) {

  // (int) nos sirve para transformar el pedazo de texto que llega como id a un número fijo por seguridad pura
  $id = (int) $_GET['id'];

  // Le pedimos a la base de datos que seleccione a TODOS (*) los datos que correspondan al id dado ("$id")
  $resultado = $conexion->query("SELECT * FROM socios WHERE id_socio = $id");

  // Metemos todo el bloque de resultados ordenaditos dentro del arreglo original ($socio) 
  $socio = $resultado->fetch_assoc();

  // Si misteriosamente la variable $socio se quedó absolutamente vacía, entonces el socio no se halló / o se metió un número incorrecto sin querer a la url
  if (!$socio) {
    // Así que regresamos silenciosamente a la lista de socios como si no ocurrió nada sin error, salimos ("exit").
    echo "<script>window.location='socios.php';</script>";
    exit;
  }
}

// Ahora, si por mala suerte tampoco dimos ni con un ID cargado, ¡Y además! el usuario nunca llenó los datos todavía a través del formulario... 
if (!$socio && !$_POST) {
  // Los devolvemos de regreso otra vez, de este modo evitamos los típicos errores cuando entran a la dirección manualmente "Sayagym/editarSocio.php".
  echo "<script>window.location='socios.php';</script>";
  exit;
}

// === SECCIÓN 2: COMENZAMOS A PREPARAR Y GUARDAR LOS DATOS ACTUALIZADOS DEL FORMULARIO POST ===
// $_POST indica y funciona solo si nosotros oprimiamos el botón de 'Guardar Cambios' allá abajo en nuestra pantallita
if ($_POST) {

  // Tomamos el dichoso número ID (El código oculto pero súper fundamental del socio), que viaja invisible
  $id = (int) $_POST['id_socio'];

  // Comenzamos a absorber o recuperar (real_escape_string salva el proceso para no generar inyecciones a la estructura de nuestra Base De Datos) todas nuestras etiquetas (nombre, apellido, contacto...) que escribieron en las casillas.
  $nom = $conexion->real_escape_string($_POST['nombre']);
  $ape = $conexion->real_escape_string($_POST['apellido']);
  $tel = $conexion->real_escape_string($_POST['telefono']);
  $tel_emergencia = $conexion->real_escape_string($_POST['contacto_emergencia']);
  $cor = $conexion->real_escape_string($_POST['correo']);
  $dir = $conexion->real_escape_string($_POST['direccion']);
  $f_nac = $conexion->real_escape_string($_POST['fecha_nacimiento']);

  // Casillas Selects
  $mem_id = (int) $_POST['id_membresia'];
  // Decimos: Si el entrenadador ("id_entrenador") NO se envió con un hueco blanco predeterminado, pon el que pidió y convirtió en entero, sino (" : ") coloca la palabra reservada inglesa de nulo
  $ent_id = (!empty($_POST['id_entrenador'])) ? (int) $_POST['id_entrenador'] : "NULL";
  $est = $conexion->real_escape_string($_POST['estado']);

  // Modificación específica de la foto
  $foto_sql = "";
  // Validamos si la persona colocó un fichero para su avatar de manera exitosa o nada mas no optó y lo dejó tal como estaba ya ("error == 0")
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    // De donde extrae el subfijo (.jpg, .png, .jpeg)? Extraemos del propio sistema
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    // Combinamos la cifra que da 'time()' con su aleatorización 'rand' hasta sumar ese .jpg al final para darle el estilo universal y seguro
    $filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $dest = "imagenes/" . $filename;

    // Ahora ordenamos la instrucción general: Transfiere la imagen retenida de temp (.tmp) hacía mis documentos reales: a 'uploads/'
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
      // De ser un proceso bueno y final, agregamos de último momento su orden o query (por ejemplo:", foto = 'uploads/imagen_1215.png'")
      $foto_sql = ", foto = '$dest'";
    }
  }

  // Preparamos por fin la actualización total del programa y la ordenanza estricta UPDATE para alterar los atributos y propiedades viejas
  $sql = "UPDATE socios SET 
            nombre = '$nom', apellido = '$ape', telefono = '$tel', 
            contacto_emergencia = '$tel_emergencia', correo = '$cor', 
            direccion = '$dir', fecha_nacimiento = '$f_nac', 
            id_membresia = $mem_id, id_entrenador = $ent_id, estado = '$est'
            $foto_sql
            WHERE id_socio = $id";

  // Aquí ejecutamos e interrogamos la validación
  if ($conexion->query($sql)) {
    // Victoria: enviamos una ruta nueva hacia nuestra listado base pero le metemos una marca final de URL '?res=editado' para activar una cajita verde en la visual de esa ventana. Finalizamos de igual forma; "exit"
    echo "<script>window.location='socios.php?res=editado';</script>";
    exit;
  } else {
    // Fracaso: imprimiremos un gran globo informativo del mal momento, usando bootstrap 
    echo "<div class='alert alert-danger'>Error: " . $conexion->error . "</div>";
  }

  // Se regenerarán y auto recargarán estas pautas con la reciente estructura ya para que las cajas visuales muestren sus nuevos datos, en ese raro momento de percance o falta. 
  $resultado = $conexion->query("SELECT * FROM socios WHERE id_socio = $id");
  $socio = $resultado->fetch_assoc();
}

// === SECCIÓN 3: PREPARAMOS LA CAJA DE OPCIÓN DE LOS PROFESORES O ENTRENADORES ===
// Consultamos por abecedario ('ORDER BY nombre ASC'), que profeso se mostrará dentro de la lista larga a lado de membresía
$query_entrenadores = "SELECT id_entrenador, nombre FROM entrenadores ORDER BY nombre ASC";
$resultado_entrenadores = $conexion->query($query_entrenadores);
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- FORMULARIO VISUAL PRINCIPAL (La tarjetita donde operamos todo lo humano -> "enctype" for a picture load) -->
    <form method="POST" class="card" style="max-width:860px; margin:0 auto;" enctype="multipart/form-data">

      <!-- Cabecera pintada con el color base "oro" y con iconos "ti-edit"  -->
      <div class="card-header gold">
        <span class="card-title">
          <i class="ti ti-edit me-2"></i>Editar Socio #<?php echo $socio['id_socio']; ?> —
          <?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']); ?>
        </span>
      </div>

      <div class="card-body">
        <!-- Nuestro Input 'tipo oculto / hidden' primordial. Nadie lo visualiza, ¡Pero el sistema lo reconoce y sin esto no actualizará quien solicitaste en POST! -->
        <input type="hidden" name="id_socio" value="<?php echo $socio['id_socio']; ?>">

        <!-- Datos Base -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          <div>
            <label class="form-label">Nombre(s)</label>
            <input type="text" name="nombre" class="form-control"
              value="<?php echo htmlspecialchars($socio['nombre']); ?>" required>
          </div>
          <div>
            <label class="form-label">Apellido(s)</label>
            <input type="text" name="apellido" class="form-control"
              value="<?php echo htmlspecialchars($socio['apellido']); ?>" required>
          </div>
          <div>
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control"
              value="<?php echo htmlspecialchars($socio['telefono']); ?>">
          </div>
          <div>
            <label class="form-label">Contacto de Emergencia</label>
            <input type="text" name="contacto_emergencia" class="form-control"
              value="<?php echo htmlspecialchars($socio['contacto_emergencia']); ?>">
          </div>
          <div>
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control"
              value="<?php echo htmlspecialchars($socio['correo']); ?>">
          </div>
          <div>
            <label class="form-label">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control"
              value="<?php echo $socio['fecha_nacimiento']; ?>">
          </div>
          <div style="grid-column:1/-1;">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control"
              value="<?php echo htmlspecialchars($socio['direccion']); ?>">
          </div>

          <!-- Bloque representativo fotográfico  -->
          <div style="grid-column:1/-1;">
            <label class="form-label">Foto de Perfil</label>
            <!-- Comprobación para que, si el individuo a la mano dispone o tiene previamente su icono en memoria, se exhiba ahí chiquito a simple vista -->
            <?php if (!empty($socio['foto'])) { ?>
              <div class="mb-2">
                <!-- Se dibuja el contorno tipo "profile" para revisar qué rostro nos aparece -->
                <img src="<?php echo htmlspecialchars($socio['foto']); ?>"
                  style="width:60px; height:60px; border-radius:50%; object-fit:cover;">
              </div>
              <?php
            } ?>
            <!-- Campo simple de "Busque en su disco (su PC)" -->
            <input type="file" name="foto" class="form-control" accept="image/*">
            <small class="text-muted">Si no deseas cambiar ni alterar la foto base de manera accidental, por favor
              simplemente ignora sin teclear esto y déjalo intacto o ignorado.</small>
          </div>
        </div>

        <!-- Línea central decorativa con subtitulo minúsculo en medio de ambas -->
        <div class="hr-text mt-3"><span>Plan y Personal Asignado Exclusivo</span></div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-top:12px;">

          <!-- Selector (o Menú Cajón Lista Despleglable) para Planes/Membresías -->
          <div>
            <label class="form-label">Plan Actual Contratado</label>
            <select name="id_membresia" class="form-select">
              <?php
              // Recopilamos absolutamente las membresías disponibles o en funcionamiento del momento
              $mems = $conexion->query("SELECT * FROM membresias");

              while ($m = $mems->fetch_assoc()) {
                // Evaluamos. Si el ID extraído concuerda enteramente (==) a la membresía almacenada del individuo, colamos "selected", es como ponerle un marcador fosforescente de que este plan elijo él originalmente.
                $sel = ($m['id_membresia'] == $socio['id_membresia']) ? 'selected' : '';

                // Formateamos en la pantalla de una con $precio. "number_format" lo vuelve un costo legible como pesos reales (con comas).
                echo "<option value='{$m['id_membresia']}' $sel>{$m['nombre']} (\$" . number_format($m['precio'], 2) . ")</option>";
              }
              ?>
            </select>
          </div>

          <!-- Entrenadores Coachs Cajita -->
          <div>
            <label class="form-label">Entrenador/Coach Asignado</label>
            <select name="id_entrenador" class="form-select">
              <option value="">-- Sin Entrenador --</option>
              <?php
              // Ciclo rápido para ubicar los entrenadores por lista 
              while ($ent = $resultado_entrenadores->fetch_assoc()) {
                ?>
                <!-- De misma manera al método anterior, compararemos el entrenador. ¡Misma técnica, misma estrategia! -->
                <option value="<?php echo $ent['id_entrenador']; ?>" <?php echo ($ent['id_entrenador'] == $socio['id_entrenador']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($ent['nombre']); ?>
                </option>
                <?php
              }
              ?>
            </select>
          </div>

          <!-- Cajita de control para Estados del practicante -->
          <div>
            <label class="form-label">Estado Sistémico</label>
            <select name="estado" class="form-select">
              <!-- Mediante if, imprimirá (echo) el "selected" sobre cada etiqueta respectiva en su turno. Así el color del usuario siempre concordará (Ejem. en 'Vencido') a como venía original. -->
              <option value="activo" <?php if ($socio['estado'] == 'activo')
                echo 'selected'; ?>>Activo</option>
              <option value="inactivo" <?php if ($socio['estado'] == 'inactivo')
                echo 'selected'; ?>>Inactivo</option>
              <option value="vencido" <?php if ($socio['estado'] == 'vencido')
                echo 'selected'; ?>>Vencido</option>
            </select>
          </div>

        </div>
      </div>

      <!-- Parte fina final de nuestro documento principal card-footer -->
      <div class="card-footer">
        <!-- Redireccionamiento general: el botón Cancelar -->
        <a href="socios.php" class="btn btn-link">Cancelar Operación</a>

        <!-- Botón que desencadena absolutamente toda la máquina de recolección hacia la cima de todo nuestro código (tipo: submit) -->
        <button type="submit" class="btn btn-gold"><i class="ti ti-device-floppy me-1"></i>Guardar Modificaciones
          Cambios</button>
      </div>
    </form>

  </div>
</div>

<!-- Pie general universal inferior de control -->
<?php include 'footer.php'; ?>