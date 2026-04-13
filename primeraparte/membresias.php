<?php
/**
 * Archivo: membresias.php
 * Descripción: Listado del estado de las membresías adquiridas por los socios.
 * Parte del sistema integral de gestión Sayagym.
 */


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
        <a href="nuevoSocio.php" class="btn btn-red">
          <i class="ti ti-plus"></i> Nuevo Socio
        </a>
      </div>
    </div>

    <div class="card">
      <div class="card-header gray">
        <h3 class="card-title">Listado de Membresías</h3>
      </div>
      <div class="card-body p-0" style="display: none;">
        <!-- Estructura Tabler: Aquí irían los filtros o búsqueda -->
      </div>
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
            $res = $conexion->query("SELECT s.*, COALESCE(m.nombre, 'Sin plan') as plan FROM socios s LEFT JOIN membresias m ON s.id_membresia = m.id_membresia ORDER BY s.fecha_vencimiento ASC");
            if ($res && $res->num_rows > 0):
              while ($row = $res->fetch_assoc()):
                $vence = strtotime($row['fecha_vencimiento']);
                $hoy = strtotime(date('Y-m-d'));
                $expired = ($hoy > $vence);
                if ($row['estado'] == 'inactivo') {
                  $status_class = 'badge-secondary';
                  $status_text = 'INACTIVA';
                } else if ($expired) {
                  $status_class = 'badge-red';
                  $status_text = 'VENCIDA';
                } else {
                  $status_class = 'badge-green';
                  $status_text = 'ACTIVA';
                }
                ?>
                <tr>
                  <td class="td-name"><?php echo $row['nombre'] . " " . $row['apellido']; ?></td>
                  <td><span class="badge badge-blue"><?php echo $row['plan']; ?></span></td>
                  <td class="td-muted"><?php echo date('d M Y', strtotime($row['fecha_registro'])); ?></td>
                  <td>
                    <span class="<?php echo $expired ? 'text-red' : 'text-green'; ?> fw-bold small">
                      <?php echo date('d M Y', $vence); ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge <?php echo $status_class; ?>">
                      <?php echo $status_text; ?>
                    </span>
                  </td>
                </tr>
                <?php
              endwhile;
            else: ?>
              <tr>
                <td colspan="5" style="text-align:center;color:#9CA3AF;padding:20px;">No se encontraron registros.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted small">Mostrando todos los registros</p>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>