<?php
/**
 * Archivo: gestionMembresias.php
 * Descripción: Creación, edición y administración de los planes de membresías.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) {
  header("Location: login.php");
  exit();
}
include 'header.php';

// ── DELETE ───────────────────────────────────────────────
if (isset($_GET['eliminar'])) {
  $id = (int) $_GET['eliminar'];
  $check = $conexion->query("SELECT COUNT(*) as t FROM socios WHERE id_membresia = $id")->fetch_assoc()['t'];
  if ($check > 0) {
    $msg = "<div class='alert alert-danger'><i class='ti ti-alert-circle me-1'></i>No se puede eliminar: hay <strong>$check socios</strong> con este plan asignado. Desactívalo en su lugar.</div>";
  } else {
    $conexion->query("DELETE FROM membresias WHERE id_membresia = $id");
    echo "<script>window.location='gestionMembresias.php?res=eliminado';</script>";
    exit;
  }
}

// ── TOGGLE ESTADO ────────────────────────────────────────
if (isset($_GET['toggle'])) {
  $id = (int) $_GET['toggle'];
  $row = $conexion->query("SELECT estado FROM membresias WHERE id_membresia=$id")->fetch_assoc();
  $nuevo = ($row['estado'] === 'activo') ? 'inactivo' : 'activo';
  $conexion->query("UPDATE membresias SET estado='$nuevo' WHERE id_membresia=$id");
  echo "<script>window.location='gestionMembresias.php?res=actualizado';</script>";
  exit;
}

// ── CREATE / UPDATE ──────────────────────────────────────
if ($_POST) {
  $nom = $conexion->real_escape_string($_POST['nombre']);
  $dur = (int) $_POST['duracion_meses'];
  $pre = (float) $_POST['precio'];
  $desc = $conexion->real_escape_string($_POST['descripcion']);
  $est = $conexion->real_escape_string($_POST['estado']);

  if (!empty($_POST['id_membresia'])) {
    $id = (int) $_POST['id_membresia'];
    $conexion->query("UPDATE membresias SET nombre='$nom', duracion_meses=$dur, precio=$pre, descripcion='$desc', estado='$est' WHERE id_membresia=$id");
    echo "<script>window.location='gestionMembresias.php?res=actualizado';</script>";
    exit;
  } else {
    $conexion->query("INSERT INTO membresias (nombre, duracion_meses, precio, descripcion, estado) VALUES ('$nom', $dur, $pre, '$desc', '$est')");
    echo "<script>window.location='gestionMembresias.php?res=creado';</script>";
    exit;
  }
}

// ── CARGAR PARA EDITAR ───────────────────────────────────
$edit = null;
if (isset($_GET['editar'])) {
  $id = (int) $_GET['editar'];
  $edit = $conexion->query("SELECT * FROM membresias WHERE id_membresia=$id")->fetch_assoc();
}

$planes = $conexion->query("SELECT m.*, (SELECT COUNT(*) FROM socios s WHERE s.id_membresia = m.id_membresia) as total_socios FROM membresias m ORDER BY precio ASC");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Planes de Membresía</h2>
        <p class="page-subtitle">Administra los planes disponibles del gimnasio.</p>
      </div>
      <div class="col-auto">
        <a href="gestionMembresias.php" class="btn btn-outline"><i class="ti ti-refresh me-1"></i>Ver todos</a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
      <div class="alert <?php echo $_GET['res'] === 'eliminado' ? 'alert-danger' : 'alert-success'; ?>">
        <?php
        $msgs = ['creado' => 'Plan creado correctamente.', 'actualizado' => 'Plan actualizado.', 'eliminado' => 'Plan eliminado.'];
        echo $msgs[$_GET['res']] ?? 'Operación realizada.';
        ?>
      </div>
    <?php endif; ?>
    <?php if (isset($msg))
      echo $msg; ?>

    <div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

      <!-- LISTA ──────────────────────────────────────── -->
      <div class="card">
        <div class="card-header gray">
          <h3 class="card-title">Planes Registrados</h3>
        </div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr>
                <th>Plan</th>
                <th>Duración</th>
                <th>Precio</th>
                <th>Socios</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($p = $planes->fetch_assoc()): ?>
                <tr>
                  <td>
                    <div class="td-name"><?php echo htmlspecialchars($p['nombre']); ?></div>
                    <div class="td-muted small"><?php echo htmlspecialchars($p['descripcion']); ?></div>
                  </td>
                  <td>
                    <span class="badge badge-blue">
                      <?php echo $p['duracion_meses']; ?> mes<?php echo $p['duracion_meses'] != 1 ? 'es' : ''; ?>
                    </span>
                  </td>
                  <td class="fw-bold text-green font-oswald" style="font-size:1.05rem;">
                    $<?php echo number_format($p['precio'], 2); ?>
                  </td>
                  <td class="td-muted"><?php echo $p['total_socios']; ?> socios</td>
                  <td>
                    <span class="badge <?php echo $p['estado'] === 'activo' ? 'badge-green' : 'badge-gray'; ?>">
                      <?php echo strtoupper($p['estado']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="btn-list">
                      <a href="gestionMembresias.php?editar=<?php echo $p['id_membresia']; ?>" class="btn btn-icon edit"
                        title="Editar">
                        <i class="ti ti-edit"></i>
                      </a>
                      <a href="gestionMembresias.php?toggle=<?php echo $p['id_membresia']; ?>" class="btn btn-icon"
                        title="<?php echo $p['estado'] === 'activo' ? 'Desactivar' : 'Activar'; ?>">
                        <i class="ti ti-<?php echo $p['estado'] === 'activo' ? 'eye-off' : 'eye'; ?>"></i>
                      </a>
                      <a href="gestionMembresias.php?eliminar=<?php echo $p['id_membresia']; ?>" class="btn btn-icon"
                        title="Eliminar" onclick="return confirm('¿Eliminar este plan permanentemente?');">
                        <i class="ti ti-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FORMULARIO ─────────────────────────────────── -->
      <form method="POST" class="card">
        <div class="card-header" style="background:linear-gradient(135deg,#1565C0,#1976D2);">
          <span class="card-title" style="color:#fff;">
            <i class="ti ti-<?php echo $edit ? 'edit' : 'plus'; ?> me-2"></i>
            <?php echo $edit ? 'Editar Plan: ' . htmlspecialchars($edit['nombre']) : 'Nuevo Plan'; ?>
          </span>
        </div>
        <div class="card-body">
          <?php if ($edit): ?>
            <input type="hidden" name="id_membresia" value="<?php echo $edit['id_membresia']; ?>">
          <?php endif; ?>

          <div style="display:flex; flex-direction:column; gap:14px;">
            <div>
              <label class="form-label">Nombre del Plan</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Ej. Mensual Basic"
                value="<?php echo $edit ? htmlspecialchars($edit['nombre']) : ''; ?>">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
              <div>
                <label class="form-label">Duración (meses)</label>
                <input type="number" name="duracion_meses" class="form-control" min="1" required
                  value="<?php echo $edit ? $edit['duracion_meses'] : '1'; ?>">
              </div>
              <div>
                <label class="form-label">Precio ($)</label>
                <input type="number" step="0.01" name="precio" class="form-control" required
                  value="<?php echo $edit ? $edit['precio'] : ''; ?>">
              </div>
            </div>
            <div>
              <label class="form-label">Descripción</label>
              <input type="text" name="descripcion" class="form-control" placeholder="Breve descripción del plan"
                value="<?php echo $edit ? htmlspecialchars($edit['descripcion']) : ''; ?>">
            </div>
            <div>
              <label class="form-label">Estado</label>
              <select name="estado" class="form-select">
                <option value="activo" <?php if (!$edit || $edit['estado'] === 'activo')
                  echo 'selected'; ?>>Activo
                </option>
                <option value="inactivo" <?php if ($edit && $edit['estado'] === 'inactivo')
                  echo 'selected'; ?>>Inactivo
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="gestionMembresias.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-blue">
            <i class="ti ti-device-floppy me-1"></i><?php echo $edit ? 'Guardar Cambios' : 'Crear Plan'; ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>