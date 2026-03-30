<?php
include 'config.php';
if (!esAdministrador()) {
  header("Location: login.php");
  exit();
}
include 'header.php';

// ── AJUSTAR LÍMITE CALÓRICO ───────────────────────────────
if ($_POST && isset($_POST['ajustar_limite'])) {
  $id_socio = (int) $_POST['id_socio'];
  $limite = (int) $_POST['limite_diario'];
  $motivo = $conexion->real_escape_string($_POST['motivo']);
  $id_admin = $_SESSION['usuario_id'];
  $conexion->query("INSERT INTO socio_calorias_limite (id_socio, limite_diario, motivo, ajustado_por)
                      VALUES ($id_socio, $limite, '$motivo', $id_admin)");
  echo "<script>window.location='nutricionAdmin.php?res=ajustado&id=$id_socio';</script>";
  exit;
}

$hoy = date('Y-m-d');

// ── DETALLE DE UN SOCIO ───────────────────────────────────
$ver_socio = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($ver_socio) {
  $socio = $conexion->query("SELECT s.*, m.nombre as plan, TIMESTAMPDIFF(YEAR, s.fecha_nacimiento, CURDATE()) as edad
        FROM socios s LEFT JOIN membresias m ON s.id_membresia=m.id_membresia
        WHERE s.id_socio=$ver_socio")->fetch_assoc();
  if (!$socio) {
    echo "<script>window.location='nutricionAdmin.php';</script>";
    exit;
  }

  // Límite actual
  $lim_row = $conexion->query("SELECT * FROM socio_calorias_limite WHERE id_socio=$ver_socio ORDER BY fecha_ajuste DESC LIMIT 1")->fetch_assoc();
  $limite = $lim_row ? (int) $lim_row['limite_diario'] : 2000;

  // Asistencias últimos 30 días
  $asist_30 = $conexion->query("SELECT COUNT(*) as t FROM asistencia WHERE id_socio=$ver_socio AND fecha >= DATE_SUB('$hoy', INTERVAL 30 DAY)")->fetch_assoc()['t'];
  $constancia = $asist_30 >= 20 ? 'Excelente' : ($asist_30 >= 12 ? 'Buena' : ($asist_30 >= 6 ? 'Regular' : 'Baja'));
  $constancia_col = $asist_30 >= 20 ? 'badge-green' : ($asist_30 >= 12 ? 'badge-blue' : ($asist_30 >= 6 ? 'badge-gold' : 'badge-red'));

  // Historial calórico últimos 7 días
  $hist = $conexion->query("
        SELECT scc.fecha,
               COALESCE(SUM(cd.calorias),0) as total_cal,
               (SELECT scl.limite_diario FROM socio_calorias_limite scl WHERE scl.id_socio=$ver_socio AND scl.fecha_ajuste <= scc.fecha ORDER BY scl.fecha_ajuste DESC LIMIT 1) as limite_dia
        FROM socio_consumo_calorico scc
        LEFT JOIN consumo_detalle cd ON cd.id_consumo = scc.id_consumo
        WHERE scc.id_socio=$ver_socio
        GROUP BY scc.fecha
        ORDER BY scc.fecha DESC
        LIMIT 7
    ");

  // Detalle de hoy
  $consumo_hoy = $conexion->query("SELECT id_consumo FROM socio_consumo_calorico WHERE id_socio=$ver_socio AND fecha='$hoy'")->fetch_assoc();
  $det_hoy = [];
  $cal_hoy = 0;
  if ($consumo_hoy) {
    $id_c = $consumo_hoy['id_consumo'];
    $res = $conexion->query("SELECT cd.*, a.nombre, a.categoria FROM consumo_detalle cd JOIN alimentos_calorias a ON cd.id_alimento=a.id_alimento WHERE cd.id_consumo=$id_c ORDER BY cd.momento, a.nombre");
    if ($res) {
      while ($d = $res->fetch_assoc()) {
        $det_hoy[] = $d;
        $cal_hoy += $d['calorias'];
      }
    }
  }
  $pct = $limite > 0 ? min(100, round($cal_hoy / $limite * 100)) : 0;
  $estado_cal = $cal_hoy > $limite * 1.05 ? 'Fuera del límite' : ($cal_hoy >= $limite * 0.95 ? 'En el límite' : 'Dentro del límite');
  $estado_col = $cal_hoy > $limite * 1.05 ? '#DC2626' : ($cal_hoy >= $limite * 0.95 ? '#D97706' : '#15803D');

  // Historial ajustes de límite
  $hist_lim = $conexion->query("SELECT scl.*, u.nombre_completo FROM socio_calorias_limite scl LEFT JOIN usuarios u ON scl.ajustado_por=u.id_usuario WHERE scl.id_socio=$ver_socio ORDER BY scl.fecha_ajuste DESC LIMIT 5");
}

// ── LISTA RESUMEN DE TODOS LOS SOCIOS ────────────────────
$socios_resumen = $conexion->query("
    SELECT s.id_socio, s.nombre, s.apellido, s.foto,
           TIMESTAMPDIFF(YEAR, s.fecha_nacimiento, CURDATE()) as edad,
           COALESCE((SELECT scl.limite_diario FROM socio_calorias_limite scl WHERE scl.id_socio=s.id_socio ORDER BY scl.fecha_ajuste DESC LIMIT 1), 2000) as limite,
           (SELECT COUNT(*) FROM asistencia a WHERE a.id_socio=s.id_socio AND a.fecha >= DATE_SUB('$hoy', INTERVAL 30 DAY)) as asist_30,
           COALESCE((SELECT SUM(cd2.calorias) FROM socio_consumo_calorico scc2
                     JOIN consumo_detalle cd2 ON cd2.id_consumo=scc2.id_consumo
                     WHERE scc2.id_socio=s.id_socio AND scc2.fecha='$hoy'), 0) as cal_hoy
    FROM socios s WHERE s.estado='activo'
    ORDER BY s.nombre ASC
");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Monitor Nutricional</h2>
        <p class="page-subtitle">Seguimiento calórico y continuidad de los socios.</p>
      </div>
      <div class="col-auto" style="display:flex; gap:10px;">
        <a href="gestionAlimentos.php" class="btn btn-outline"><i class="ti ti-database me-1"></i>Catálogo Alimentos</a>
        <a href="evaluaciones.php" class="btn btn-outline"><i class="ti ti-arrow-left me-1"></i>Evaluaciones</a>
      </div>
    </div>

    <?php if (isset($_GET['res']) && $_GET['res'] === 'ajustado'): ?>
      <div class="alert alert-success"><i class="ti ti-circle-check me-1"></i>Límite calórico actualizado correctamente.
      </div>
    <?php endif; ?>

    <?php if ($ver_socio && isset($socio)): ?>
      <!-- ══════════════ VISTA DETALLE SOCIO ══════════════ -->
      <div style="display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start;">

        <div style="display:flex; flex-direction:column; gap:18px;">

          <!-- Perfil + KPIs -->
          <div class="card">
            <div class="card-body" style="padding:20px;">
              <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
                <?php if (!empty($socio['foto'])): ?>
                  <img src="<?php echo htmlspecialchars($socio['foto']); ?>"
                    style="width:56px;height:56px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                  <div
                    style="width:56px;height:56px;border-radius:50%;background:#F3F4F6;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#9CA3AF;">
                    <i class="ti ti-user"></i></div>
                <?php endif; ?>
                <div>
                  <div style="font-family:'Oswald',sans-serif; font-size:1.4rem; font-weight:700;">
                    <?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']); ?></div>
                  <div style="font-size:0.82rem; color:var(--muted);">
                    <?php echo $socio['edad'] ? $socio['edad'] . ' años' : '—'; ?> &nbsp;·&nbsp;
                    <?php echo htmlspecialchars($socio['plan'] ?? 'Sin plan'); ?></div>
                </div>
                <a href="nutricionAdmin.php" style="margin-left:auto;" class="btn btn-link"><i
                    class="ti ti-arrow-left me-1"></i>Volver</a>
              </div>

              <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                <div style="text-align:center; background:#F9FAFB; border-radius:8px; padding:14px;">
                  <div
                    style="font-family:'Oswald',sans-serif; font-size:1.8rem; font-weight:700; color:<?php echo $estado_col; ?>;">
                    <?php echo number_format($cal_hoy); ?></div>
                  <div style="font-size:0.74rem; color:var(--muted);">kcal hoy</div>
                </div>
                <div style="text-align:center; background:#F9FAFB; border-radius:8px; padding:14px;">
                  <div style="font-family:'Oswald',sans-serif; font-size:1.8rem; font-weight:700; color:var(--text);">
                    <?php echo number_format($limite); ?></div>
                  <div style="font-size:0.74rem; color:var(--muted);">Límite diario</div>
                </div>
                <div style="text-align:center; background:#F9FAFB; border-radius:8px; padding:14px;">
                  <div style="font-family:'Oswald',sans-serif; font-size:1.8rem; font-weight:700; color:var(--text);">
                    <?php echo $asist_30; ?></div>
                  <div style="font-size:0.74rem; color:var(--muted);">Visitas (30 días)</div>
                </div>
                <div style="text-align:center; background:#F9FAFB; border-radius:8px; padding:14px;">
                  <div style="margin-top:4px;"><span class="badge <?php echo $constancia_col; ?>"
                      style="font-size:0.8rem;"><?php echo $constancia; ?></span></div>
                  <div style="font-size:0.74rem; color:var(--muted); margin-top:4px;">Continuidad</div>
                </div>
              </div>

              <!-- Barra de progreso calórico -->
              <div style="margin-top:18px;">
                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:6px;">
                  <span style="color:var(--muted);">Progreso calórico hoy</span>
                  <span style="font-weight:700; color:<?php echo $estado_col; ?>"><?php echo $pct; ?>% —
                    <?php echo $estado_cal; ?></span>
                </div>
                <div style="background:#F3F4F6; border-radius:999px; height:10px; overflow:hidden;">
                  <div
                    style="height:100%; width:<?php echo $pct; ?>%; background:<?php echo $estado_col; ?>; border-radius:999px; transition:width .5s;">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Consumo de hoy -->
          <div class="card">
            <div class="card-header gray">
              <h3 class="card-title"><i class="ti ti-salad me-2" style="color:var(--green);"></i>Alimentos de Hoy —
                <?php echo date('d/m/Y'); ?></h3>
            </div>
            <?php if (empty($det_hoy)): ?>
              <div style="padding:30px; text-align:center; color:var(--muted);">Sin registro de alimentos para hoy.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="gym-table">
                  <thead>
                    <tr>
                      <th>Alimento</th>
                      <th>Categoría</th>
                      <th>Momento</th>
                      <th>Gramos</th>
                      <th>Calorías</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($det_hoy as $d): ?>
                      <tr>
                        <td class="td-name"><?php echo htmlspecialchars($d['nombre']); ?></td>
                        <td><span class="badge badge-blue"
                            style="font-size:0.67rem;"><?php echo htmlspecialchars($d['categoria']); ?></span></td>
                        <td class="td-muted"><?php echo htmlspecialchars($d['momento']); ?></td>
                        <td class="td-muted"><?php echo $d['gramos']; ?>g</td>
                        <td class="fw-bold"><?php echo $d['calorias']; ?> kcal</td>
                      </tr>
                    <?php endforeach; ?>
                    <tr style="background:#F9FAFB; font-weight:700;">
                      <td colspan="4" style="text-align:right; padding:12px 16px;">Total:</td>
                      <td
                        style="padding:12px 16px; color:<?php echo $estado_col; ?>; font-family:'Oswald',sans-serif; font-size:1.1rem;">
                        <?php echo number_format($cal_hoy); ?> kcal</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- Historial 7 días -->
          <div class="card">
            <div class="card-header gray">
              <h3 class="card-title">Historial Últimos 7 Días</h3>
            </div>
            <div class="table-responsive">
              <table class="gym-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Calorías Consumidas</th>
                    <th>Límite</th>
                    <th>Diferencia</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($hist) {
                    $hist->data_seek(0);
                    while ($h = $hist->fetch_assoc()):
                      $lim_d = $h['limite_dia'] ?: 2000;
                      $dif = $h['total_cal'] - $lim_d;
                      $e_col = $h['total_cal'] > $lim_d * 1.05 ? 'badge-red' : ($h['total_cal'] >= $lim_d * 0.95 ? 'badge-gold' : 'badge-green');
                      $e_txt = $h['total_cal'] > $lim_d * 1.05 ? 'Fuera del límite' : ($h['total_cal'] >= $lim_d * 0.95 ? 'En el límite' : 'Dentro del límite');
                      ?>
                      <tr>
                        <td class="td-muted"><?php echo date('d/m/Y', strtotime($h['fecha'])); ?></td>
                        <td class="fw-bold"><?php echo number_format($h['total_cal']); ?> kcal</td>
                        <td class="td-muted"><?php echo number_format($lim_d); ?> kcal</td>
                        <td style="color:<?php echo $dif > 0 ? 'var(--danger)' : 'var(--green)'; ?>; font-weight:700;">
                          <?php echo ($dif > 0 ? '+' : '') . number_format($dif); ?> kcal
                        </td>
                        <td><span class="badge <?php echo $e_col; ?>"><?php echo $e_txt; ?></span></td>
                      </tr>
                    <?php endwhile;
                  } ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <!-- PANEL DERECHO ────────────────────────────────── -->
        <div style="display:flex; flex-direction:column; gap:16px;">

          <!-- Ajustar límite -->
          <form method="POST" class="card">
            <div class="card-header" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
              <span class="card-title" style="color:#fff;"><i class="ti ti-adjustments me-2"></i>Ajustar Límite
                Calórico</span>
            </div>
            <div class="card-body">
              <input type="hidden" name="ajustar_limite" value="1">
              <input type="hidden" name="id_socio" value="<?php echo $ver_socio; ?>">
              <div style="margin-bottom:14px;">
                <label class="form-label">Nuevo Límite Diario (kcal)</label>
                <input type="number" name="limite_diario" class="form-control" value="<?php echo $limite; ?>" min="500"
                  max="6000" required>
                <small style="color:var(--muted); font-size:0.75rem;">Actual: <?php echo number_format($limite); ?>
                  kcal/día</small>
              </div>
              <div>
                <label class="form-label">Motivo del Ajuste</label>
                <textarea name="motivo" class="form-control" rows="2"
                  placeholder="Ej. Buena asistencia en el mes, se aumenta límite..."></textarea>
              </div>
              <?php if ($asist_30 >= 20): ?>
                <div
                  style="background:#DCFCE7; border-left:3px solid var(--green); border-radius:0 6px 6px 0; padding:10px 12px; margin-top:12px; font-size:0.8rem; color:#15803D;">
                  <i class="ti ti-star me-1"></i> <strong>Asistencia excelente:</strong> <?php echo $asist_30; ?> visitas en
                  30 días. Considera aumentar el límite.
                </div>
              <?php elseif ($asist_30 < 6): ?>
                <div
                  style="background:#FEF3C7; border-left:3px solid var(--gold); border-radius:0 6px 6px 0; padding:10px 12px; margin-top:12px; font-size:0.8rem; color:#92400E;">
                  <i class="ti ti-alert-triangle me-1"></i> <strong>Baja asistencia:</strong> solo <?php echo $asist_30; ?>
                  visitas en 30 días.
                </div>
              <?php endif; ?>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-red" style="width:100%; justify-content:center;">
                <i class="ti ti-device-floppy me-1"></i>Guardar Límite
              </button>
            </div>
          </form>

          <!-- Historial de ajustes -->
          <div class="card">
            <div class="card-header gray">
              <h3 class="card-title" style="font-size:0.95rem;">Historial de Ajustes</h3>
            </div>
            <div style="padding:0;">
              <?php
              $hay_hist = false;
              if ($hist_lim) {
                $hist_lim->data_seek(0);
                while ($hl = $hist_lim->fetch_assoc()):
                  $hay_hist = true;
                  ?>
                  <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:0.82rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span class="fw-bold"><?php echo number_format($hl['limite_diario']); ?> kcal/día</span>
                      <span class="td-muted"><?php echo date('d/m/Y', strtotime($hl['fecha_ajuste'])); ?></span>
                    </div>
                    <?php if ($hl['motivo']): ?>
                      <div style="color:var(--muted); margin-top:2px;"><?php echo htmlspecialchars($hl['motivo']); ?></div>
                    <?php endif; ?>
                  </div>
                <?php endwhile;
              } ?>
              <?php if (!$hay_hist): ?>
                <div style="padding:16px; text-align:center; color:var(--muted); font-size:0.82rem;">Sin ajustes previos.
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

    <?php else: ?>
      <!-- ══════════════ VISTA LISTA SOCIOS ══════════════ -->
      <div class="card">
        <div class="card-header gray">
          <h3 class="card-title">Socios Activos — Seguimiento Calórico</h3>
        </div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr>
                <th>Socio</th>
                <th>Edad</th>
                <th>Calorías Hoy</th>
                <th>Límite</th>
                <th>Estado Hoy</th>
                <th>Visitas (30d)</th>
                <th>Continuidad</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($socios_resumen) {
                while ($s = $socios_resumen->fetch_assoc()):
                  $lim = (int) $s['limite'];
                  $cal = (int) $s['cal_hoy'];
                  $pct_s = $lim > 0 ? min(100, round($cal / $lim * 100)) : 0;
                  $e_col_s = $cal > $lim * 1.05 ? 'badge-red' : ($cal >= $lim * 0.95 ? 'badge-gold' : ($cal > 0 ? 'badge-green' : 'badge-gray'));
                  $e_txt_s = $cal > $lim * 1.05 ? 'Fuera' : ($cal >= $lim * 0.95 ? 'En el límite' : ($cal > 0 ? 'Dentro' : 'Sin registro'));
                  $asist = (int) $s['asist_30'];
                  $con_txt = $asist >= 20 ? 'Excelente' : ($asist >= 12 ? 'Buena' : ($asist >= 6 ? 'Regular' : 'Baja'));
                  $con_col = $asist >= 20 ? 'badge-green' : ($asist >= 12 ? 'badge-blue' : ($asist >= 6 ? 'badge-gold' : 'badge-red'));
                  ?>
                  <tr>
                    <td>
                      <div style="display:flex;align-items:center;gap:10px;">
                        <?php if (!empty($s['foto'])): ?>
                          <img src="<?php echo htmlspecialchars($s['foto']); ?>"
                            style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                          <div
                            style="width:32px;height:32px;border-radius:50%;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:#9CA3AF;font-size:0.9rem;">
                            <i class="ti ti-user"></i></div>
                        <?php endif; ?>
                        <span class="td-name"><?php echo htmlspecialchars($s['nombre'] . ' ' . $s['apellido']); ?></span>
                      </div>
                    </td>
                    <td class="td-muted"><?php echo $s['edad'] ?: '—'; ?></td>
                    <td>
                      <span class="fw-bold"><?php echo $cal > 0 ? number_format($cal) . ' kcal' : '—'; ?></span>
                      <?php if ($cal > 0): ?>
                        <div
                          style="background:#F3F4F6;border-radius:999px;height:4px;margin-top:4px;overflow:hidden;width:80px;">
                          <div
                            style="height:100%;width:<?php echo $pct_s; ?>%;background:<?php echo $cal > $lim * 1.05 ? '#DC2626' : ($cal >= $lim * 0.95 ? '#D97706' : '#15803D'); ?>;border-radius:999px;">
                          </div>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="td-muted"><?php echo number_format($lim); ?> kcal</td>
                    <td><span class="badge <?php echo $e_col_s; ?>"><?php echo $e_txt_s; ?></span></td>
                    <td class="td-muted"><?php echo $asist; ?></td>
                    <td><span class="badge <?php echo $con_col; ?>"><?php echo $con_txt; ?></span></td>
                    <td>
                      <a href="nutricionAdmin.php?id=<?php echo $s['id_socio']; ?>" class="btn btn-icon edit"
                        title="Ver detalle">
                        <i class="ti ti-eye"></i>
                      </a>
                    </td>
                  </tr>
                <?php endwhile;
              } ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
<?php include 'footer.php'; ?>