<?php
/**
 * Archivo: ejercicios.php
 * Descripción: Gestión y listado de ejercicios individuales disponibles para armar rutinas.
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
  $uso = $conexion->query("SELECT COUNT(*) as t FROM rutina_ejercicio WHERE id_ejercicio=$id")->fetch_assoc()['t'];
  if ($uso > 0) {
    $msg = "<div class='alert alert-danger'>No se puede eliminar: este ejercicio está en uso en $uso rutina(s).</div>";
  } else {
    $conexion->query("DELETE FROM ejercicios WHERE id_ejercicio=$id");
    echo "<script>window.location='ejercicios.php?res=eliminado';</script>";
    exit;
  }
}

// ── CREATE / UPDATE ──────────────────────────────────────
if ($_POST) {
  $nom = $conexion->real_escape_string($_POST['nombre']);
  $grup = $conexion->real_escape_string($_POST['grupo_muscular']);
  $desc = $conexion->real_escape_string($_POST['descripcion']);

  if (!empty($_POST['id_ejercicio'])) {
    $id = (int) $_POST['id_ejercicio'];
    $conexion->query("UPDATE ejercicios SET nombre='$nom', grupo_muscular='$grup', descripcion='$desc' WHERE id_ejercicio=$id");
  } else {
    $conexion->query("INSERT INTO ejercicios (nombre, grupo_muscular, descripcion) VALUES ('$nom','$grup','$desc')");
  }
  echo "<script>window.location='ejercicios.php?res=guardado';</script>";
  exit;
}

// ── CARGAR PARA EDITAR ───────────────────────────────────
$edit = null;
if (isset($_GET['editar'])) {
  $id = (int) $_GET['editar'];
  $edit = $conexion->query("SELECT * FROM ejercicios WHERE id_ejercicio=$id")->fetch_assoc();
}

$grupos = ['Pecho', 'Espalda', 'Hombros', 'Bíceps', 'Tríceps', 'Antebrazos', 'Abdomen', 'Cuádriceps', 'Isquiotibiales', 'Glúteos', 'Pantorrillas', 'Cardio', 'Funcional', 'Otro'];
$ejercicios = $conexion->query("SELECT e.*, (SELECT COUNT(*) FROM rutina_ejercicio re WHERE re.id_ejercicio=e.id_ejercicio) as en_rutinas FROM ejercicios e ORDER BY grupo_muscular, nombre ASC");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Catálogo de Ejercicios</h2>
        <p class="page-subtitle">Base de ejercicios disponibles para armar rutinas.</p>
      </div>
      <div class="col-auto">
        <a href="rutinas.php" class="btn btn-outline"><i class="ti ti-list-check me-1"></i>Ver Rutinas</a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
      <div class="alert alert-success">✓
        <?php echo $_GET['res'] === 'eliminado' ? 'Ejercicio eliminado.' : 'Ejercicio guardado correctamente.'; ?></div>
    <?php endif; ?>
    <?php if (isset($msg))
      echo $msg; ?>

    <div style="display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start;">

      <!-- TABLA ──────────────────────────────────────── -->
      <div class="card">
        <div class="card-header gray">
          <h3 class="card-title">Ejercicios (<?php echo $ejercicios->num_rows; ?>)</h3>
        </div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr>
                <th>Ejercicio</th>
                <th>Grupo Muscular</th>
                <th>En Rutinas</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($ejercicios->num_rows === 0): ?>
                <tr>
                  <td colspan="4" style="text-align:center;padding:30px;color:var(--muted);">No hay ejercicios aún. Agrega
                    el primero.</td>
                </tr>
              <?php endif; ?>
              <?php while ($e = $ejercicios->fetch_assoc()): ?>
                <tr>
                  <td>
                    <div class="td-name"><?php echo htmlspecialchars($e['nombre']); ?></div>
                    <?php if ($e['descripcion']): ?>
                      <div class="td-muted small">
                        <?php echo htmlspecialchars(substr($e['descripcion'], 0, 60)); ?>    <?php echo strlen($e['descripcion']) > 60 ? '...' : ''; ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge badge-purple"><?php echo htmlspecialchars($e['grupo_muscular'] ?? '—'); ?></span>
                  </td>
                  <td class="td-muted"><?php echo $e['en_rutinas']; ?> rutina(s)</td>
                  <td>
                    <div class="btn-list">
                      <a href="ejercicios.php?editar=<?php echo $e['id_ejercicio']; ?>" class="btn btn-icon edit"
                        title="Editar"><i class="ti ti-edit"></i></a>
                      <a href="ejercicios.php?eliminar=<?php echo $e['id_ejercicio']; ?>" class="btn btn-icon"
                        title="Eliminar" onclick="return confirm('¿Eliminar este ejercicio?');"><i
                          class="ti ti-trash"></i></a>
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
        <div class="card-header" style="background:linear-gradient(135deg,#4A148C,#7B1FA2);">
          <span class="card-title" style="color:#fff;">
            <i class="ti ti-<?php echo $edit ? 'edit' : 'plus'; ?> me-2"></i>
            <?php echo $edit ? 'Editar: ' . htmlspecialchars($edit['nombre']) : 'Nuevo Ejercicio'; ?>
          </span>
        </div>
        <div class="card-body">
          <?php if ($edit): ?>
            <input type="hidden" name="id_ejercicio" value="<?php echo $edit['id_ejercicio']; ?>">
          <?php endif; ?>
          <div style="display:flex; flex-direction:column; gap:14px;">
            <div>
              <label class="form-label">Nombre del Ejercicio</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Ej. Press de banca"
                value="<?php echo $edit ? htmlspecialchars($edit['nombre']) : ''; ?>">
            </div>
            <div>
              <label class="form-label">Grupo Muscular</label>
              <select name="grupo_muscular" class="form-select">
                <option value="">— Selecciona —</option>
                <?php foreach ($grupos as $g): ?>
                  <option value="<?php echo $g; ?>" <?php if ($edit && $edit['grupo_muscular'] === $g)
                       echo 'selected'; ?>>
                    <?php echo $g; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Descripción / Técnica</label>
              <textarea name="descripcion" class="form-control" rows="3"
                placeholder="Descripción opcional del ejercicio..."><?php echo $edit ? htmlspecialchars($edit['descripcion']) : ''; ?></textarea>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="ejercicios.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-red">
            <i class="ti ti-device-floppy me-1"></i><?php echo $edit ? 'Guardar Cambios' : 'Agregar Ejercicio'; ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>