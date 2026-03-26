<?php
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$id_rutina = isset($_GET['id_rutina']) ? (int)$_GET['id_rutina'] : 0;
$id_socio  = isset($_GET['id_socio'])  ? (int)$_GET['id_socio']  : 0;

// ── DESASIGNAR ───────────────────────────────────────────
if (isset($_GET['desasignar'])) {
    $id_a = (int)$_GET['desasignar'];
    $conexion->query("DELETE FROM socio_rutina WHERE id_asignacion=$id_a");
    $back = $id_rutina ? "asignarRutina.php?id_rutina=$id_rutina" : "asignarRutina.php?id_socio=$id_socio";
    echo "<script>window.location='$back&res=desasignado';</script>"; exit;
}

// ── ASIGNAR ──────────────────────────────────────────────
if ($_POST) {
    $idr = (int)$_POST['id_rutina'];
    $ids = (int)$_POST['id_socio'];
    $fi  = $conexion->real_escape_string($_POST['fecha_inicio']);
    $ff  = !empty($_POST['fecha_fin']) ? "'".$conexion->real_escape_string($_POST['fecha_fin'])."'" : "NULL";
    $conexion->query("INSERT INTO socio_rutina (id_socio, id_rutina, fecha_inicio, fecha_fin) VALUES ($ids, $idr, '$fi', $ff)");
    $back = $idr ? "asignarRutina.php?id_rutina=$idr" : "asignarRutina.php?id_socio=$ids";
    echo "<script>window.location='$back&res=asignado';</script>"; exit;
}

// ── DATOS CONTEXTO ───────────────────────────────────────
$rutina = $id_rutina ? $conexion->query("SELECT * FROM rutinas WHERE id_rutina=$id_rutina")->fetch_assoc() : null;
$socio  = $id_socio  ? $conexion->query("SELECT id_socio, nombre, apellido FROM socios WHERE id_socio=$id_socio")->fetch_assoc() : null;

// Asignaciones existentes
$where_asig = $id_rutina ? "sr.id_rutina=$id_rutina" : "sr.id_socio=$id_socio";
$asignaciones = $conexion->query("
    SELECT sr.*, s.nombre, s.apellido, r.nombre_rutina
    FROM socio_rutina sr
    JOIN socios s ON sr.id_socio = s.id_socio
    JOIN rutinas r ON sr.id_rutina = r.id_rutina
    WHERE $where_asig
    ORDER BY sr.fecha_inicio DESC
");

$socios_lista  = $conexion->query("SELECT id_socio, nombre, apellido FROM socios WHERE estado='activo' ORDER BY nombre ASC");
$rutinas_lista = $conexion->query("SELECT id_rutina, nombre_rutina, nivel FROM rutinas ORDER BY nombre_rutina ASC");

$titulo = $rutina ? 'Asignar Rutina: '.htmlspecialchars($rutina['nombre_rutina']) : ($socio ? 'Rutinas de '.htmlspecialchars($socio['nombre'].' '.$socio['apellido']) : 'Asignar Rutina');
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title"><?php echo $titulo; ?></h2>
        <p class="page-subtitle">Asigna o gestiona rutinas para los socios.</p>
      </div>
      <div class="col-auto">
        <a href="rutinas.php" class="btn btn-outline"><i class="ti ti-arrow-left me-1"></i>Volver a Rutinas</a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
    <div class="alert alert-success">✓ <?php echo $_GET['res']==='asignado' ? 'Rutina asignada correctamente.' : 'Asignación eliminada.'; ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start;">

      <!-- ASIGNACIONES ACTUALES ───────────────────────── -->
      <div class="card">
        <div class="card-header gray"><h3 class="card-title">Asignaciones Actuales</h3></div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr>
                <?php if ($id_rutina): ?><th>Socio</th><?php else: ?><th>Rutina</th><?php endif; ?>
                <th>Inicio</th><th>Fin</th><th>Estado</th><th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($asignaciones->num_rows === 0): ?>
              <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--muted);">Sin asignaciones aún.</td></tr>
              <?php endif; ?>
              <?php while ($a = $asignaciones->fetch_assoc()):
                $hoy = date('Y-m-d');
                $activa = (!$a['fecha_fin'] || $a['fecha_fin'] >= $hoy);
              ?>
              <tr>
                <td class="td-name"><?php echo $id_rutina ? htmlspecialchars($a['nombre'].' '.$a['apellido']) : htmlspecialchars($a['nombre_rutina']); ?></td>
                <td class="td-muted"><?php echo date('d/m/Y', strtotime($a['fecha_inicio'])); ?></td>
                <td class="td-muted"><?php echo $a['fecha_fin'] ? date('d/m/Y', strtotime($a['fecha_fin'])) : '—'; ?></td>
                <td><span class="badge <?php echo $activa ? 'badge-green' : 'badge-gray'; ?>"><?php echo $activa ? 'Activa' : 'Terminada'; ?></span></td>
                <td>
                  <a href="asignarRutina.php?desasignar=<?php echo $a['id_asignacion']; ?>&id_rutina=<?php echo $id_rutina; ?>&id_socio=<?php echo $id_socio; ?>"
                     class="btn btn-icon" title="Quitar asignación"
                     onclick="return confirm('¿Quitar esta asignación?');">
                    <i class="ti ti-unlink"></i>
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FORMULARIO NUEVA ASIGNACIÓN ────────────────── -->
      <form method="POST" class="card">
        <div class="card-header" style="background:linear-gradient(135deg,#4A148C,#7B1FA2);">
          <span class="card-title" style="color:#fff;"><i class="ti ti-user-plus me-2"></i>Nueva Asignación</span>
        </div>
        <div class="card-body">
          <div style="display:flex; flex-direction:column; gap:14px;">
            <div>
              <label class="form-label">Socio</label>
              <select name="id_socio" class="form-select" required>
                <option value="">— Seleccionar socio —</option>
                <?php
                $socios_lista->data_seek(0);
                while ($s = $socios_lista->fetch_assoc()):
                  $sel = ($id_socio == $s['id_socio']) ? 'selected' : '';
                ?>
                <option value="<?php echo $s['id_socio']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($s['nombre'].' '.$s['apellido']); ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Rutina</label>
              <select name="id_rutina" class="form-select" required>
                <option value="">— Seleccionar rutina —</option>
                <?php
                $rutinas_lista->data_seek(0);
                while ($r2 = $rutinas_lista->fetch_assoc()):
                  $sel = ($id_rutina == $r2['id_rutina']) ? 'selected' : '';
                ?>
                <option value="<?php echo $r2['id_rutina']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($r2['nombre_rutina']); ?> (<?php echo $r2['nivel']; ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Fecha de Inicio</label>
              <input type="date" name="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div>
              <label class="form-label">Fecha de Fin (opcional)</label>
              <input type="date" name="fecha_fin" class="form-control">
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-red" style="width:100%; justify-content:center;">
            <i class="ti ti-link me-1"></i>Asignar Rutina
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
