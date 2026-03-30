<?php

// 1. Incluimos el archivo para conectarnos a la base de datos
include 'config.php';
// 2. Incluimos el encabezado para el menú y diseño visual
include 'header.php';


// Verificamos si el formulario nos envió datos (método POST)
if ($_POST) {
  // Escapamos los textos para evitar que nos inyecten código malicioso (SQL Injection)
  $nom = mysqli_real_escape_string($conexion, $_POST['nombre']);
  $ape = mysqli_real_escape_string($conexion, $_POST['apellido']);
  $tel = mysqli_real_escape_string($conexion, $_POST['telefono']);
  $tel_e = mysqli_real_escape_string($conexion, $_POST['contacto_emergencia']);
  $cor = mysqli_real_escape_string($conexion, $_POST['correo']);
  $dir = mysqli_real_escape_string($conexion, $_POST['direccion']);

  // Obtenemos las fechas e IDs (identificadores)
  $f_nac = $_POST['fecha_nacimiento'];
  $id_mem = $_POST['id_membresia'];

  // Si seleccionaron entrenador, usamos su ID. Si no, enviamos "NULL" que significa "vacío" o "nada"
  $id_ent = !empty($_POST['id_entrenador']) ? $_POST['id_entrenador'] : "NULL";

  // La fecha de registro es la misma del día de hoy
  $f_reg = date('Y-m-d');

  // === PROCESAMOS LA FOTO DE PERFIL ===
  $foto_path = "";
  // Revisamos si se subió un archivo llamado 'foto' y si no hubo errores al subirlo (error == 0)
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

    // Obtenemos la extensión o tipo del archivo (por ejemplo, jpg, png, etc.)
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);

    // Creamos un nombre único aleatorio para no sobreescribir fotos viejas: "numeroDeTiempo_1234.jpg"
    $filename = time() . '_' . rand(1000, 9999) . '.' . $ext;

    // Indicamos dónde la vamos a guardar usando una ruta relativa de carpeta
    $dest = "imagenes/" . $filename;

    // Movemos el archivo temporal (que nos llega del formulario en memoria) a nuestra carpeta "uploads"
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
      $foto_path = $dest;
    }
  }

  // === CALCULAMOS LA FECHA DE VENCIMIENTO DEL PLAN ===
  // Preguntamos a la base de datos cuántos meses dura el plan ("membresía") elegido por el usuario
  $mem_res = $conexion->query("SELECT duracion_meses FROM membresias WHERE id_membresia = $id_mem");

  // Verificamos si en verdad encontramos dicha membresía
  if ($mem_res && $mem_res->num_rows > 0) {
    // Sacamos los datos de la respuesta
    $m = $mem_res->fetch_assoc();

    // Asignamos la duración en meses
    $meses = $m['duracion_meses'];

    // Le sumamos los meses a la fecha de hoy para saber cuándo vencerá el plan del socio nuevo
    $f_ven = date('Y-m-d', strtotime("+$meses months"));
  } else {
    // En un caso raro de que no funcione u olvidaron algo, ponemos que se vence hoy mismo
    $f_ven = $f_reg;
  }

  // === GUARDAMOS AL NUEVO SOCIO ===
  // Generar código QR único
  $qr_codigo = 'SGY-' . strtoupper(bin2hex(random_bytes(5)));

  $sql = "INSERT INTO socios (nombre, apellido, telefono, contacto_emergencia, correo, direccion, fecha_nacimiento, fecha_registro, fecha_vencimiento, id_membresia, id_entrenador, estado, foto, qr_codigo) 
            VALUES ('$nom', '$ape', '$tel', '$tel_e', '$cor', '$dir', '$f_nac', '$f_reg', '$f_ven', $id_mem, $id_ent, 'activo', '$foto_path', '$qr_codigo')";

  if ($conexion->query($sql)) {
    $id_nuevo_socio = $conexion->insert_id;
    // Registrar en historial de membresías
    $conexion->query("INSERT INTO socios_membresias (id_socio, id_membresia, fecha_inicio, fecha_fin, estado) VALUES ($id_nuevo_socio, $id_mem, '$f_reg', '$f_ven', 'activa')");
    echo "<script>window.location='socios.php';</script>";
  } else {
    die("Error al guardar: " . $conexion->error);
  }
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- Cuadrícula para acomodar nuestro formulario en la zona grande y un panel vertical en la chica (derecha) -->
    <div style="display:grid; grid-template-columns: 1fr 380px; gap:24px; align-items:start;">

      <!-- TARJETA PRINCIPAL DEL FORMULARIO -->
      <!-- Es importante usar enctype="multipart/form-data" para permitir subir y enviar fotos y archivos -->
      <form method="POST" class="card" enctype="multipart/form-data">
        <div class="card-header red">
          <span class="card-title"><i class="ti ti-user-plus me-2"></i>Nueva Inscripción de Socio</span>
        </div>

        <div class="card-body">
          <!-- Cuadrícula secundaria para los campos (Cajas de texto o inputs) en 2 columnas -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

            <div>
              <label class="form-label">Nombre(s)</label>
              <!-- La propiedad 'required' significa que este campo es completamente obligatorio para avanzar -->
              <input type="text" name="nombre" class="form-control" required placeholder="Juan">
            </div>

            <div>
              <label class="form-label">Apellido(s)</label>
              <input type="text" name="apellido" class="form-control" required placeholder="Pérez">
            </div>

            <div>
              <label class="form-label">Teléfono</label>
              <!-- 'placeholder' es un texto decorativo temporal que te muestra cómo debes escribir los datos -->
              <input type="text" name="telefono" class="form-control" placeholder="834 000 0000">
            </div>

            <div>
              <label class="form-label">Contacto de Emergencia</label>
              <input type="text" name="contacto_emergencia" class="form-control" placeholder="834 000 0000">
            </div>

            <div>
              <label class="form-label">Correo Electrónico</label>
              <input type="email" name="correo" class="form-control" placeholder="socio@ejemplo.com">
            </div>

            <div>
              <label class="form-label">Fecha de Nacimiento</label>
              <!-- 'type=date' muestra un pequeño calendario visual para que podamos elegir un día -->
              <input type="date" name="fecha_nacimiento" class="form-control">
            </div>

            <!-- grid-column:1/-1 usamos CSS grid para forzar que este campo único ocupe todo el ancho disponible -->
            <div style="grid-column:1/-1;">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-control" placeholder="Calle, Número y Colonia">
            </div>

            <div style="grid-column:1/-1;">
              <label class="form-label">Foto de Perfil</label>
              <!-- 'accept="image/*"' obliga a tu computadora a que solo te deje elegir imágenes compatibles al explorar -->
              <input type="file" name="foto" class="form-control" accept="image/*">
            </div>

          </div> <!-- Fin cuadrícula de inputs iniciales -->

          <!-- Pequeño separador visual de interfaz con un título en el centro -->
          <div class="hr-text mt-3"><span>Plan y Personal</span></div>

          <!-- Cuadrícula para las opciones (Select) de planes y coaches -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:12px;">
            <div>
              <label class="form-label">Plan de Membresía</label>
              <select name="id_membresia" class="form-select" required>
                <!-- La primera opción vacía actúa como título o texto por defecto del elemento de lista -->
                <option value="">Seleccione un plan...</option>
                <?php
                // Buscamos todas las membresías que existen en nuestra base de datos
                $mems = $conexion->query("SELECT * FROM membresias");
                // Mientras haya planes en el resultado, los seguimos iterando (repitiendo) acá
                while ($m = $mems->fetch_assoc()) {
                  // Armamos dinámicamente cada "option": guardamos el ID clave pero le mostramos el nombre al usuario
                  echo "<option value='{$m['id_membresia']}'>{$m['nombre']} - \${$m['precio']}</option>";
                }
                ?>
              </select>
            </div>

            <div>
              <label class="form-label">Entrenador Personal</label>
              <select name="id_entrenador" class="form-select">
                <option value="">-- Sin entrenador --</option>
                <?php
                // Localizamos a todos los entrenadores o profes que contengan el estatus 'activo'
                $profes = $conexion->query("SELECT id_entrenador, nombre FROM entrenadores WHERE estado = 'activo'");
                while ($p = $profes->fetch_assoc()) {
                  echo "<option value='{$p['id_entrenador']}'>{$p['nombre']}</option>";
                }
                ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Pie (inferior) de nuestra tarjeta -->
        <div class="card-footer">
          <!-- Botón o Vínculo web normal que sirve para regresarse o cancelar toda la instrucción de arriba -->
          <a href="index.php" class="btn btn-link">Cancelar</a>
          <!-- Botón definitivo tipo "submit" que envía absolutamente toda la información a donde empezamos en la línea = "if($_POST)" -->
          <button type="submit" class="btn btn-red"><i class="ti ti-check me-1"></i>Registrar Socio</button>
        </div>
      </form> <!-- Cierre del tag de formulario -->

      <!-- PANEl LATERAL PUBLICITARIO Y DE ADVERTENCIAS (A lado del formulario) -->
      <div style="display:flex; flex-direction:column; gap:16px;">

        <!-- Cuadro azul-gradiente lateral llamativo y sin funciones traseras -->
        <div class="motive-panel">
          <div class="line1">Regístrate y<br>haz un cambio</div>
          <div class="line2">ahora en tu vida</div>
          <div
            style="font-family:'Oswald',sans-serif; font-size:1.5rem; font-weight:700; color:#F5A623; letter-spacing:2px; margin-top:8px;">
            ¡TÚ PUEDES LOGRARLO!
          </div>
        </div>

        <!-- Tarjetita de alerta informativa (Roja clarita estilo pastel) con la misma funcionalidad pero un poco de texto de ayuda explicativo para la persona capturando datos en el sistema -->
        <div class="card" style="background:#FEF2F2; border-color:#FCA5A5;">
          <div class="card-body" style="padding:20px;">
            <p
              style="font-size:0.8rem; color:#991B1B; font-weight:600; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">
              <i class="ti ti-info-circle me-1"></i> Nota de Ayuda
            </p>
            <p style="font-size:0.85rem; color:#7F1D1D; line-height:1.5;">
              La fecha de vencimiento que marca a la membresía de la persona se calculará automáticamente según la
              duración (meses predeterminados) dependiente de qué plan elijas justo en el menú de aquí a un costado.
            </p>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- Incluimos y finalizamos uniendo la parte inferior universal donde acaba el HTML (footer) -->
<?php include 'footer.php'; ?>