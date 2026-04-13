<?php
/**
 * Archivo: evaluaciones.php
 * Descripción: Módulo para visualizar las evaluaciones físicas de los socios.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) {
  header("Location: login.php");
  exit();
}
include 'header.php';

$filtro_socio = isset($_GET['id_socio']) ? (int) $_GET['id_socio'] : 0;

$where = $filtro_socio ? "WHERE ef.id_socio=$filtro_socio" : '';
$evaluaciones = $conexion->query("
    SELECT ef.*, s.nombre, s.apellido, CONCAT(s.nombre,' ',s.apellido) as socio_nombre,
           e.nombre as entrenador_nombre
    FROM evaluaciones_fisicas ef
    LEFT JOIN socios s ON ef.id_socio = s.id_socio
    LEFT JOIN entrenadores e ON ef.id_entrenador = e.id_entrenador
    $where
    ORDER BY ef.id_socio, ef.fecha DESC
");

$socios_lista = $conexion->query("SELECT id_socio, nombre, apellido FROM socios ORDER BY nombre ASC");

// Datos para gráfica (solo si hay filtro por socio)
$chart_labels = $chart_peso = $chart_imc = [];
if ($filtro_socio) {
  $graf = $conexion->query("SELECT fecha, peso, imc FROM evaluaciones_fisicas WHERE id_socio=$filtro_socio ORDER BY fecha ASC LIMIT 12");
  while ($g = $graf->fetch_assoc()) {
    $chart_labels[] = date('d/m/Y', strtotime($g['fecha']));
    $chart_peso[] = (float) $g['peso'];
    $chart_imc[] = (float) $g['imc'];
  }
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Evaluaciones Físicas</h2>
        <p class="page-subtitle">Seguimiento de composición corporal y progreso de socios.</p>
      </div>
      <div class="col-auto">
        <a href="nuevaEvaluacion.php<?php echo $filtro_socio ? '?id_socio=' . $filtro_socio : ''; ?>" class="btn btn-red">
          <i class="ti ti-plus me-1"></i>Nueva Evaluación
        </a>
      </div>
    </div>

    <?php if (isset($_GET['res'])): ?>
      <div class="alert <?php echo $_GET['res'] === 'eliminada' ? 'alert-danger' : 'alert-success'; ?>">
        ✓ <?php echo $_GET['res'] === 'eliminada' ? 'Evaluación eliminada.' : 'Evaluación guardada correctamente.'; ?>
      </div>
    <?php endif; ?>

    <!-- Filtro por socio -->
    <div class="card" style="margin-bottom:20px; padding:16px 20px;">
      <form method="GET" style="display:flex; align-items:center; gap:12px;">
        <label style="font-size:0.85rem; font-weight:600; color:#374151; white-space:nowrap;">Filtrar por socio:</label>
        <select name="id_socio" class="form-select" style="max-width:280px;" onchange="this.form.submit()">
          <option value="">— Todos los socios —</option>
          <?php while ($s = $socios_lista->fetch_assoc()): ?>
            <option value="<?php echo $s['id_socio']; ?>" <?php if ($filtro_socio == $s['id_socio'])
                 echo 'selected'; ?>>
              <?php echo htmlspecialchars($s['nombre'] . ' ' . $s['apellido']); ?>
            </option>
          <?php endwhile; ?>
        </select>
        <?php if ($filtro_socio): ?>
          <a href="evaluaciones.php" class="btn btn-outline" style="padding:8px 14px; font-size:0.85rem;">Ver todos</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Gráfica de progreso (solo si hay filtro) -->
    <?php if ($filtro_socio && count($chart_labels) > 1): ?>
      <div class="card" style="margin-bottom:20px; padding:20px;">
        <div
          style="font-family:'Oswald',sans-serif; font-size:1rem; font-weight:600; color:var(--text); margin-bottom:16px;">
          <i class="ti ti-chart-line me-2" style="color:var(--red);"></i>Progreso de Peso e IMC
        </div>
        <div style="position:relative; height:220px;">
          <canvas id="chartProgreso"></canvas>
        </div>
      </div>
    <?php endif; ?>

    <!-- Tabla -->
    <div class="card">
      <div class="card-header gray">
        <h3 class="card-title">Historial de Evaluaciones</h3>
      </div>
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr>
              <th>Socio</th>
              <th>Fecha</th>
              <th>Peso</th>
              <th>Altura</th>
              <th>IMC</th>
              <th>% Grasa</th>
              <th>Cintura</th>
              <th>Entrenador</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$evaluaciones || $evaluaciones->num_rows === 0): ?>
              <tr>
                <td colspan="9" style="text-align:center; padding:40px; color:var(--muted);">
                  Sin evaluaciones registradas. <a href="nuevaEvaluacion.php" style="color:var(--red);">Agrega la
                    primera</a>.
                </td>
              </tr>
            <?php else: ?>
              <?php while ($ev = $evaluaciones->fetch_assoc()):
                $imc = (float) $ev['imc'];
                if ($imc < 18.5) {
                  $imc_txt = 'Bajo peso';
                  $imc_col = 'badge-gold';
                } elseif ($imc < 25) {
                  $imc_txt = 'Normal';
                  $imc_col = 'badge-green';
                } elseif ($imc < 30) {
                  $imc_txt = 'Sobrepeso';
                  $imc_col = 'badge-gold';
                } else {
                  $imc_txt = 'Obesidad';
                  $imc_col = 'badge-red';
                }
                ?>
                <tr>
                  <td class="td-name"><?php echo htmlspecialchars($ev['socio_nombre']); ?></td>
                  <td class="td-muted"><?php echo date('d/m/Y', strtotime($ev['fecha'])); ?></td>
                  <td class="fw-bold"><?php echo $ev['peso'] ? $ev['peso'] . ' kg' : '—'; ?></td>
                  <td class="td-muted"><?php echo $ev['altura'] ? $ev['altura'] . ' cm' : '—'; ?></td>
                  <td>
                    <?php if ($ev['imc']): ?>
                      <span style="font-weight:700;"><?php echo number_format($ev['imc'], 1); ?></span>
                      <span class="badge <?php echo $imc_col; ?>"
                        style="margin-left:4px; font-size:0.68rem;"><?php echo $imc_txt; ?></span>
                    <?php else: ?>—<?php endif; ?>
                  </td>
                  <td class="td-muted"><?php echo $ev['porcentaje_grasa'] ? $ev['porcentaje_grasa'] . '%' : '—'; ?></td>
                  <td class="td-muted"><?php echo $ev['cintura'] ? $ev['cintura'] . ' cm' : '—'; ?></td>
                  <td class="td-muted"><?php echo htmlspecialchars($ev['entrenador_nombre'] ?? '—'); ?></td>
                  <td>
                    <a href="evaluaciones.php?eliminar=<?php echo $ev['id_evaluacion']; ?>&id_socio=<?php echo $filtro_socio; ?>"
                      class="btn btn-icon" title="Eliminar" onclick="return confirm('¿Eliminar esta evaluación?');">
                      <i class="ti ti-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php if (isset($_GET['eliminar'])): ?>
  <?php
  $id_e = (int) $_GET['eliminar'];
  $conexion->query("DELETE FROM evaluaciones_fisicas WHERE id_evaluacion=$id_e");
  $back = $filtro_socio ? "evaluaciones.php?id_socio=$filtro_socio&res=eliminada" : "evaluaciones.php?res=eliminada";
  echo "<script>window.location='$back';</script>";
  exit;
?>
<?php endif; ?>

<?php if (count($chart_labels) > 1): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
  <script>
    new Chart(document.getElementById('chartProgreso'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [
          {
            label: 'Peso (kg)',
            data: <?php echo json_encode($chart_peso); ?>,
            borderColor: '#B71C1C',
            backgroundColor: 'rgba(183,28,28,0.08)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#B71C1C',
            yAxisID: 'y'
          },
          {
            label: 'IMC',
            data: <?php echo json_encode($chart_imc); ?>,
            borderColor: '#1565C0',
            backgroundColor: 'rgba(21,101,192,0.06)',
            tension: 0.4,
            fill: false,
            pointBackgroundColor: '#1565C0',
            borderDash: [5, 3],
            yAxisID: 'y2'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
          y: { position: 'left', title: { display: true, text: 'Peso (kg)' } },
          y2: { position: 'right', title: { display: true, text: 'IMC' }, grid: { drawOnChartArea: false } }
        }
      }
    });
  </script>
<?php endif; ?>

<?php include 'footer.php'; ?>