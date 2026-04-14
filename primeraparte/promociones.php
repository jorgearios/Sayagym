<?php
/**
 * Archivo: promociones.php
 * Descripción: Módulo para enviar o publicar avisos y promociones a los socios.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) {
  header("Location: login.php");
  exit();
}
include 'header.php';

if (isset($_GET['eliminar'])) {
  $conexion->query("DELETE FROM promociones WHERE id_promo=" . (int) $_GET['eliminar']);
  echo "<script>window.location='promociones.php?res=eliminada';</script>";
  exit;
}

if (isset($_GET['toggle'])) {
  $id = (int) $_GET['toggle'];
  $est = $conexion->query("SELECT estado FROM promociones WHERE id_promo=$id")->fetch_assoc()['estado'];
  $nuevo = ($est === 'activa') ? 'inactiva' : 'activa';
  $conexion->query("UPDATE promociones SET estado='$nuevo' WHERE id_promo=$id");
  echo "<script>window.location='promociones.php';</script>";
  exit;
}

if ($_POST) {
  $nom = $conexion->real_escape_string($_POST['nombre']);
  $tipo = $conexion->real_escape_string($_POST['tipo']);
  $val = (float) $_POST['valor'];
  $fi = $conexion->real_escape_string($_POST['fecha_inicio']);
  $ff = $conexion->real_escape_string($_POST['fecha_fin']);
  $est = $conexion->real_escape_string($_POST['estado']);

  if (!empty($_POST['id_promo'])) {
    $id = (int) $_POST['id_promo'];
    $conexion->query("UPDATE promociones SET nombre='$nom', tipo='$tipo', valor=$val, fecha_inicio='$fi', fecha_fin='$ff', estado='$est' WHERE id_promo=$id");
  } else {
    $conexion->query("INSERT INTO promociones (nombre, tipo, valor, fecha_inicio, fecha_fin, estado) VALUES ('$nom','$tipo',$val,'$fi','$ff','$est')");
  }
  echo "<script>window.location='promociones.php?res=guardada';</script>";
  exit;
}

$edit = null;
if (isset($_GET['editar'])) {
  $edit = $conexion->query("SELECT * FROM promociones WHERE id_promo=" . (int) $_GET['editar'])->fetch_assoc();
}

$hoy = date('Y-m-d');
$promos = $conexion->query("SELECT * FROM promociones ORDER BY fecha_fin DESC");
?>
<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Promociones y Descuentos</h2>
        <p class="page-subtitle">Gestión de promociones aplicables en el punto de venta.</p>
      </div>
      <div class="col-auto">
        <a href="pos.php" class="btn btn-outline"><i class="ti ti-arrow-left me-1"></i>Volver al POS</a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
      <div class="alert alert-success">Promoción
        <?php echo $_GET['res'] === 'eliminada' ? 'eliminada.' : 'guardada correctamente.'; ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

      <!-- LISTA ────────────────────────────────────────── -->
      <div class="card">
        <div class="card-header gray">
          <h3 class="card-title">Promociones registradas</h3>
        </div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Descuento</th>
                <th>Vigencia</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($promos->num_rows === 0): ?>
                <tr>
                  <td colspan="6" style="text-align:center;padding:30px;color:var(--muted);">Sin promociones. Crea la
                    primera.</td>
                </tr>
              <?php endif; ?>
              <?php while ($p = $promos->fetch_assoc()):
                $vigente = ($p['fecha_inicio'] <= $hoy && $p['fecha_fin'] >= $hoy && $p['estado'] === 'activa');
                ?>
                <tr>
                  <td class="td-name"><?php echo htmlspecialchars($p['nombre']); ?></td>
                  <td class="td-muted"><?php echo $p['tipo'] === 'porcentaje' ? 'Porcentaje' : 'Monto fijo'; ?></td>
                  <td class="fw-bold" style="color:var(--red);">
                    <?php echo $p['tipo'] === 'porcentaje' ? $p['valor'] . '%' : '$' . number_format($p['valor'], 2); ?>
                  </td>
                  <td class="td-muted"><?php echo date('d/m/Y', strtotime($p['fecha_inicio'])); ?> –
                    <?php echo date('d/m/Y', strtotime($p['fecha_fin'])); ?></td>
                  <td>
                    <span
                      class="badge <?php echo $vigente ? 'badge-green' : ($p['estado'] === 'activa' ? 'badge-gold' : 'badge-gray'); ?>">
                      <?php echo $vigente ? 'VIGENTE' : strtoupper($p['estado']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="btn-list">
                      <a href="promociones.php?editar=<?php echo $p['id_promo']; ?>" class="btn btn-icon edit"
                        title="Editar"><i class="ti ti-edit"></i></a>
                      <a href="promociones.php?toggle=<?php echo $p['id_promo']; ?>" class="btn btn-icon"
                        title="<?php echo $p['estado'] === 'activa' ? 'Desactivar' : 'Activar'; ?>">
                        <i class="ti ti-<?php echo $p['estado'] === 'activa' ? 'eye-off' : 'eye'; ?>"></i>
                      </a>
                      <a href="promociones.php?eliminar=<?php echo $p['id_promo']; ?>" class="btn btn-icon"
                        title="Eliminar" onclick="return confirm('¿Eliminar esta promoción?');"><i
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
      <form method="POST" class="card" style="position:sticky;top:80px;">
        <div class="card-header" style="background:linear-gradient(135deg,#B45309,var(--gold));">
          <span class="card-title" style="color:#fff;">
            <i class="ti ti-<?php echo $edit ? 'edit' : 'tag'; ?> me-2"></i>
            <?php echo $edit ? 'Editar Promoción' : 'Nueva Promoción'; ?>
          </span>
        </div>
        <div class="card-body">
          <?php if ($edit): ?>
            <input type="hidden" name="id_promo" value="<?php echo $edit['id_promo']; ?>">
          <?php endif; ?>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
              <label class="form-label">Nombre de la promoción</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Ej. Descuento de bienvenida"
                value="<?php echo $edit ? htmlspecialchars($edit['nombre']) : ''; ?>">
            </div>
            <div>
              <label class="form-label">Tipo de descuento</label>
              <select name="tipo" class="form-select" id="sel-tipo" onchange="cambiarTipo()">
                <option value="porcentaje" <?php if (!$edit || $edit['tipo'] === 'porcentaje')
                  echo 'selected'; ?>>
                  Porcentaje (%)</option>
                <option value="monto_fijo" <?php if ($edit && $edit['tipo'] === 'monto_fijo')
                  echo 'selected'; ?>>Monto
                  fijo ($)</option>
              </select>
            </div>
            <div>
              <label class="form-label" id="lbl-valor">Valor del descuento (%)</label>
              <input type="number" step="0.01" name="valor" class="form-control" required min="0"
                value="<?php echo $edit ? $edit['valor'] : ''; ?>" placeholder="0">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <div>
                <label class="form-label">Fecha inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" required
                  value="<?php echo $edit ? $edit['fecha_inicio'] : date('Y-m-d'); ?>">
              </div>
              <div>
                <label class="form-label">Fecha fin</label>
                <input type="date" name="fecha_fin" class="form-control" required
                  value="<?php echo $edit ? $edit['fecha_fin'] : date('Y-m-d', strtotime('+30 days')); ?>">
              </div>
            </div>
            <div>
              <label class="form-label">Estado</label>
              <select name="estado" class="form-select">
                <option value="activa" <?php if (!$edit || $edit['estado'] === 'activa')
                  echo 'selected'; ?>>Activa
                </option>
                <option value="inactiva" <?php if ($edit && $edit['estado'] === 'inactiva')
                  echo 'selected'; ?>>Inactiva
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="promociones.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-gold" style="color:#1a1a1a;">
            <i class="ti ti-device-floppy me-1"></i><?php echo $edit ? 'Guardar' : 'Crear Promoción'; ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
  function cambiarTipo() {
    const tipo = document.getElementById('sel-tipo').value;
    document.getElementById('lbl-valor').textContent =
      tipo === 'porcentaje' ? 'Valor del descuento (%)' : 'Monto fijo de descuento ($)';
  }
<?php if ($edit): ?>cambiarTipo(); <?php endif; ?>
</script>
<?php include 'footer.php'; ?>