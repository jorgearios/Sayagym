<?php 
include 'config.php';
include 'header.php'; 

$e = null;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $resultado = $conexion->query("SELECT * FROM entrenadores WHERE id_entrenador = $id");
    $e = $resultado->fetch_assoc();
    if (!$e) {
        echo "<script>window.location='entrenadores.php';</script>";
        exit;
    }
}

if (!$e && !$_POST) {
    echo "<script>window.location='entrenadores.php';</script>";
    exit;
}

if ($_POST) {
    $id_ent = (int)$_POST['id_entrenador'];
    $nom = $conexion->real_escape_string($_POST['nombre']);
    $esp = $conexion->real_escape_string($_POST['especialidad']);
    $tel = $conexion->real_escape_string($_POST['telefono']);
    $cor = $conexion->real_escape_string($_POST['correo']);
    $com = (float)$_POST['tarifa_comision'];
    $tur = $conexion->real_escape_string($_POST['turno']);
    $est = $conexion->real_escape_string($_POST['estado']);

    $sql = "UPDATE entrenadores SET 
            nombre = '$nom', especialidad = '$esp', telefono = '$tel', 
            correo = '$cor', tarifa_comision = '$com', turno = '$tur', estado = '$est'
            WHERE id_entrenador = $id_ent";
    
    if ($conexion->query($sql)) {
        echo "<script>window.location='entrenadores.php?res=editado';</script>";
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conexion->error . "</div>";
    }

    // Recargar datos del entrenador después de intentar guardar
    $resultado = $conexion->query("SELECT * FROM entrenadores WHERE id_entrenador = $id_ent");
    $e = $resultado->fetch_assoc();
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <form method="POST" class="card" style="max-width:760px; margin:0 auto;">
      <div class="card-header" style="background: linear-gradient(135deg, #4A148C, #7B1FA2);">
        <span class="card-title" style="color:#fff;">
          <i class="ti ti-barbell me-2"></i>Editar Entrenador — <?php echo htmlspecialchars($e['nombre']); ?>
        </span>
      </div>
      <div class="card-body">
        <input type="hidden" name="id_entrenador" value="<?php echo $e['id_entrenador']; ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          <div>
            <label class="form-label">Nombre Completo</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($e['nombre']); ?>" required>
          </div>
          <div>
            <label class="form-label">Especialidad</label>
            <input type="text" name="especialidad" class="form-control" value="<?php echo htmlspecialchars($e['especialidad']); ?>">
          </div>
          <div>
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($e['telefono']); ?>">
          </div>
          <div>
            <label class="form-label">Correo Electrónico</label>
            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($e['correo']); ?>">
          </div>
          <div>
            <label class="form-label">Turno</label>
            <select name="turno" class="form-select">
              <option value="Matutino"   <?php if($e['turno']=='Matutino')   echo 'selected'; ?>>Matutino</option>
              <option value="Vespertino" <?php if($e['turno']=='Vespertino') echo 'selected'; ?>>Vespertino</option>
              <option value="Completo"   <?php if($e['turno']=='Completo')   echo 'selected'; ?>>Completo</option>
            </select>
          </div>
          <div>
            <label class="form-label">Comisión por Socio ($)</label>
            <input type="number" step="0.01" name="tarifa_comision" class="form-control" value="<?php echo $e['tarifa_comision']; ?>">
          </div>
          <div style="grid-column:1/-1;">
            <label class="form-label">Estado Laboral</label>
            <select name="estado" class="form-select">
              <option value="activo"   <?php if($e['estado']=='activo')   echo 'selected'; ?>>Activo (En nómina)</option>
              <option value="inactivo" <?php if($e['estado']=='inactivo') echo 'selected'; ?>>Inactivo (Baja)</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <a href="entrenadores.php" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-red"><i class="ti ti-device-floppy me-1"></i>Actualizar Perfil</button>
      </div>
    </form>

  </div>
</div>

<?php include 'footer.php'; ?>
