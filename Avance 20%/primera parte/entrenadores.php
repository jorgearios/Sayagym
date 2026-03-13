<?php 
include 'config.php';
include 'header.php'; 
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Equipo de Entrenadores</h2>
        <p class="page-subtitle">Gestión de personal y comisiones.</p>
      </div>
      <div class="col-auto">
        <a href="nuevo_entrenador.php" class="btn btn-red">
          <i class="ti ti-plus"></i> Registrar Entrenador
        </a>
      </div>
    </div>

    <?php if(isset($_GET['res'])): ?>
      <div class="alert <?php echo $_GET['res']=='eliminado' ? 'alert-danger' : 'alert-success'; ?>">
        <?php echo $_GET['res']=='eliminado' ? '✓ Entrenador eliminado.' : '✓ Entrenador actualizado correctamente.'; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr>
              <th>Entrenador</th>
              <th>Contacto</th>
              <th>Turno / Especialidad</th>
              <th>Comisión</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $res = $conexion->query("SELECT * FROM entrenadores ORDER BY id_entrenador DESC");
            while($e = $res->fetch_assoc()):
            ?>
            <tr>
              <td class="td-name"><?php echo $e['nombre']; ?></td>
              <td>
                <div><?php echo $e['telefono']; ?></div>
                <div class="td-muted"><?php echo $e['correo']; ?></div>
              </td>
              <td>
                <div class="small"><?php echo $e['especialidad']; ?></div>
                <span class="badge badge-purple"><?php echo $e['turno']; ?></span>
              </td>
              <td>
                <span class="fw-bold text-green font-oswald" style="font-size:1.05rem;">
                  $<?php echo number_format($e['tarifa_comision'], 2); ?>
                </span>
              </td>
              <td>
                <span class="badge <?php echo $e['estado']=='activo' ? 'badge-green' : 'badge-gray'; ?>">
                  <?php echo strtoupper($e['estado']); ?>
                </span>
              </td>
              <td>
                <div class="btn-list">
                  <a href="editar_entrenador.php?id=<?php echo $e['id_entrenador']; ?>" class="btn btn-icon edit" title="Editar">
                    <i class="ti ti-edit"></i>
                  </a>
                  <a href="eliminar_entrenador.php?id=<?php echo $e['id_entrenador']; ?>"
                     class="btn btn-icon" title="Eliminar"
                     onclick="return confirm('¿Eliminar a este entrenador?');">
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
