<?php
/**
 * Archivo: inventario.php
 * Descripción: Gestión de productos y artículos disponibles en el gimnasio.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

// ── DELETE ────────────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id  = (int)$_GET['eliminar'];
    $uso = $conexion->query("SELECT COUNT(*) as t FROM venta_detalle WHERE id_producto=$id")->fetch_assoc()['t'];
    if ($uso > 0) {
        $msg_err = "No se puede eliminar: el producto aparece en $uso venta(s). Desactívalo en su lugar.";
    } else {
        $conexion->query("DELETE FROM productos WHERE id_producto=$id");
        echo "<script>window.location='inventario.php?res=eliminado';</script>"; exit;
    }
}

// ── TOGGLE ESTADO ─────────────────────────────────────────
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $est = $conexion->query("SELECT estado FROM productos WHERE id_producto=$id")->fetch_assoc()['estado'];
    $nuevo = ($est === 'activo') ? 'inactivo' : 'activo';
    $conexion->query("UPDATE productos SET estado='$nuevo' WHERE id_producto=$id");
    echo "<script>window.location='inventario.php';</script>"; exit;
}

// ── GUARDAR ───────────────────────────────────────────────
if ($_POST) {
    $nom  = $conexion->real_escape_string(trim($_POST['nombre']));
    $cat  = $conexion->real_escape_string($_POST['categoria']);
    $desc = $conexion->real_escape_string($_POST['descripcion'] ?? '');
    $pc   = (float)$_POST['precio_costo'];
    $pv   = (float)$_POST['precio_venta'];
    $stk  = (int)$_POST['stock'];
    $smin = (int)$_POST['stock_minimo'];

    if (!empty($_POST['id_producto'])) {
        $id = (int)$_POST['id_producto'];
        $conexion->query("UPDATE productos SET nombre='$nom', categoria='$cat', descripcion='$desc',
            precio_costo=$pc, precio_venta=$pv, stock=$stk, stock_minimo=$smin WHERE id_producto=$id");
    } else {
        $conexion->query("INSERT INTO productos (nombre, categoria, descripcion, precio_costo, precio_venta, stock, stock_minimo)
            VALUES ('$nom','$cat','$desc',$pc,$pv,$stk,$smin)");
    }
    echo "<script>window.location='inventario.php?res=guardado';</script>"; exit;
}

$edit = null;
if (isset($_GET['editar'])) {
    $edit = $conexion->query("SELECT * FROM productos WHERE id_producto=".(int)$_GET['editar'])->fetch_assoc();
}

$categorias = ['Suplementos','Bebidas','Accesorios','Ropa','General'];
$filtro_cat = $conexion->real_escape_string($_GET['cat'] ?? '');
$where = $filtro_cat ? "WHERE categoria='$filtro_cat'" : '';
$productos = $conexion->query("SELECT * FROM productos $where ORDER BY categoria, nombre ASC");
$alertas   = $conexion->query("SELECT * FROM productos WHERE stock <= stock_minimo AND estado='activo'")->num_rows;
?>
<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Inventario de Productos</h2>
        <p class="page-subtitle">Control de stock del gimnasio.</p>
      </div>
      <div class="col-auto" style="display:flex;gap:10px;">
        <?php if ($alertas > 0): ?>
        <span class="badge badge-red" style="padding:8px 14px;font-size:.82rem;align-self:center;">
          <i class="ti ti-alert-triangle me-1"></i><?php echo $alertas; ?> producto(s) con stock bajo
        </span>
        <?php endif; ?>
        <a href="pos.php" class="btn btn-red"><i class="ti ti-cash me-1"></i>Punto de Venta</a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
    <div class="alert <?php echo $_GET['res']==='eliminado'?'alert-danger':'alert-success'; ?>">
      <?php echo $_GET['res']==='eliminado' ? '✓ Producto eliminado.' : '✓ Producto guardado correctamente.'; ?>
    </div>
    <?php endif; ?>
    <?php if (isset($msg_err)): ?>
    <div class="alert alert-danger"><?php echo $msg_err; ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

      <!-- TABLA ─────────────────────────────────────────── -->
      <div>
        <!-- Filtro por categoría -->
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
          <a href="inventario.php" class="btn btn-outline" style="padding:6px 14px;font-size:.82rem;<?php echo !$filtro_cat?'border-color:var(--red);color:var(--red);':''; ?>">Todos</a>
          <?php foreach ($categorias as $c): ?>
          <a href="inventario.php?cat=<?php echo urlencode($c); ?>" class="btn btn-outline"
             style="padding:6px 14px;font-size:.82rem;<?php echo $filtro_cat===$c?'border-color:var(--red);color:var(--red);':''; ?>">
            <?php echo $c; ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="card">
          <div class="card-header gray">
            <h3 class="card-title">Productos</h3>
          </div>
          <div class="table-responsive">
            <table class="gym-table">
              <thead>
                <tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr>
              </thead>
              <tbody>
                <?php if ($productos->num_rows === 0): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted);">Sin productos. Agrega el primero.</td></tr>
                <?php endif; ?>
                <?php while ($p = $productos->fetch_assoc()):
                  $stock_bajo = $p['stock'] <= $p['stock_minimo'];
                ?>
                <tr>
                  <td>
                    <div class="td-name"><?php echo htmlspecialchars($p['nombre']); ?></div>
                    <?php if ($p['descripcion']): ?>
                    <div class="td-muted small"><?php echo htmlspecialchars(substr($p['descripcion'],0,45)); ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge badge-blue" style="font-size:.7rem;"><?php echo $p['categoria']; ?></span></td>
                  <td>
                    <div class="fw-bold" style="font-family:'Oswald',sans-serif;">$<?php echo number_format($p['precio_venta'],2); ?></div>
                    <div class="td-muted small">Costo: $<?php echo number_format($p['precio_costo'],2); ?></div>
                  </td>
                  <td>
                    <span class="badge <?php echo $stock_bajo ? 'badge-red' : 'badge-green'; ?>" style="font-size:.8rem;">
                      <?php echo $p['stock']; ?> uds
                    </span>
                    <?php if ($stock_bajo): ?>
                    <div class="td-muted small">Mín: <?php echo $p['stock_minimo']; ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?php echo $p['estado']==='activo'?'badge-green':'badge-gray'; ?>">
                      <?php echo strtoupper($p['estado']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="btn-list">
                      <a href="inventario.php?editar=<?php echo $p['id_producto']; ?>" class="btn btn-icon edit" title="Editar"><i class="ti ti-edit"></i></a>
                      <a href="inventario.php?toggle=<?php echo $p['id_producto']; ?>" class="btn btn-icon" title="<?php echo $p['estado']==='activo'?'Desactivar':'Activar'; ?>">
                        <i class="ti ti-<?php echo $p['estado']==='activo'?'eye-off':'eye'; ?>"></i>
                      </a>
                      <a href="inventario.php?eliminar=<?php echo $p['id_producto']; ?>" class="btn btn-icon" title="Eliminar" onclick="return confirm('¿Eliminar este producto?');"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- FORMULARIO ─────────────────────────────────────── -->
      <form method="POST" class="card" style="position:sticky;top:80px;">
        <div class="card-header" style="background:linear-gradient(135deg,#1565C0,#1976D2);">
          <span class="card-title" style="color:#fff;">
            <i class="ti ti-<?php echo $edit?'edit':'plus'; ?> me-2"></i>
            <?php echo $edit ? 'Editar: '.htmlspecialchars($edit['nombre']) : 'Nuevo Producto'; ?>
          </span>
        </div>
        <div class="card-body">
          <?php if ($edit): ?>
          <input type="hidden" name="id_producto" value="<?php echo $edit['id_producto']; ?>">
          <?php endif; ?>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required
                     value="<?php echo $edit ? htmlspecialchars($edit['nombre']) : ''; ?>" placeholder="Ej. Proteína Whey">
            </div>
            <div>
              <label class="form-label">Categoría</label>
              <select name="categoria" class="form-select">
                <?php foreach ($categorias as $c): ?>
                <option value="<?php echo $c; ?>" <?php if($edit && $edit['categoria']===$c) echo 'selected'; ?>><?php echo $c; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <div>
                <label class="form-label">Precio Costo ($)</label>
                <input type="number" step="0.01" name="precio_costo" class="form-control"
                       value="<?php echo $edit ? $edit['precio_costo'] : ''; ?>" placeholder="0.00">
              </div>
              <div>
                <label class="form-label">Precio Venta ($)</label>
                <input type="number" step="0.01" name="precio_venta" class="form-control" required
                       value="<?php echo $edit ? $edit['precio_venta'] : ''; ?>" placeholder="0.00">
              </div>
              <div>
                <label class="form-label">Stock actual</label>
                <input type="number" name="stock" class="form-control" min="0"
                       value="<?php echo $edit ? $edit['stock'] : '0'; ?>">
              </div>
              <div>
                <label class="form-label">Stock mínimo</label>
                <input type="number" name="stock_minimo" class="form-control" min="0"
                       value="<?php echo $edit ? $edit['stock_minimo'] : '5'; ?>">
              </div>
            </div>
            <div>
              <label class="form-label">Descripción (opcional)</label>
              <textarea name="descripcion" class="form-control" rows="2"><?php echo $edit ? htmlspecialchars($edit['descripcion']) : ''; ?></textarea>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="inventario.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-blue">
            <i class="ti ti-device-floppy me-1"></i><?php echo $edit ? 'Guardar Cambios' : 'Agregar Producto'; ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
