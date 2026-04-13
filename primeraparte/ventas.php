<?php
/**
 * Archivo: ventas.php
 * Descripción: Historial del punto de venta (POS) correspondientes a artículos o productos.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$hoy = date('Y-m-d');
$desde  = $conexion->real_escape_string($_GET['desde'] ?? date('Y-m-01'));
$hasta  = $conexion->real_escape_string($_GET['hasta'] ?? $hoy);

$ventas = $conexion->query("
    SELECT v.*, u.nombre_completo as cajero,
           CONCAT(COALESCE(s.nombre,''),' ',COALESCE(s.apellido,'')) as cliente
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    LEFT JOIN socios   s ON v.id_socio   = s.id_socio
    WHERE DATE(v.fecha) BETWEEN '$desde' AND '$hasta'
    ORDER BY v.fecha DESC
");

$resumen = $conexion->query("SELECT COUNT(*) as n, COALESCE(SUM(total),0) as total,
    COALESCE(SUM(descuento),0) as desc_total
    FROM ventas WHERE DATE(fecha) BETWEEN '$desde' AND '$hasta'")->fetch_assoc();
?>
<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Historial de Ventas</h2>
        <p class="page-subtitle">Registro de todas las transacciones del punto de venta.</p>
      </div>
      <div class="col-auto" style="display:flex;gap:10px;">
        <a href="pos.php" class="btn btn-red"><i class="ti ti-cash me-1"></i>Nueva Venta</a>
        <a href="reportes.php" class="btn btn-outline"><i class="ti ti-file-text me-1"></i>Reportes PDF</a>
      </div>
    </div>

    <!-- Filtro fechas -->
    <form method="GET" class="card" style="padding:16px 20px;margin-bottom:18px;">
      <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div>
          <label class="form-label">Desde</label>
          <input type="date" name="desde" class="form-control" value="<?php echo $desde; ?>" style="width:160px;">
        </div>
        <div>
          <label class="form-label">Hasta</label>
          <input type="date" name="hasta" class="form-control" value="<?php echo $hasta; ?>" style="width:160px;">
        </div>
        <button type="submit" class="btn btn-red"><i class="ti ti-search me-1"></i>Filtrar</button>
        <a href="ventas.php" class="btn btn-link">Limpiar</a>
      </div>
    </form>

    <!-- KPIs del rango -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
      <div class="card" style="padding:18px;text-align:center;">
        <div style="font-family:'Oswald',sans-serif;font-size:2rem;font-weight:700;"><?php echo $resumen['n']; ?></div>
        <div style="font-size:.78rem;color:var(--muted);">Ventas en el período</div>
      </div>
      <div class="card" style="padding:18px;text-align:center;border-left:3px solid var(--green);">
        <div style="font-family:'Oswald',sans-serif;font-size:2rem;font-weight:700;color:var(--green);">$<?php echo number_format($resumen['total'],2); ?></div>
        <div style="font-size:.78rem;color:var(--muted);">Ingresos totales</div>
      </div>
      <div class="card" style="padding:18px;text-align:center;border-left:3px solid var(--gold);">
        <div style="font-family:'Oswald',sans-serif;font-size:2rem;font-weight:700;color:var(--gold-dark);">$<?php echo number_format($resumen['desc_total'],2); ?></div>
        <div style="font-size:.78rem;color:var(--muted);">Descuentos aplicados</div>
      </div>
    </div>

    <div class="card">
      <div class="card-header gray"><h3 class="card-title">Transacciones</h3></div>
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Cajero</th><th>Método</th><th>Subtotal</th><th>Descuento</th><th>Total</th><th>Acciones</th></tr>
          </thead>
          <tbody>
            <?php if ($ventas->num_rows === 0): ?>
            <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--muted);">Sin ventas en este período.</td></tr>
            <?php endif; ?>
            <?php while ($v = $ventas->fetch_assoc()): ?>
            <tr>
              <td class="td-muted">#<?php echo str_pad($v['id_venta'],5,'0',STR_PAD_LEFT); ?></td>
              <td class="td-muted"><?php echo date('d/m/Y H:i', strtotime($v['fecha'])); ?></td>
              <td class="td-name"><?php echo trim($v['cliente']) ?: '<span style="color:var(--muted);">Ocasional</span>'; ?></td>
              <td class="td-muted"><?php echo htmlspecialchars($v['cajero'] ?? '—'); ?></td>
              <td><span class="badge badge-blue" style="font-size:.7rem;"><?php echo $v['metodo_pago']; ?></span></td>
              <td class="td-muted">$<?php echo number_format($v['subtotal'],2); ?></td>
              <td><?php echo $v['descuento'] > 0 ? '<span style="color:var(--red);">-$'.number_format($v['descuento'],2).'</span>' : '—'; ?></td>
              <td class="fw-bold" style="font-family:'Oswald',sans-serif;font-size:1rem;">$<?php echo number_format($v['total'],2); ?></td>
              <td>
                <a href="ticket.php?id=<?php echo $v['id_venta']; ?>" target="_blank" class="btn btn-icon" title="Ver Ticket">
                  <i class="ti ti-printer"></i>
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
<?php include 'footer.php'; ?>
