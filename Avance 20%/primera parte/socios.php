<?php 
include 'config.php';
include 'header.php'; 
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Gestión de Socios</h2>
        <p class="page-subtitle">Administra el padrón de miembros activos e inactivos.</p>
      </div>
      <div class="col-auto">
        <a href="nuevo_socio.php" class="btn btn-red">
          <i class="ti ti-user-plus"></i> Nuevo Socio
        </a>
      </div>
    </div>

    <?php if(isset($_GET['res'])): ?>
      <div class="alert <?php echo $_GET['res']=='eliminado' ? 'alert-danger' : 'alert-success'; ?>">
        <?php echo $_GET['res']=='eliminado' ? '✓ Socio eliminado correctamente.' : '✓ Socio actualizado correctamente.'; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr>
              <th>Socio / Contacto</th>
              <th>Plan</th>
              <th>Entrenador</th>
              <th>Vencimiento</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT s.*, m.nombre as plan, e.nombre as nombre_profe 
                    FROM socios s 
                    JOIN membresias m ON s.id_membresia = m.id_membresia 
                    LEFT JOIN entrenadores e ON s.id_entrenador = e.id_entrenador
                    ORDER BY s.id_socio DESC";
            $res = $conexion->query($sql);
            while($row = $res->fetch_assoc()):
              $vence     = strtotime($row['fecha_vencimiento']);
              $hoy       = strtotime(date('Y-m-d'));
              $vencido   = $vence < $hoy;
            ?>
            <tr>
              <td>
                <div class="td-name"><?php echo $row['nombre']." ".$row['apellido']; ?></div>
                <div class="td-muted">
                  <?php echo $row['telefono']; ?>
                  <?php if($row['correo']) echo " &middot; ".$row['correo']; ?>
                </div>
              </td>
              <td><span class="badge badge-blue"><?php echo $row['plan']; ?></span></td>
              <td class="td-muted">
                <i class="ti ti-barbell me-1"></i>
                <?php echo $row['nombre_profe'] ?: '<span style="color:#9CA3AF">Sin asignar</span>'; ?>
              </td>
              <td>
                <span class="<?php echo $vencido ? 'text-red' : 'text-green'; ?> fw-bold small">
                  <?php echo date('d/m/Y', $vence); ?>
                </span>
              </td>
              <td>
                <span class="badge <?php echo $vencido ? 'badge-red' : 'badge-green'; ?>">
                  <?php echo $vencido ? 'VENCIDO' : 'ACTIVO'; ?>
                </span>
              </td>
              <td>
                <div class="btn-list">
                  <a href="editar_socio.php?id=<?php echo $row['id_socio']; ?>" class="btn btn-icon edit" title="Editar">
                    <i class="ti ti-edit"></i>
                  </a>
                  <a href="eliminar_socio.php?id=<?php echo $row['id_socio']; ?>" 
                     class="btn btn-icon" title="Eliminar"
                     onclick="return confirm('¿Eliminar a este socio?');">
                    <i class="ti ti-trash"></i>
                  </a>
                </div>
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
