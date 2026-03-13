<?php 
include 'config.php';
include 'header.php'; 

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $resultado = $conexion->query("SELECT * FROM socios WHERE id_socio = $id");
    $socio = $resultado->fetch_assoc();
    if (!$socio) {
        echo "<script>window.location='socios.php';</script>";
        exit;
    }
}

if ($_POST) {
    $id = $_POST['id_socio'];
    $nom = $_POST['nombre'];
    $ape = $_POST['apellido'];
    $tel = $_POST['telefono'];
    $tel_emergencia = $_POST['contacto_emergencia'];
    $cor = $_POST['correo'];
    $dir = $_POST['direccion'];
    $f_nac = $_POST['fecha_nacimiento'];
    $mem_id = $_POST['id_membresia'];
    $ent_id = ($_POST['id_entrenador'] == "") ? "NULL" : $_POST['id_entrenador'];
    $est = $_POST['estado'];

    $sql = "UPDATE socios SET 
            nombre = '$nom', apellido = '$ape', telefono = '$tel', 
            contacto_emergencia = '$tel_emergencia', correo = '$cor', 
            direccion = '$dir', fecha_nacimiento = '$f_nac', 
            id_membresia = '$mem_id', id_entrenador = $ent_id, estado = '$est'
            WHERE id_socio = $id";
    
    if ($conexion->query($sql)) {
        echo "<script>window.location='socios.php?res=editado';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conexion->error . "</div>";
    }
}

$query_entrenadores = "SELECT id_entrenador, nombre FROM entrenadores ORDER BY nombre ASC";
$resultado_entrenadores = $conexion->query($query_entrenadores);
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <form method="POST" class="card" style="max-width:860px; margin:0 auto;">
      <div class="card-header gold">
        <span class="card-title">
          <i class="ti ti-edit me-2"></i>Editar Socio #<?php echo $socio['id_socio']; ?> — <?php echo $socio['nombre'].' '.$socio['apellido']; ?>
        </span>
      </div>
      <div class="card-body">
        <input type="hidden" name="id_socio" value="<?php echo $socio['id_socio']; ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          <div>
            <label class="form-label">Nombre(s)</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo $socio['nombre']; ?>" required>
          </div>
          <div>
            <label class="form-label">Apellido(s)</label>
            <input type="text" name="apellido" class="form-control" value="<?php echo $socio['apellido']; ?>" required>
          </div>
          <div>
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="<?php echo $socio['telefono']; ?>">
          </div>
          <div>
            <label class="form-label">Contacto de Emergencia</label>
            <input type="text" name="contacto_emergencia" class="form-control" value="<?php echo $socio['contacto_emergencia']; ?>">
          </div>
          <div>
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" value="<?php echo $socio['correo']; ?>">
          </div>
          <div>
            <label class="form-label">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo $socio['fecha_nacimiento']; ?>">
          </div>
          <div style="grid-column:1/-1;">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control" value="<?php echo $socio['direccion']; ?>">
          </div>
        </div>

        <div class="hr-text mt-3"><span>Plan y Personal</span></div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-top:12px;">
          <div>
            <label class="form-label">Plan Actual</label>
            <select name="id_membresia" class="form-select">
              <?php 
              $mems = $conexion->query("SELECT * FROM membresias");
              while($m = $mems->fetch_assoc()){
                  $sel = ($m['id_membresia'] == $socio['id_membresia']) ? 'selected' : '';
                  echo "<option value='{$m['id_membresia']}' $sel>{$m['nombre']} (\$".number_format($m['precio'],2).")</option>";
              }
              ?>
            </select>
          </div>
          <div>
            <label class="form-label">Entrenador Asignado</label>
            <select name="id_entrenador" class="form-select">
              <option value="">-- Sin Entrenador --</option>
              <?php while($e = $resultado_entrenadores->fetch_assoc()): ?>
                <option value="<?php echo $e['id_entrenador']; ?>" <?php echo ($e['id_entrenador'] == $socio['id_entrenador']) ? 'selected' : ''; ?>>
                  <?php echo $e['nombre']; ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
              <option value="activo"   <?php if($socio['estado']=='activo')   echo 'selected'; ?>>Activo</option>
              <option value="inactivo" <?php if($socio['estado']=='inactivo') echo 'selected'; ?>>Inactivo</option>
              <option value="vencido"  <?php if($socio['estado']=='vencido')  echo 'selected'; ?>>Vencido</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <a href="socios.php" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-gold"><i class="ti ti-device-floppy me-1"></i>Guardar Cambios</button>
      </div>
    </form>

  </div>
</div>

<?php include 'footer.php'; ?>
