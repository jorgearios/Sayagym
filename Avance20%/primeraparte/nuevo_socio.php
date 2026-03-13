<?php 
include 'config.php';
include 'header.php'; 

if ($_POST) {
    $nom   = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $ape   = mysqli_real_escape_string($conexion, $_POST['apellido']);
    $tel   = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $tel_e = mysqli_real_escape_string($conexion, $_POST['contacto_emergencia']);
    $cor   = mysqli_real_escape_string($conexion, $_POST['correo']);
    $dir   = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $f_nac = $_POST['fecha_nacimiento'];
    $id_mem = $_POST['id_membresia'];
    $id_ent = !empty($_POST['id_entrenador']) ? $_POST['id_entrenador'] : "NULL";
    $f_reg  = date('Y-m-d');

    $mem_res = $conexion->query("SELECT duracion_meses FROM membresias WHERE id_membresia = $id_mem");
    if($mem_res && $mem_res->num_rows > 0){
        $m = $mem_res->fetch_assoc();
        $meses = $m['duracion_meses'];
        $f_ven = date('Y-m-d', strtotime("+$meses months"));
    } else {
        $f_ven = $f_reg;
    }

    $sql = "INSERT INTO socios (nombre, apellido, telefono, contacto_emergencia, correo, direccion, fecha_nacimiento, fecha_registro, fecha_vencimiento, id_membresia, id_entrenador, estado) 
            VALUES ('$nom', '$ape', '$tel', '$tel_e', '$cor', '$dir', '$f_nac', '$f_reg', '$f_ven', $id_mem, $id_ent, 'activo')";
    
    if ($conexion->query($sql)) {
        echo "<script>window.location='socios.php';</script>";
    } else {
        die("Error al guardar: " . $conexion->error);
    }
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div style="display:grid; grid-template-columns: 1fr 380px; gap:24px; align-items:start;">

      <!-- FORM CARD -->
      <form method="POST" class="card">
        <div class="card-header red">
          <span class="card-title"><i class="ti ti-user-plus me-2"></i>Nueva Inscripción de Socio</span>
        </div>
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
              <label class="form-label">Nombre(s)</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Juan">
            </div>
            <div>
              <label class="form-label">Apellido(s)</label>
              <input type="text" name="apellido" class="form-control" required placeholder="Pérez">
            </div>
            <div>
              <label class="form-label">Teléfono</label>
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
              <input type="date" name="fecha_nacimiento" class="form-control">
            </div>
            <div style="grid-column:1/-1;">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-control" placeholder="Calle, Número y Colonia">
            </div>
          </div>

          <div class="hr-text mt-3"><span>Plan y Personal</span></div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:12px;">
            <div>
              <label class="form-label">Plan de Membresía</label>
              <select name="id_membresia" class="form-select" required>
                <option value="">Seleccione un plan...</option>
                <?php 
                $mems = $conexion->query("SELECT * FROM membresias");
                while($m = $mems->fetch_assoc()){
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
                $profes = $conexion->query("SELECT id_entrenador, nombre FROM entrenadores WHERE estado = 'activo'");
                while($p = $profes->fetch_assoc()){
                    echo "<option value='{$p['id_entrenador']}'>{$p['nombre']}</option>";
                }
                ?>
              </select>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="index.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-red"><i class="ti ti-check me-1"></i>Registrar Socio</button>
        </div>
      </form>

      <!-- MOTIVATIONAL PANEL -->
      <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="motive-panel">
          <div class="line1">Regístrate y<br>haz un cambio</div>
          <div class="line2">ahora en tu vida</div>
          <div style="font-family:'Oswald',sans-serif; font-size:1.5rem; font-weight:700; color:#F5A623; letter-spacing:2px; margin-top:8px;">
            ¡TÚ PUEDES LOGRARLO!
          </div>
        </div>
        <div class="card" style="background:#FEF2F2; border-color:#FCA5A5;">
          <div class="card-body" style="padding:20px;">
            <p style="font-size:0.8rem; color:#991B1B; font-weight:600; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">
              <i class="ti ti-info-circle me-1"></i> Nota
            </p>
            <p style="font-size:0.85rem; color:#7F1D1D; line-height:1.5;">
              La fecha de vencimiento se calcula automáticamente según la duración del plan seleccionado.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
