<?php

// 1. Incluimos el documento vital que nos permite leer o escribir datos en el sistema (nuestra conexión MySQL)
include 'config.php';
// 2. Incluimos el código HTML con la estética visual compartida por todas las pantallas
include 'header.php';


// Detectamos velozmente si hubo un click y envío por medio del método $_POST (el botón "Dar de alta")
if ($_POST) {
  // Jalamos uno por uno cada recuadro (cajas de texto o inputs del formulario) para prepararlos 
  $nom = $_POST['nombre'];
  $esp = $_POST['especialidad'];
  $tel = $_POST['telefono'];
  $cor = $_POST['correo'];
  $f_con = $_POST['fecha_contratacion'];
  $com = $_POST['tarifa_comision'];
  $tur = $_POST['turno'];

  // Preparamos nuestro comando de SQL (INSERT INTO) para empujar estos registros frescos directo hacia la tabla 'entrenadores'
  $sql = "INSERT INTO entrenadores 
            (nombre, especialidad, telefono, correo, fecha_contratacion, tarifa_comision, turno, estado) 
            VALUES ('$nom', '$esp', '$tel', '$cor', '$f_con', '$com', '$tur', 'activo')";

  // Ejecutamos o disparamos la instrucción estructurada de arriba
  if ($conexion->query($sql)) {
    // En caso triunfal, usamos una miniatura de JavaScript para regresar al usuario hacía la lista extensa de todos los entrenadores
    echo "<script>window.location='entrenadores.php';</script>";
    // Nota: se podía haber usado el equivalente de servidor: header('Location: ...');
  } else {
    // Al suceder fallas imprevistas informativas, alertamos lo sucedido pintando textualmente qué y porqué fracasó con un cajón "danger" rojo
    echo "<div class='alert alert-danger'>Error: " . $conexion->error . "</div>";
  }
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- Reja divisoria (Malla de dos retenciones 1 principal y su mini ventana compañera) -->
    <div style="display:grid; grid-template-columns: 1fr 340px; gap:24px; align-items:start;">

      <!-- TARJETA DEL FORMULARIO OFICIAL -->
      <form method="POST" class="card">

        <!-- Título oscuro pintado de color guinda con un sombreado degradado decorativo linear-gradient CSS -->
        <div class="card-header" style="background: linear-gradient(135deg, #4A148C, #7B1FA2);">
          <span class="card-title" style="color:#fff;">
            <i class="ti ti-barbell me-2"></i>Registro de Nuevo Entrenador
          </span>
        </div>

        <div class="card-body">
          <!-- Organizamos meticulosamente las opciones bajo dos columnas uniformes -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

            <div>
              <label class="form-label">Nombre Completo</label>
              <!-- Required asusta e incomoda o avisa si hay omisión: Impone obligación al campo por obligación técnica y lógica -->
              <input type="text" name="nombre" class="form-control" required placeholder="Ej. Carlos Méndez">
            </div>

            <div>
              <label class="form-label">Especialidad principal</label>
              <input type="text" name="especialidad" class="form-control" placeholder="Ej. Pesas o Funcional">
            </div>

            <div>
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control" placeholder="834 000 0000">
            </div>

            <div>
              <label class="form-label">Correo Electrónico (E-Mail)</label>
              <!-- Type="email" fuerza el control a admitir obligatoriamente texto que asimile dominios o una arroba  -->
              <input type="email" name="correo" class="form-control" placeholder="coach@ejemplo.com">
            </div>

            <div>
              <label class="form-label">Fecha del alta (Contratación)</label>
              <!-- date('Y-m-d') autocalcula el día universal y lo pre-estampa ya listo. Otorga conveniencia rápida sin elegirlo. -->
              <input type="date" name="fecha_contratacion" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div>
              <label class="form-label">Turno Cursado laboralmente</label>
              <select name="turno" class="form-select">
                <option value="Matutino">Matutino</option>
                <option value="Vespertino">Vespertino</option>
                <option value="Completo">Termino Completo General</option>
              </select>
            </div>

            <!-- Obligamos su ensanchamiento masivo por medio CSS "1/-1" en la cuadrícula superior limitándolo horizontal a toda regla para no arruinar estructura estética.  -->
            <div style="grid-column:1/-1;">
              <label class="form-label">Comisión recibida individual por Socio asignado ($)</label>
              <!-- Al estipular Type=number junto con el subvalor Step dejamos claro al ordenador abrir espacio decimal y suprimir letras estorbosas incalculables. -->
              <input type="number" step="0.01" name="tarifa_comision" class="form-control" placeholder="0.00">
            </div>

          </div>
        </div>

        <div class="card-footer">
          <!-- Botón o ancla inofensiva destructora o canceladora de retorno. -->
          <a href="entrenadores.php" class="btn btn-link">Cancelar la Alta</a>
          <!-- Acción definitiva para detonar toda mi operación programada (POST) guardadora de este sistema actual -->
          <button type="submit" class="btn btn-red"><i class="ti ti-check me-1"></i>Dar de Alta</button>
        </div>
      </form>

      <!-- CUADRÍCULA PUBLICITARIA LATERAL COMPAÑERA DERECHA -->
      <div style="display:flex; flex-direction:column; gap:16px;">

        <!-- Cartel decorativo visual -->
        <div class="motive-panel">
          <div class="line1">Forma<br>a los mejores</div>
          <div class="line2">Cada coach cuenta</div>
          <div
            style="font-family:'Oswald',sans-serif; font-size:1.2rem; color:#F5A623; margin-top:8px; letter-spacing:1px;">
            ¡EL EQUIPO ES LA FUERZA!
          </div>
        </div>

        <!-- Bloque informativo morado sobre lo que involucra manejar personal para este subsistema y su significado en vida real -->
        <div class="card" style="border-left: 4px solid #7B1FA2;">
          <div class="card-body" style="padding:18px;">
            <p
              style="font-size:0.8rem; color:#6D28D9; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
              Información Sobre Comisiones
            </p>
            <p style="font-size:0.85rem; color:#4B5563; line-height:1.6;">
              La tarifa de la comisión monetaria que se tecleé y registre expresamente justo sobre esa área (la casilla
              de tarifa) será libre de multiplicarse automáticamente por el abultado número de socios que integres
              individualmente a él en lo futuro.
            </p>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- Imprimimos o inyectamos visualmente la parte baja (footer) de fin del documento estricto -->
<?php include 'footer.php'; ?>