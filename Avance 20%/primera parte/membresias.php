<?php 
include 'config.php';
include 'header.php'; 
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Estado de Membresías</h2>
        <p class="page-subtitle">Monitoreo de vencimientos y planes contratados.</p>
      </div>
      <div class="col-auto">
        <a href="nuevo_socio.php" class="btn btn-red">
          <i class="ti ti-plus"></i> Nuevo Socio
        </a>
      </div>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr>
              <th>Socio</th>
              <th>Plan Contratado</th>
              <th>Fecha Inicio</th>
              <th>Fecha Vencimiento</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $res = $conexion->query("SELECT s.*, m.nombre as plan FROM socios s JOIN membresias m ON s.id_membresia = m.id_membresia ORDER BY s.fecha_vencimiento ASC");
            while($row = $res->fetch_assoc()):
              $vence   = strtotime($row['fecha_vencimiento']);
              $hoy     = strtotime(date('Y-m-d'));
              $expired = ($hoy > $vence);
            ?>
            <tr>
              <td class="td-name"><?php echo $row['nombre']." ".$row['apellido']; ?></td>
              <td><span class="badge badge-blue"><?php echo $row['plan']; ?></span></td>
              <td class="td-muted"><?php echo date('d M Y', strtotime($row['fecha_registro'])); ?></td>
              <td>
                <span class="<?php echo $expired ? 'text-red' : 'text-green'; ?> fw-bold small">
                  <?php echo date('d M Y', $vence); ?>
                </span>
              </td>
              <td>
                <span class="badge <?php echo $expired ? 'badge-red' : 'badge-green'; ?>">
                  <?php echo $expired ? 'VENCIDA' : 'ACTIVA'; ?>
                </span>
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
