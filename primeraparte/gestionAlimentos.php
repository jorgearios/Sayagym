<?php
/**
 * Archivo: gestionAlimentos.php
 * Descripción: Administración del catálogo de alimentos para el módulo de nutrición.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) {
  header("Location: login.php");
  exit();
}
include 'header.php';

// ── DELETE ────────────────────────────────────────────────
if (isset($_GET['eliminar'])) {
  $id = (int) $_GET['eliminar'];
  $en_uso = $conexion->query("SELECT COUNT(*) as t FROM consumo_detalle WHERE id_alimento=$id")->fetch_assoc()['t'];
  if ($en_uso > 0) {
    $msg = "<div class='alert alert-danger'><i class='ti ti-alert-circle me-1'></i>No se puede eliminar: el alimento está en $en_uso registro(s) de consumo.</div>";
  } else {
    $conexion->query("DELETE FROM alimentos_calorias WHERE id_alimento=$id");
    echo "<script>window.location='gestionAlimentos.php?res=eliminado';</script>";
    exit;
  }
}

// ── TOGGLE ESTADO ─────────────────────────────────────────
if (isset($_GET['toggle'])) {
  $id = (int) $_GET['toggle'];
  $row = $conexion->query("SELECT estado FROM alimentos_calorias WHERE id_alimento=$id")->fetch_assoc();
  $nuevo = ($row['estado'] === 'activo') ? 'inactivo' : 'activo';
  $conexion->query("UPDATE alimentos_calorias SET estado='$nuevo' WHERE id_alimento=$id");
  echo "<script>window.location='gestionAlimentos.php';</script>";
  exit;
}

// ── GUARDAR ───────────────────────────────────────────────
if ($_POST) {
  $nom = $conexion->real_escape_string(trim($_POST['nombre']));
  $cat = $conexion->real_escape_string(trim($_POST['categoria']));
  $cal = (int) $_POST['calorias_100g'];
  $est = 'activo';

  if (!empty($_POST['id_alimento'])) {
    $id = (int) $_POST['id_alimento'];
    $conexion->query("UPDATE alimentos_calorias SET nombre='$nom', categoria='$cat', calorias_100g=$cal WHERE id_alimento=$id");
  } else {
    $conexion->query("INSERT INTO alimentos_calorias (categoria, nombre, calorias_100g, estado) VALUES ('$cat','$nom',$cal,'$est')");
  }
  echo "<script>window.location='gestionAlimentos.php?res=guardado';</script>";
  exit;
}

// ── CARGAR PARA EDITAR ────────────────────────────────────
$edit = null;
if (isset($_GET['editar'])) {
  $id = (int) $_GET['editar'];
  $edit = $conexion->query("SELECT * FROM alimentos_calorias WHERE id_alimento=$id")->fetch_assoc();
}

// ── FILTRO / PAGINACIÓN ───────────────────────────────────
$filtro_cat = $conexion->real_escape_string($_GET['cat'] ?? '');
$filtro_bus = $conexion->real_escape_string($_GET['bus'] ?? '');
$where = "WHERE 1=1";
if ($filtro_cat)
  $where .= " AND categoria='$filtro_cat'";
if ($filtro_bus)
  $where .= " AND nombre LIKE '%$filtro_bus%'";

$total = $conexion->query("SELECT COUNT(*) as t FROM alimentos_calorias $where")->fetch_assoc()['t'];
$pag = max(1, (int) ($_GET['p'] ?? 1));
$limit = 20;
$offset = ($pag - 1) * $limit;
$pages = ceil($total / $limit);

$alimentos = $conexion->query("SELECT * FROM alimentos_calorias $where ORDER BY categoria, nombre ASC LIMIT $limit OFFSET $offset");
$categorias = $conexion->query("SELECT DISTINCT categoria FROM alimentos_calorias ORDER BY categoria ASC");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Catálogo de Alimentos</h2>
        <p class="page-subtitle">306 alimentos disponibles — calorías por cada 100g.</p>
      </div>
      <div class="col-auto">
        <a href="evaluaciones.php" class="btn btn-outline"><i class="ti ti-arrow-left me-1"></i>Evaluaciones</a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
      <div class="alert alert-success">
        <?php echo $_GET['res'] === 'eliminado' ? 'Alimento eliminado.' : 'Alimento guardado correctamente.'; ?>
      </div>
    <?php endif; ?>
    <?php if (isset($msg))
      echo $msg; ?>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start;">

      <!-- LISTA ──────────────────────────────────────────── -->
      <div>
        <!-- Filtros -->
        <form method="GET" style="display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap;">
          <input type="text" name="bus" class="form-control" placeholder="Buscar alimento..."
            value="<?php echo htmlspecialchars($filtro_bus); ?>" style="max-width:220px;">
          <select name="cat" class="form-select" style="max-width:230px;" onchange="this.form.submit()">
            <option value="">— Todas las categorías —</option>
            <?php while ($c = $categorias->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($c['categoria']); ?>" <?php if ($filtro_cat === $c['categoria'])
                   echo 'selected'; ?>>
                <?php echo htmlspecialchars($c['categoria']); ?>
              </option>
            <?php endwhile; ?>
          </select>
          <button type="submit" class="btn btn-outline"><i class="ti ti-search"></i></button>
          <?php if ($filtro_cat || $filtro_bus): ?>
            <a href="gestionAlimentos.php" class="btn btn-link">Limpiar</a>
          <?php endif; ?>
        </form>

        <div class="card">
          <div class="card-header gray">
            <h3 class="card-title">Alimentos (<?php echo $total; ?>)</h3>
            <span style="font-size:0.78rem; color:var(--muted);">Página <?php echo $pag; ?> de
              <?php echo $pages; ?></span>
          </div>
          <div class="table-responsive">
            <table class="gym-table">
              <thead>
                <tr>
                  <th>Alimento</th>
                  <th>Categoría</th>
                  <th>kcal/100g</th>
                  <th>Estado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($a = $alimentos->fetch_assoc()): ?>
                  <tr>
                    <td class="td-name"><?php echo htmlspecialchars($a['nombre']); ?></td>
                    <td><span class="badge badge-blue"
                        style="font-size:0.68rem;"><?php echo htmlspecialchars($a['categoria']); ?></span></td>
                    <td>
                      <span class="fw-bold font-oswald"
                        style="font-size:1rem; color:<?php echo $a['calorias_100g'] >= 400 ? 'var(--red)' : ($a['calorias_100g'] >= 200 ? 'var(--gold-dark)' : 'var(--green)'); ?>">
                        <?php echo $a['calorias_100g']; ?>
                      </span>
                      <span style="font-size:0.72rem; color:var(--muted);"> kcal</span>
                    </td>
                    <td>
                      <span class="badge <?php echo $a['estado'] === 'activo' ? 'badge-green' : 'badge-gray'; ?>">
                        <?php echo strtoupper($a['estado']); ?>
                      </span>
                    </td>
                    <td>
                      <div class="btn-list">
                        <a href="gestionAlimentos.php?editar=<?php echo $a['id_alimento']; ?><?php echo $filtro_cat ? "&cat=" . urlencode($filtro_cat) : ''; ?><?php echo $filtro_bus ? "&bus=" . urlencode($filtro_bus) : ''; ?>"
                          class="btn btn-icon edit" title="Editar"><i class="ti ti-edit"></i></a>
                        <a href="gestionAlimentos.php?toggle=<?php echo $a['id_alimento']; ?>" class="btn btn-icon"
                          title="<?php echo $a['estado'] === 'activo' ? 'Desactivar' : 'Activar'; ?>"><i
                            class="ti ti-<?php echo $a['estado'] === 'activo' ? 'eye-off' : 'eye'; ?>"></i></a>
                        <a href="gestionAlimentos.php?eliminar=<?php echo $a['id_alimento']; ?>" class="btn btn-icon"
                          title="Eliminar" onclick="return confirm('¿Eliminar este alimento?');"><i
                            class="ti ti-trash"></i></a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <!-- Paginación -->
          <?php if ($pages > 1): ?>
            <div class="card-footer" style="justify-content:center; gap:6px;">
              <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?p=<?php echo $i; ?>&cat=<?php echo urlencode($filtro_cat); ?>&bus=<?php echo urlencode($filtro_bus); ?>"
                  style="padding:5px 12px; border-radius:4px; font-size:0.82rem; text-decoration:none;
                      background:<?php echo $i === $pag ? 'var(--red)' : 'var(--border)'; ?>;
                      color:<?php echo $i === $pag ? '#fff' : 'var(--text)'; ?>;"><?php echo $i; ?></a>
              <?php endfor; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- FORMULARIO ─────────────────────────────────────── -->
      <form method="POST" class="card" style="position:sticky; top:80px;">
        <div class="card-header" style="background:linear-gradient(135deg,#1565C0,#1976D2);">
          <span class="card-title" style="color:#fff;">
            <i class="ti ti-<?php echo $edit ? 'edit' : 'plus'; ?> me-2"></i>
            <?php echo $edit ? 'Editar: ' . htmlspecialchars($edit['nombre']) : 'Nuevo Alimento'; ?>
          </span>
        </div>
        <div class="card-body">
          <?php if ($edit): ?>
            <input type="hidden" name="id_alimento" value="<?php echo $edit['id_alimento']; ?>">
          <?php endif; ?>
          <div style="display:flex; flex-direction:column; gap:14px;">
            <div>
              <label class="form-label">Nombre del Alimento</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Ej. Pechuga de pollo"
                value="<?php echo $edit ? htmlspecialchars($edit['nombre']) : ''; ?>">
            </div>
            <div>
              <label class="form-label">Categoría</label>
              <input type="text" name="categoria" class="form-control" list="cats-list" required
                placeholder="Ej. CARNES, CAZA Y EMBUTIDOS"
                value="<?php echo $edit ? htmlspecialchars($edit['categoria']) : ''; ?>">
              <datalist id="cats-list">
                <?php
                $conexion->query("SELECT DISTINCT categoria FROM alimentos_calorias ORDER BY categoria ASC")->data_seek(0) ?? null;
                $cats2 = $conexion->query("SELECT DISTINCT categoria FROM alimentos_calorias ORDER BY categoria ASC");
                while ($c2 = $cats2->fetch_assoc()): ?>
                  <option value="<?php echo htmlspecialchars($c2['categoria']); ?>">
                  <?php endwhile; ?>
              </datalist>
            </div>
            <div>
              <label class="form-label">Calorías por 100g (kcal)</label>
              <input type="number" name="calorias_100g" class="form-control" required min="0" max="9999"
                value="<?php echo $edit ? $edit['calorias_100g'] : ''; ?>" placeholder="0">
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="gestionAlimentos.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-blue"><i
              class="ti ti-device-floppy me-1"></i><?php echo $edit ? 'Guardar Cambios' : 'Agregar'; ?></button>
        </div>
      </form>

    </div>
  </div>
</div>
<?php include 'footer.php'; ?>