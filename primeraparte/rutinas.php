<?php
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$rutinas = $conexion->query("
    SELECT r.*,
           (SELECT COUNT(DISTINCT re.id_ejercicio) FROM rutina_ejercicio re WHERE re.id_rutina = r.id_rutina) as total_ejercicios,
           (SELECT COUNT(*) FROM socio_rutina sr WHERE sr.id_rutina = r.id_rutina) as total_asignados
    FROM rutinas r
    ORDER BY r.id_rutina DESC
");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Rutinas de Entrenamiento</h2>
        <p class="page-subtitle">Planes de ejercicio personalizables para socios.</p>
      </div>
      <div class="col-auto" style="display:flex; gap:10px;">
        <a href="ejercicios.php" class="btn btn-outline"><i class="ti ti-database me-1"></i>Catálogo Ejercicios</a>
        <a href="nuevaRutina.php" class="btn btn-red"><i class="ti ti-plus me-1"></i>Nueva Rutina</a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
    <div class="alert <?php echo $_GET['res']==='eliminada' ? 'alert-danger' : 'alert-success'; ?>">
      ✓ <?php echo $_GET['res']==='eliminada' ? 'Rutina eliminada.' : 'Rutina guardada correctamente.'; ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header gray"><h3 class="card-title">Listado de Rutinas</h3></div>
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr><th>Rutina</th><th>Nivel</th><th>Ejercicios</th><th>Socios Asignados</th><th>Acciones</th></tr>
          </thead>
          <tbody>
            <?php if ($rutinas->num_rows === 0): ?>
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted);">
              No hay rutinas. <a href="nuevaRutina.php" style="color:var(--red);">Crea la primera</a>.
            </td></tr>
            <?php endif; ?>
            <?php
$nivel_badge = ['Principiante'=>'badge-green','Intermedio'=>'badge-gold','Avanzado'=>'badge-red'];
while ($r = $rutinas->fetch_assoc()):
  $badge = $nivel_badge[$r['nivel']] ?? 'badge-gray';
?>
            <tr>
              <td>
                <div class="td-name"><?php echo htmlspecialchars($r['nombre_rutina']); ?></div>
                <?php if ($r['descripcion']): ?>
                <div class="td-muted small"><?php echo htmlspecialchars(substr($r['descripcion'],0,55)).(strlen($r['descripcion'])>55?'...':''); ?></div>
                <?php endif; ?>
              </td>
              <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($r['nivel'] ?? '—'); ?></span></td>
              <td class="td-muted"><i class="ti ti-barbell me-1"></i><?php echo $r['total_ejercicios']; ?></td>
              <td class="td-muted"><i class="ti ti-users me-1"></i><?php echo $r['total_asignados']; ?></td>
              <td>
                <div class="btn-list">
                  <a href="asignarRutina.php?id_rutina=<?php echo $r['id_rutina']; ?>" class="btn btn-icon" title="Asignar a socios" style="color:var(--green);">
                    <i class="ti ti-user-plus"></i>
                  </a>
                  <a href="editarRutina.php?id=<?php echo $r['id_rutina']; ?>" class="btn btn-icon edit" title="Editar">
                    <i class="ti ti-edit"></i>
                  </a>
                  <a href="eliminarRutina.php?id=<?php echo $r['id_rutina']; ?>" class="btn btn-icon" title="Eliminar"
                     onclick="return confirm('¿Eliminar esta rutina? Se desasignará de todos los socios.');">
                    <i class="ti ti-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <p class="m-0 text-muted small">Total: <?php echo $rutinas->num_rows; ?> rutinas registradas.</p>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
