<?php
// pagos.php - Gestión de Pagos y Caja
include 'config.php';
include 'header.php';

// Bloqueamos la página a cualquier usuario que no sea Administrador
if (!esAdministrador()) {
  echo "<div class='container-xl mt-4'><div class='alert alert-danger'>Acceso denegado. Esta página es exclusiva para Administradores.</div></div>";
  include 'footer.php';
  exit();
}

$mes_actual = date('Y-m');

// 1. Obtener el total de ingresos del mes actual
$consulta_ingresos = $conexion->query("SELECT SUM(monto) as total FROM pagos WHERE estado = 'pagado' AND DATE_FORMAT(fecha_pago, '%Y-%m') = '$mes_actual'");
$ingresos_mes = ($consulta_ingresos && $consulta_ingresos->num_rows > 0) ? ($consulta_ingresos->fetch_assoc()['total'] ?? 0) : 0;

// 2. Obtener la lista de todos los pagos registrados (más recientes primero)
$consulta_pagos = $conexion->query("
    SELECT p.*, s.nombre, s.apellido, m.nombre as plan 
    FROM pagos p 
    LEFT JOIN socios s ON p.id_socio = s.id_socio 
    LEFT JOIN membresias m ON p.id_membresia = m.id_membresia 
    ORDER BY p.id_pago DESC 
    LIMIT 50
");

?>
<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h1 class="page-title">Caja y Pagos</h1>
        <p class="page-subtitle">Monitoreo de ingresos y registro de mensualidades.</p>
      </div>
      <div class="col-auto">
        <a href="nuevoPago.php" class="btn btn-red">
          <i class="ti ti-cash"></i> + Registrar Nuevo Pago
        </a>
      </div>
    </div>

    <!-- Mini Dashboard de Caja -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card" style="border-left: 4px solid var(--green);">
          <div class="card-body">
            <div
              style="color:var(--muted); font-size:0.8rem; font-weight:600; text-transform:uppercase; margin-bottom:5px;">
              Ingresos este mes</div>
            <div style="font-size:2rem; font-family:'Oswald', sans-serif; font-weight:700; color:var(--green);">
              $<?php echo number_format($ingresos_mes, 2); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla Lista de Pagos -->
    <div class="card">
      <div class="card-header gray">
        <span class="card-title">Historial de Transacciones</span>
      </div>
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fecha</th>
              <th>Socio</th>
              <th>Plan / Membresía</th>
              <th>Método</th>
              <th>Monto</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($consulta_pagos && $consulta_pagos->num_rows > 0): ?>
              <?php while ($pago = $consulta_pagos->fetch_assoc()):
                $color_estado = $pago['estado'] == 'pagado' ? 'badge-green' : 'badge-red';
                ?>
                <tr>
                  <td class="td-muted">#<?php echo $pago['id_pago']; ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></td>
                  <td class="td-name"><?php echo $pago['nombre'] . ' ' . $pago['apellido']; ?></td>
                  <td><span class="badge badge-blue"><?php echo $pago['plan']; ?></span></td>
                  <td class="td-muted"><?php echo $pago['metodo_pago'] ?: 'Efectivo'; ?></td>
                  <td style="font-weight:700; color:var(--text);">$<?php echo number_format($pago['monto'], 2); ?></td>
                  <td><span class="badge <?php echo $color_estado; ?>"><?php echo strtoupper($pago['estado']); ?></span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align:center; padding:20px; color:var(--muted);">No hay pagos registrados aún
                  en el sistema.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include 'footer.php'; ?>