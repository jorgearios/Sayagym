<?php 
include 'config.php';
include 'header.php'; 

if ($_POST) {
    $nom   = $_POST['nombre'];
    $esp   = $_POST['especialidad'];
    $tel   = $_POST['telefono'];
    $cor   = $_POST['correo'];
    $f_con = $_POST['fecha_contratacion'];
    $com   = $_POST['tarifa_comision'];
    $tur   = $_POST['turno'];

    $sql = "INSERT INTO entrenadores (nombre, especialidad, telefono, correo, fecha_contratacion, tarifa_comision, turno, estado) 
            VALUES ('$nom', '$esp', '$tel', '$cor', '$f_con', '$com', '$tur', 'activo')";
    
    if ($conexion->query($sql)) {
        echo "<script>window.location='entrenadores.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conexion->error . "</div>";
    }
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div style="display:grid; grid-template-columns: 1fr 340px; gap:24px; align-items:start;">

      <!-- FORM CARD -->
      <form method="POST" class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #4A148C, #7B1FA2);">
          <span class="card-title" style="color:#fff;">
            <i class="ti ti-barbell me-2"></i>Registro de Entrenador
          </span>
        </div>
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
              <label class="form-label">Nombre Completo</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Ej. Carlos Méndez">
            </div>
            <div>
              <label class="form-label">Especialidad</label>
              <input type="text" name="especialidad" class="form-control" placeholder="Ej. Pesas / Funcional">
            </div>
            <div>
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control" placeholder="834 000 0000">
            </div>
            <div>
              <label class="form-label">Correo Electrónico</label>
              <input type="email" name="correo" class="form-control" placeholder="coach@ejemplo.com">
            </div>
            <div>
              <label class="form-label">Fecha de Contratación</label>
              <input type="date" name="fecha_contratacion" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div>
              <label class="form-label">Turno</label>
              <select name="turno" class="form-select">
                <option value="Matutino">Matutino</option>
                <option value="Vespertino">Vespertino</option>
                <option value="Completo">Completo</option>
              </select>
            </div>
            <div style="grid-column:1/-1;">
              <label class="form-label">Comisión por Socio ($)</label>
              <input type="number" step="0.01" name="tarifa_comision" class="form-control" placeholder="0.00">
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="entrenadores.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-red"><i class="ti ti-check me-1"></i>Dar de Alta</button>
        </div>
      </form>

      <!-- SIDE PANEL -->
      <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="motive-panel">
          <div class="line1">Forma<br>a los mejores</div>
          <div class="line2">Cada coach cuenta</div>
          <div style="font-family:'Oswald',sans-serif; font-size:1.2rem; color:#F5A623; margin-top:8px; letter-spacing:1px;">
            ¡EL EQUIPO ES LA FUERZA!
          </div>
        </div>
        <div class="card" style="border-left: 4px solid #7B1FA2;">
          <div class="card-body" style="padding:18px;">
            <p style="font-size:0.8rem; color:#6D28D9; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
              Comisiones
            </p>
            <p style="font-size:0.85rem; color:#4B5563; line-height:1.6;">
              La comisión registrada se multiplica automáticamente por el número de socios asignados a este entrenador.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
