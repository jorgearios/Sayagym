<?php
/**
 * Archivo: reportes.php
 * Descripción: Panel central para enlaces y botones que dirigen a reportes detallados.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$hoy = date('Y-m-d');
// Quick stats for the page
$st_ventas = $conexion->query("SELECT COUNT(*) as n, COALESCE(SUM(total),0) as t FROM ventas WHERE DATE(fecha)='{$hoy}'")->fetch_assoc();
$st_activas = $conexion->query("SELECT COUNT(*) as n FROM socios WHERE fecha_vencimiento >= '{$hoy}' AND estado='activo'")->fetch_assoc();
$st_vencidas = $conexion->query("SELECT COUNT(*) as n FROM socios WHERE fecha_vencimiento < '{$hoy}'")->fetch_assoc();
$st_prods = $conexion->query("SELECT COUNT(*) as n FROM productos WHERE stock <= stock_minimo AND estado='activo'")->fetch_assoc();
?>
<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Centro de Reportes</h2>
        <p class="page-subtitle">Genera reportes en PDF con un clic.</p>
      </div>
    </div>

    <!-- KPIs rápidos -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:30px;">
      <div class="card" style="padding:18px;text-align:center;border-left:3px solid var(--green);">
        <div style="font-family:'Oswald',sans-serif;font-size:1.8rem;font-weight:700;color:var(--green);">$<?php echo number_format($st_ventas['t'],2); ?></div>
        <div style="font-size:.76rem;color:var(--muted);">Ventas hoy (<?php echo $st_ventas['n']; ?>)</div>
      </div>
      <div class="card" style="padding:18px;text-align:center;border-left:3px solid var(--blue);">
        <div style="font-family:'Oswald',sans-serif;font-size:1.8rem;font-weight:700;color:var(--blue);"><?php echo $st_activas['n']; ?></div>
        <div style="font-size:.76rem;color:var(--muted);">Membresías activas</div>
      </div>
      <div class="card" style="padding:18px;text-align:center;border-left:3px solid var(--red);">
        <div style="font-family:'Oswald',sans-serif;font-size:1.8rem;font-weight:700;color:var(--red);"><?php echo $st_vencidas['n']; ?></div>
        <div style="font-size:.76rem;color:var(--muted);">Membresías vencidas</div>
      </div>
      <div class="card" style="padding:18px;text-align:center;border-left:3px solid var(--gold);">
        <div style="font-family:'Oswald',sans-serif;font-size:1.8rem;font-weight:700;color:var(--gold-dark);"><?php echo $st_prods['n']; ?></div>
        <div style="font-size:.76rem;color:var(--muted);">Productos stock bajo</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;">

      <!-- Reporte 1: Ingresos por rango de fechas -->
      <div class="card">
        <div class="card-header" style="background:linear-gradient(135deg,#15803D,#16a34a);">
          <span class="card-title" style="color:#fff;"><i class="ti ti-chart-bar me-2"></i>Ingresos por Período</span>
        </div>
        <div class="card-body">
          <p style="font-size:.84rem;color:var(--muted);margin-bottom:16px;line-height:1.5;">
            Reporte de ingresos del punto de venta incluyendo detalle por método de pago y productos más vendidos.
          </p>
          <form action="reporte_ingresos.php" method="GET" target="_blank">
            <div style="margin-bottom:10px;">
              <label class="form-label">Fecha inicio</label>
              <input type="date" name="desde" class="form-control" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div style="margin-bottom:14px;">
              <label class="form-label">Fecha fin</label>
              <input type="date" name="hasta" class="form-control" value="<?php echo $hoy; ?>">
            </div>
            <button type="submit" class="btn" style="background:var(--green);color:#fff;width:100%;justify-content:center;">
              <i class="ti ti-file-type-pdf me-2"></i>Generar PDF
            </button>
          </form>
        </div>
      </div>

      <!-- Reporte 2: Membresías activas -->
      <div class="card">
        <div class="card-header" style="background:linear-gradient(135deg,#1565C0,#1976D2);">
          <span class="card-title" style="color:#fff;"><i class="ti ti-id-badge me-2"></i>Membresías Activas</span>
        </div>
        <div class="card-body">
          <p style="font-size:.84rem;color:var(--muted);margin-bottom:16px;line-height:1.5;">
            Lista de todos los socios con membresía vigente, plan contratado, entrenador asignado y fecha de vencimiento.
          </p>
          <div style="background:#DBEAFE;border-radius:6px;padding:12px;margin-bottom:14px;text-align:center;">
            <div style="font-family:'Oswald',sans-serif;font-size:1.6rem;font-weight:700;color:var(--blue);"><?php echo $st_activas['n']; ?></div>
            <div style="font-size:.75rem;color:#1565C0;">socios con membresía activa hoy</div>
          </div>
          <a href="reporte_membresias.php?tipo=activas" target="_blank"
             class="btn btn-blue" style="width:100%;justify-content:center;">
            <i class="ti ti-file-type-pdf me-2"></i>Generar PDF
          </a>
        </div>
      </div>

      <!-- Reporte 3: Membresías vencidas -->
      <div class="card">
        <div class="card-header" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
          <span class="card-title" style="color:#fff;"><i class="ti ti-alert-triangle me-2"></i>Membresías Vencidas</span>
        </div>
        <div class="card-body">
          <p style="font-size:.84rem;color:var(--muted);margin-bottom:16px;line-height:1.5;">
            Reporte de socios con membresía vencida o con cuenta inactiva, ordenados por fecha de vencimiento.
          </p>
          <div style="background:#FEE2E2;border-radius:6px;padding:12px;margin-bottom:14px;text-align:center;">
            <div style="font-family:'Oswald',sans-serif;font-size:1.6rem;font-weight:700;color:var(--red);"><?php echo $st_vencidas['n']; ?></div>
            <div style="font-size:.75rem;color:var(--danger);">socios con membresía vencida</div>
          </div>
          <a href="reporte_membresias.php?tipo=vencidas" target="_blank"
             class="btn btn-red" style="width:100%;justify-content:center;">
            <i class="ti ti-file-type-pdf me-2"></i>Generar PDF
          </a>
        </div>
      </div>

    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
