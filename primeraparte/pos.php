<?php
/**
 * Archivo: pos.php
 * Descripción: Módulo de punto de venta rápido para consumir artículos (snacks, bebidas).
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$hoy = date('Y-m-d');

// ── PROCESAR VENTA ────────────────────────────────────────
if ($_POST && isset($_POST['confirmar_venta'])) {
    $id_socio   = !empty($_POST['id_socio'])    ? (int)$_POST['id_socio']    : null;
    $metodo     = $conexion->real_escape_string($_POST['metodo_pago']);
    $nota       = $conexion->real_escape_string($_POST['nota'] ?? '');
    $descuento  = (float)($_POST['descuento'] ?? 0);
    $id_usuario = (int)$_SESSION['usuario_id'];

    $ids_prod   = $_POST['prod_id']  ?? [];
    $cantidades = $_POST['prod_qty'] ?? [];

    if (empty($ids_prod)) {
        $error_venta = "Agrega al menos un producto antes de confirmar.";
    } else {
        // Calcular subtotal
        $subtotal = 0;
        $items = [];
        $stock_ok = true;
        foreach ($ids_prod as $k => $pid) {
            $pid = (int)$pid;
            $qty = max(1, (int)($cantidades[$k] ?? 1));
            $prod = $conexion->query("SELECT nombre, precio_venta, stock FROM productos WHERE id_producto=$pid AND estado='activo'")->fetch_assoc();
            if (!$prod) continue;
            if ($prod['stock'] < $qty) {
                $error_venta = "Stock insuficiente para «{$prod['nombre']}» (disponible: {$prod['stock']}).";
                $stock_ok = false; break;
            }
            $sub_item  = $prod['precio_venta'] * $qty;
            $subtotal += $sub_item;
            $items[] = ['id'=>$pid, 'nombre'=>$prod['nombre'], 'precio'=>$prod['precio_venta'], 'qty'=>$qty, 'sub'=>$sub_item];
        }

        if ($stock_ok) {
            $total = max(0, $subtotal - $descuento);
            $id_socio_sql = $id_socio ? $id_socio : 'NULL';

            $conexion->query("INSERT INTO ventas (id_usuario, id_socio, subtotal, descuento, total, metodo_pago, nota)
                VALUES ($id_usuario, $id_socio_sql, $subtotal, $descuento, $total, '$metodo', '$nota')");
            $id_venta = $conexion->insert_id;

            foreach ($items as $it) {
                $conexion->query("INSERT INTO venta_detalle (id_venta, id_producto, cantidad, precio_unit, subtotal)
                    VALUES ($id_venta, {$it['id']}, {$it['qty']}, {$it['precio']}, {$it['sub']})");
                // Descontar stock
                $conexion->query("UPDATE productos SET stock = stock - {$it['qty']} WHERE id_producto={$it['id']}");
            }
            echo "<script>window.location='pos.php?venta=$id_venta&res=ok';</script>"; exit;
        }
    }
}

// Alerta venta exitosa
$venta_ok = null;
if (isset($_GET['res']) && $_GET['res']==='ok' && isset($_GET['venta'])) {
    $id_v = (int)$_GET['venta'];
    $venta_ok = $conexion->query("SELECT v.*, s.nombre as snombre, s.apellido as sape
        FROM ventas v LEFT JOIN socios s ON v.id_socio=s.id_socio
        WHERE v.id_venta=$id_v")->fetch_assoc();
}

// Datos para el formulario
$productos_activos = $conexion->query("SELECT id_producto, nombre, categoria, precio_venta, stock FROM productos WHERE estado='activo' AND stock > 0 ORDER BY categoria, nombre");
$socios_lista      = $conexion->query("SELECT id_socio, nombre, apellido FROM socios WHERE estado='activo' ORDER BY nombre");
$promo_activa      = $conexion->query("SELECT * FROM promociones WHERE estado='activa' AND fecha_inicio <= '$hoy' AND fecha_fin >= '$hoy' ORDER BY id_promo DESC LIMIT 1")->fetch_assoc();

// KPIs del día
$ventas_hoy   = $conexion->query("SELECT COUNT(*) as n, COALESCE(SUM(total),0) as t FROM ventas WHERE DATE(fecha)='$hoy'")->fetch_assoc();
?>
<style>
.pos-grid { display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start; }
.prod-card { border:1.5px solid var(--border); border-radius:8px; padding:12px 14px; cursor:pointer; transition:all .15s; background:#fff; }
.prod-card:hover { border-color:var(--red); background:#FEF2F2; }
.prod-card .pnombre { font-weight:700; font-size:.88rem; }
.prod-card .pprecio { font-family:'Oswald',sans-serif; font-size:1.1rem; color:var(--red); font-weight:700; }
.prod-card .pstock  { font-size:.74rem; color:var(--muted); }
.cart-row { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid var(--border); font-size:.88rem; }
.cart-row:last-child { border-bottom:none; }
#cart-empty { text-align:center; padding:30px; color:var(--muted); font-size:.88rem; }
</style>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Punto de Venta</h2>
        <p class="page-subtitle"><?php echo date('d \d\e F \d\e Y'); ?></p>
      </div>
      <div class="col-auto" style="display:flex;gap:10px;align-items:center;">
        <div style="text-align:right;">
          <div style="font-family:'Oswald',sans-serif;font-size:1.4rem;font-weight:700;color:var(--green);">$<?php echo number_format($ventas_hoy['t'],2); ?></div>
          <div style="font-size:.74rem;color:var(--muted);"><?php echo $ventas_hoy['n']; ?> venta(s) hoy</div>
        </div>
        <a href="inventario.php" class="btn btn-outline"><i class="ti ti-package me-1"></i>Inventario</a>
        <a href="ventas.php" class="btn btn-outline"><i class="ti ti-list me-1"></i>Historial</a>
      </div>
    </div>

    <?php if ($venta_ok): ?>
    <div class="alert alert-success" style="display:flex;justify-content:space-between;align-items:center;">
      <span>✅ Venta #<?php echo $venta_ok['id_venta']; ?> registrada — Total: <strong>$<?php echo number_format($venta_ok['total'],2); ?></strong></span>
      <a href="ticket.php?id=<?php echo $venta_ok['id_venta']; ?>" target="_blank" class="btn btn-outline" style="padding:6px 14px;font-size:.82rem;">
        <i class="ti ti-printer me-1"></i>Imprimir Ticket
      </a>
    </div>
    <?php endif; ?>

    <?php if (isset($error_venta)): ?>
    <div class="alert alert-danger"><?php echo $error_venta; ?></div>
    <?php endif; ?>

    <?php if ($promo_activa): ?>
    <div style="background:#DCFCE7;border-left:4px solid var(--green);border-radius:0 6px 6px 0;padding:10px 16px;margin-bottom:16px;font-size:.85rem;color:#15803D;display:flex;align-items:center;gap:10px;">
      <i class="ti ti-tag" style="font-size:1.1rem;"></i>
      <span>Promoción activa: <strong><?php echo htmlspecialchars($promo_activa['nombre']); ?></strong>
      — <?php echo $promo_activa['tipo']==='porcentaje' ? $promo_activa['valor'].'% de descuento' : '$'.number_format($promo_activa['valor'],2).' de descuento fijo'; ?>
      (válida hasta <?php echo date('d/m/Y', strtotime($promo_activa['fecha_fin'])); ?>)</span>
      <button type="button" onclick="aplicarPromo(<?php echo $promo_activa['tipo']==='porcentaje'?-$promo_activa['valor']:$promo_activa['valor']; ?>, '<?php echo $promo_activa['tipo']; ?>')"
              class="btn btn-outline" style="padding:4px 12px;font-size:.78rem;border-color:var(--green);color:var(--green);">Aplicar</button>
    </div>
    <?php endif; ?>

    <form method="POST" id="form-venta">
    <input type="hidden" name="confirmar_venta" value="1">
    <div class="pos-grid">

      <!-- PRODUCTOS ──────────────────────────────────────── -->
      <div>
        <!-- Buscador -->
        <div style="margin-bottom:12px;">
          <input type="text" id="buscador-prod" class="form-control" placeholder="Buscar producto..." oninput="filtrarProductos(this.value)">
        </div>
        <div id="grid-productos" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
          <?php
          $productos_activos->data_seek(0);
          while ($p = $productos_activos->fetch_assoc()):
          ?>
          <div class="prod-card" data-nombre="<?php echo strtolower(htmlspecialchars($p['nombre'])); ?>"
               onclick="agregarAlCarrito(<?php echo $p['id_producto']; ?>, '<?php echo addslashes($p['nombre']); ?>', <?php echo $p['precio_venta']; ?>, <?php echo $p['stock']; ?>)">
            <div class="pnombre"><?php echo htmlspecialchars($p['nombre']); ?></div>
            <div class="pprecio">$<?php echo number_format($p['precio_venta'],2); ?></div>
            <div class="pstock"><i class="ti ti-package" style="font-size:.8rem;"></i> Stock: <?php echo $p['stock']; ?></div>
          </div>
          <?php endwhile; ?>
        </div>
      </div>

      <!-- CARRITO ────────────────────────────────────────── -->
      <div class="card" style="position:sticky;top:80px;">
        <div class="card-header" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
          <span class="card-title" style="color:#fff;"><i class="ti ti-shopping-cart me-2"></i>Carrito</span>
          <button type="button" onclick="limpiarCarrito()" class="btn btn-link" style="color:rgba(255,255,255,.7);padding:4px 8px;font-size:.8rem;">Limpiar</button>
        </div>
        <div class="card-body" style="padding:14px;">
          <div id="cart-empty"><i class="ti ti-shopping-cart" style="font-size:2rem;display:block;margin-bottom:8px;"></i>Haz clic en un producto para agregarlo</div>
          <div id="cart-items"></div>
          <div id="cart-hidden-inputs"></div>

          <!-- Totales -->
          <div id="cart-totales" style="display:none;border-top:2px solid var(--border);margin-top:12px;padding-top:12px;">
            <div style="display:flex;justify-content:space-between;font-size:.88rem;margin-bottom:6px;">
              <span style="color:var(--muted);">Subtotal</span>
              <span id="lbl-subtotal">$0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.88rem;margin-bottom:10px;align-items:center;">
              <span style="color:var(--muted);">Descuento ($)</span>
              <input type="number" name="descuento" id="inp-descuento" min="0" step="0.01" value="0"
                     class="form-control" style="width:110px;text-align:right;padding:6px 8px;"
                     oninput="recalcular()">
            </div>
            <div style="display:flex;justify-content:space-between;font-family:'Oswald',sans-serif;font-size:1.5rem;font-weight:700;">
              <span>TOTAL</span>
              <span id="lbl-total" style="color:var(--red);">$0.00</span>
            </div>
          </div>

          <!-- Opciones de pago -->
          <div id="cart-opciones" style="display:none;margin-top:14px;">
            <div style="margin-bottom:10px;">
              <label class="form-label">Cliente (opcional)</label>
              <select name="id_socio" class="form-select">
                <option value="">— Cliente ocasional —</option>
                <?php while ($s = $socios_lista->fetch_assoc()): ?>
                <option value="<?php echo $s['id_socio']; ?>"><?php echo htmlspecialchars($s['nombre'].' '.$s['apellido']); ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div style="margin-bottom:10px;">
              <label class="form-label">Método de pago</label>
              <select name="metodo_pago" class="form-select">
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
                <option value="Transferencia">Transferencia</option>
              </select>
            </div>
            <div style="margin-bottom:12px;">
              <label class="form-label">Nota (opcional)</label>
              <input type="text" name="nota" class="form-control" placeholder="Observaciones...">
            </div>
            <button type="submit" class="btn btn-red" style="width:100%;justify-content:center;font-size:1rem;padding:13px;">
              <i class="ti ti-check me-2"></i>Confirmar Venta
            </button>
          </div>
        </div>
      </div>

    </div>
    </form>
  </div>
</div>

<script>
const carrito = {};

function agregarAlCarrito(id, nombre, precio, stockMax) {
    if (carrito[id]) {
        if (carrito[id].qty >= stockMax) {
            alert('Stock máximo disponible: ' + stockMax);
            return;
        }
        carrito[id].qty++;
    } else {
        carrito[id] = { nombre, precio, qty: 1, stockMax };
    }
    renderCarrito();
}

function cambiarCantidad(id, delta) {
    if (!carrito[id]) return;
    carrito[id].qty += delta;
    if (carrito[id].qty <= 0) { delete carrito[id]; }
    else if (carrito[id].qty > carrito[id].stockMax) { carrito[id].qty = carrito[id].stockMax; }
    renderCarrito();
}

function eliminarItem(id) { delete carrito[id]; renderCarrito(); }

function limpiarCarrito() {
    Object.keys(carrito).forEach(k => delete carrito[k]);
    document.getElementById('inp-descuento').value = 0;
    renderCarrito();
}

function renderCarrito() {
    const items  = document.getElementById('cart-items');
    const hidden = document.getElementById('cart-hidden-inputs');
    const empty  = document.getElementById('cart-empty');
    const tots   = document.getElementById('cart-totales');
    const opts   = document.getElementById('cart-opciones');
    const keys   = Object.keys(carrito);

    hidden.innerHTML = '';
    if (keys.length === 0) {
        items.innerHTML = ''; empty.style.display = 'block';
        tots.style.display = opts.style.display = 'none';
        return;
    }
    empty.style.display = 'none';
    tots.style.display = opts.style.display = 'block';

    items.innerHTML = keys.map(id => {
        const it = carrito[id];
        return `<div class="cart-row">
          <div style="flex:1;">
            <div style="font-weight:600;font-size:.85rem;">${it.nombre}</div>
            <div style="font-size:.76rem;color:var(--muted);">$${it.precio.toFixed(2)} c/u</div>
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <button type="button" onclick="cambiarCantidad(${id},-1)" style="width:26px;height:26px;border:1px solid var(--border);border-radius:4px;background:#fff;cursor:pointer;font-size:1rem;line-height:1;">−</button>
            <span style="min-width:20px;text-align:center;font-weight:700;">${it.qty}</span>
            <button type="button" onclick="cambiarCantidad(${id},1)" style="width:26px;height:26px;border:1px solid var(--border);border-radius:4px;background:#fff;cursor:pointer;font-size:1rem;line-height:1;">+</button>
          </div>
          <div style="min-width:64px;text-align:right;font-weight:700;font-family:'Oswald',sans-serif;">$${(it.precio*it.qty).toFixed(2)}</div>
          <button type="button" onclick="eliminarItem(${id})" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1rem;padding:2px 4px;">✕</button>
        </div>`;
    }).join('');

    hidden.innerHTML = keys.map(id =>
        `<input type="hidden" name="prod_id[]" value="${id}">
         <input type="hidden" name="prod_qty[]" value="${carrito[id].qty}">`
    ).join('');

    recalcular();
}

function recalcular() {
    const keys = Object.keys(carrito);
    const subtotal  = keys.reduce((s, id) => s + carrito[id].precio * carrito[id].qty, 0);
    const descuento = Math.max(0, parseFloat(document.getElementById('inp-descuento').value) || 0);
    const total     = Math.max(0, subtotal - descuento);
    document.getElementById('lbl-subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('lbl-total').textContent    = '$' + total.toFixed(2);
}

function aplicarPromo(valor, tipo) {
    const keys = Object.keys(carrito);
    if (keys.length === 0) { alert('Agrega productos primero.'); return; }
    const subtotal = keys.reduce((s, id) => s + carrito[id].precio * carrito[id].qty, 0);
    const desc = tipo === 'porcentaje' ? subtotal * Math.abs(valor) / 100 : Math.abs(valor);
    document.getElementById('inp-descuento').value = desc.toFixed(2);
    recalcular();
}

function filtrarProductos(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.prod-card').forEach(c => {
        c.style.display = c.dataset.nombre.includes(q) ? '' : 'none';
    });
}
</script>

<?php include 'footer.php'; ?>
