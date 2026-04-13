<?php
/**
 * Archivo: miNutricion.php
 * Descripción: Panel para que los socios vean su seguimiento nutricional y límite calórico.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
// Accesible para socios y admins
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}

$es_admin = esAdministrador();
$es_socio = esSocio();
$id_socio = $es_socio ? (int) $_SESSION['usuario_id'] : (isset($_GET['id_socio']) ? (int) $_GET['id_socio'] : 0);

if (!$id_socio) {
  header("Location: " . ($es_admin ? "nutricionAdmin.php" : "inicioSocio.php"));
  exit();
}

include 'header.php';

$hoy = date('Y-m-d');

// ── AGREGAR ALIMENTO ──────────────────────────────────────
if ($_POST && isset($_POST['agregar_alimento'])) {
  $id_alim = (int) $_POST['id_alimento'];
  $gramos = max(1, (int) $_POST['gramos']);
  $momento = $conexion->real_escape_string($_POST['momento']);
  $fecha_r = $conexion->real_escape_string($_POST['fecha_registro'] ?? $hoy);

  // Obtener o crear el registro del día
  $reg_dia = $conexion->query("SELECT id_consumo FROM socio_consumo_calorico WHERE id_socio=$id_socio AND fecha='$fecha_r'")->fetch_assoc();
  if (!$reg_dia) {
    $conexion->query("INSERT INTO socio_consumo_calorico (id_socio, fecha) VALUES ($id_socio, '$fecha_r')");
    $id_consumo = $conexion->insert_id;
  } else {
    $id_consumo = $reg_dia['id_consumo'];
  }

  // Calcular calorías según gramos
  $alim = $conexion->query("SELECT calorias_100g FROM alimentos_calorias WHERE id_alimento=$id_alim")->fetch_assoc();
  $cals = $alim ? round($alim['calorias_100g'] * $gramos / 100) : 0;

  $conexion->query("INSERT INTO consumo_detalle (id_consumo, id_alimento, gramos, calorias, momento) VALUES ($id_consumo, $id_alim, $gramos, $cals, '$momento')");
  echo "<script>window.location='miNutricion.php?id_socio=$id_socio&fecha=$fecha_r&res=agregado';</script>";
  exit;
}

// ── ELIMINAR ALIMENTO DEL DÍA ─────────────────────────────
if (isset($_GET['quitar'])) {
  $id_det = (int) $_GET['quitar'];
  $fecha_q = $conexion->real_escape_string($_GET['fecha'] ?? $hoy);
  // Verificar que pertenece al socio
  $res_ok = $conexion->query("SELECT cd.id_detalle FROM consumo_detalle cd LEFT JOIN socio_consumo_calorico scc ON cd.id_consumo=scc.id_consumo WHERE cd.id_detalle=$id_det AND scc.id_socio=$id_socio");
  $ok = $res_ok && $res_ok->num_rows > 0;
  if ($ok)
    $conexion->query("DELETE FROM consumo_detalle WHERE id_detalle=$id_det");
  echo "<script>window.location='miNutricion.php?id_socio=$id_socio&fecha=$fecha_q';</script>";
  exit;
}

// ── DATOS DEL SOCIO ───────────────────────────────────────
$socio = $conexion->query("SELECT s.*, TIMESTAMPDIFF(YEAR, s.fecha_nacimiento, CURDATE()) as edad FROM socios s WHERE s.id_socio=$id_socio")->fetch_assoc();
if (!$socio) {
  echo "<script>window.location='inicioSocio.php';</script>";
  exit;
}

// Fecha que se está viendo
$fecha_ver = $conexion->real_escape_string($_GET['fecha'] ?? $hoy);

// Límite calórico
$lim_row = $conexion->query("SELECT limite_diario FROM socio_calorias_limite WHERE id_socio=$id_socio ORDER BY fecha_ajuste DESC LIMIT 1")->fetch_assoc();
$limite = $lim_row ? (int) $lim_row['limite_diario'] : 2000;

// Consumo del día seleccionado
$reg_dia = $conexion->query("SELECT id_consumo FROM socio_consumo_calorico WHERE id_socio=$id_socio AND fecha='$fecha_ver'")->fetch_assoc();
$detalles = [];
$cal_total = 0;
if ($reg_dia) {
  $id_c = $reg_dia['id_consumo'];
  $res = $conexion->query("SELECT cd.*, a.nombre, a.categoria, a.calorias_100g FROM consumo_detalle cd LEFT JOIN alimentos_calorias a ON cd.id_alimento=a.id_alimento WHERE cd.id_consumo=$id_c ORDER BY cd.momento, a.nombre");
  if ($res) {
    while ($d = $res->fetch_assoc()) {
      $detalles[] = $d;
      $cal_total += $d['calorias'];
    }
  }
}

$pct = $limite > 0 ? min(100, round($cal_total / $limite * 100)) : 0;
$restante = max(0, $limite - $cal_total);
$exceso = max(0, $cal_total - $limite);
$estado_txt = $cal_total > $limite * 1.05 ? 'Fuera del límite' : ($cal_total >= $limite * 0.95 ? 'En el límite' : 'Dentro del límite');
$estado_col = $cal_total > $limite * 1.05 ? '#DC2626' : ($cal_total >= $limite * 0.95 ? '#D97706' : '#15803D');
$estado_badge = $cal_total > $limite * 1.05 ? 'badge-red' : ($cal_total >= $limite * 0.95 ? 'badge-gold' : 'badge-green');

// Asistencias últimos 30 días
$asist_30 = $conexion->query("SELECT COUNT(*) as t FROM asistencia WHERE id_socio=$id_socio AND fecha >= DATE_SUB('$hoy', INTERVAL 30 DAY)")->fetch_assoc()['t'];
$constancia_txt = $asist_30 >= 20 ? 'Excelente' : ($asist_30 >= 12 ? 'Buena' : ($asist_30 >= 6 ? 'Regular' : 'Baja'));
$constancia_col = $asist_30 >= 20 ? 'badge-green' : ($asist_30 >= 12 ? 'badge-blue' : ($asist_30 >= 6 ? 'badge-gold' : 'badge-red'));

// Historial 7 días para gráfica
$hist7 = $conexion->query("
    SELECT scc.fecha,
           COALESCE(SUM(cd.calorias),0) as total_cal
    FROM socio_consumo_calorico scc
    LEFT JOIN consumo_detalle cd ON cd.id_consumo=scc.id_consumo
    WHERE scc.id_socio=$id_socio
    GROUP BY scc.fecha
    ORDER BY scc.fecha DESC LIMIT 7
");
$chart_labels = $chart_data = [];
if ($hist7) {
  while ($h = $hist7->fetch_assoc()) {
    $chart_labels[] = date('d/m', strtotime($h['fecha']));
    $chart_data[] = (int) $h['total_cal'];
  }
}
$chart_labels = array_reverse($chart_labels);
$chart_data = array_reverse($chart_data);

// Alimentos disponibles para el selector
$alimentos_json_q = $conexion->query("SELECT id_alimento, categoria, nombre, calorias_100g FROM alimentos_calorias WHERE estado='activo' ORDER BY categoria, nombre ASC");
$alimentos_arr = [];
if ($alimentos_json_q) {
  while ($a = $alimentos_json_q->fetch_assoc())
    $alimentos_arr[] = $a;
}
$alimentos_json = json_encode($alimentos_arr);
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Mi Seguimiento Nutricional</h2>
        <p class="page-subtitle">
          <?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']); ?>
          <?php if ($socio['edad']): ?> · <?php echo $socio['edad']; ?> años<?php endif; ?>
        </p>
      </div>
      <?php if ($es_admin): ?>
        <div class="col-auto">
          <a href="nutricionAdmin.php?id=<?php echo $id_socio; ?>" class="btn btn-outline"><i
              class="ti ti-arrow-left me-1"></i>Monitor Admin</a>
        </div>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['res']) && $_GET['res'] === 'agregado'): ?>
      <div class="alert alert-success"><i class="ti ti-circle-check me-1"></i>Alimento registrado correctamente.</div>
    <?php endif; ?>

    <!-- KPIs ─────────────────────────────────────────────── -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px;">
      <div class="card" style="padding:18px; text-align:center;">
        <div
          style="font-family:'Oswald',sans-serif; font-size:2rem; font-weight:700; color:<?php echo $estado_col; ?>;">
          <?php echo number_format($cal_total); ?></div>
        <div style="font-size:0.76rem; color:var(--muted);">kcal consumidas hoy</div>
      </div>
      <div class="card" style="padding:18px; text-align:center;">
        <div style="font-family:'Oswald',sans-serif; font-size:2rem; font-weight:700;">
          <?php echo number_format($limite); ?></div>
        <div style="font-size:0.76rem; color:var(--muted);">Límite diario</div>
      </div>
      <div class="card" style="padding:18px; text-align:center;">
        <div
          style="font-family:'Oswald',sans-serif; font-size:2rem; font-weight:700; color:<?php echo $exceso > 0 ? 'var(--red)' : 'var(--green)'; ?>;">
          <?php echo $exceso > 0 ? '+' . number_format($exceso) : number_format($restante); ?>
        </div>
        <div style="font-size:0.76rem; color:var(--muted);">
          <?php echo $exceso > 0 ? 'kcal excedidas' : 'kcal disponibles'; ?></div>
      </div>
      <div class="card" style="padding:18px; text-align:center;">
        <div style="margin-top:4px;"><span class="badge <?php echo $estado_badge; ?>"
            style="font-size:0.85rem;"><?php echo $estado_txt; ?></span></div>
        <div style="font-size:0.76rem; color:var(--muted); margin-top:6px;">Estado calórico</div>
      </div>
    </div>

    <!-- Barra de progreso ────────────────────────────────── -->
    <div class="card" style="margin-bottom:20px; padding:18px 22px;">
      <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:8px;">
        <span style="color:var(--muted);">Progreso del día —
          <?php echo date('d \d\e F', strtotime($fecha_ver)); ?></span>
        <span style="font-weight:700;"><?php echo $pct; ?>%</span>
      </div>
      <div style="background:#F3F4F6; border-radius:999px; height:14px; overflow:hidden;">
        <div
          style="height:100%; width:<?php echo $pct; ?>%; background:<?php echo $estado_col; ?>; border-radius:999px; transition:width .6s;">
        </div>
      </div>
      <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:0.74rem; color:var(--muted);">
        <span>0 kcal</span>
        <span><?php echo number_format($limite); ?> kcal (límite)</span>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start;">

      <!-- LADO IZQUIERDO ──────────────────────────────────── -->
      <div style="display:flex; flex-direction:column; gap:18px;">

        <!-- Tabla de alimentos del día -->
        <div class="card">
          <div class="card-header gray" style="justify-content:space-between;">
            <h3 class="card-title"><i class="ti ti-salad me-2" style="color:var(--green);"></i>Alimentos Registrados
            </h3>
            <!-- Selector de fecha -->
            <form method="GET" style="display:flex; align-items:center; gap:8px;">
              <input type="hidden" name="id_socio" value="<?php echo $id_socio; ?>">
              <label style="font-size:0.78rem; color:var(--muted);">Fecha:</label>
              <input type="date" name="fecha" class="form-control" value="<?php echo $fecha_ver; ?>"
                max="<?php echo $hoy; ?>" style="width:160px; padding:6px 10px; font-size:0.82rem;"
                onchange="this.form.submit()">
            </form>
          </div>

          <?php if (empty($detalles)): ?>
            <div style="padding:36px; text-align:center; color:var(--muted);">
              <i class="ti ti-bowl" style="font-size:2.5rem; display:block; margin-bottom:10px;"></i>
              Sin alimentos registrados para este día. Agrega tu primer alimento.
            </div>
          <?php else: ?>

            <!-- Agrupar por momento -->
            <?php
            $momentos_orden = ['Desayuno', 'Almuerzo', 'Cena', 'Snack'];
            $por_momento = [];
            foreach ($detalles as $d)
              $por_momento[$d['momento']][] = $d;
            foreach ($momentos_orden as $mom):
              if (!isset($por_momento[$mom]))
                continue;
              $items = $por_momento[$mom];
              $cal_mom = array_sum(array_column($items, 'calorias'));
              ?>
              <div style="border-bottom:1px solid var(--border);">
                <div
                  style="padding:10px 16px 6px; background:#F9FAFB; font-family:'Oswald',sans-serif; font-size:0.85rem; font-weight:600; color:var(--text); display:flex; justify-content:space-between;">
                  <span><?php echo $mom; ?></span>
                  <span style="color:var(--muted); font-weight:400;"><?php echo number_format($cal_mom); ?> kcal</span>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                  <tbody>
                    <?php foreach ($items as $det): ?>
                      <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 16px;">
                          <div style="font-weight:600; font-size:0.88rem;"><?php echo htmlspecialchars($det['nombre']); ?>
                          </div>
                          <div style="font-size:0.74rem; color:var(--muted);">
                            <?php echo htmlspecialchars($det['categoria']); ?></div>
                        </td>
                        <td style="padding:10px; color:var(--muted); font-size:0.82rem;"><?php echo $det['gramos']; ?>g</td>
                        <td style="padding:10px 16px; font-weight:700; font-family:'Oswald',sans-serif;">
                          <?php echo $det['calorias']; ?> kcal</td>
                        <td style="padding:10px; width:44px;">
                          <?php if ($fecha_ver === $hoy): ?>
                            <a href="miNutricion.php?id_socio=<?php echo $id_socio; ?>&quitar=<?php echo $det['id_detalle']; ?>&fecha=<?php echo $fecha_ver; ?>"
                              class="btn btn-icon" title="Quitar" onclick="return confirm('¿Quitar este alimento?');"
                              style="width:30px;height:30px;">
                              <i class="ti ti-x" style="font-size:0.85rem;"></i>
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endforeach; ?>

            <!-- Total -->
            <div
              style="padding:14px 16px; display:flex; justify-content:space-between; align-items:center; background:#F9FAFB;">
              <span style="font-weight:700;">Total del día</span>
              <span
                style="font-family:'Oswald',sans-serif; font-size:1.3rem; font-weight:700; color:<?php echo $estado_col; ?>;">
                <?php echo number_format($cal_total); ?> <span style="font-size:0.85rem;">kcal</span>
              </span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Gráfica 7 días -->
        <?php if (!empty($chart_labels)): ?>
          <div class="card" style="padding:20px;">
            <div
              style="font-family:'Oswald',sans-serif; font-size:1rem; font-weight:600; color:var(--text); margin-bottom:14px;">
              <i class="ti ti-chart-bar me-2" style="color:var(--red);"></i>Últimos 7 días de consumo
            </div>
            <div style="position:relative; height:200px;">
              <canvas id="chart7dias"></canvas>
            </div>
          </div>
        <?php endif; ?>

        <!-- Continuidad -->
        <div class="card" style="padding:18px 22px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-family:'Oswald',sans-serif; font-size:1rem; font-weight:600; margin-bottom:4px;">Mi
                Continuidad en el Gimnasio</div>
              <div style="font-size:0.82rem; color:var(--muted);">Asistencias registradas en los últimos 30 días</div>
            </div>
            <div style="text-align:right;">
              <div style="font-family:'Oswald',sans-serif; font-size:2.2rem; font-weight:700;"><?php echo $asist_30; ?>
              </div>
              <span class="badge <?php echo $constancia_col; ?>"><?php echo $constancia_txt; ?></span>
            </div>
          </div>
          <?php if ($asist_30 >= 20): ?>
            <div
              style="margin-top:12px; background:#DCFCE7; border-radius:6px; padding:10px 14px; font-size:0.82rem; color:#15803D;">
              <i class="ti ti-star me-1"></i> ¡Excelente asistencia! Tu entrenador puede ajustar tu límite calórico.
            </div>
          <?php elseif ($asist_30 < 6): ?>
            <div
              style="margin-top:12px; background:#FEF3C7; border-radius:6px; padding:10px 14px; font-size:0.82rem; color:#92400E;">
              <i class="ti ti-flame me-1"></i> Incrementa tu asistencia para mejores resultados.
            </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- FORMULARIO AGREGAR ALIMENTO ────────────────────── -->
      <div style="position:sticky; top:80px;">
        <form method="POST" class="card" id="form-alimento">
          <div class="card-header" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
            <span class="card-title" style="color:#fff;"><i class="ti ti-plus me-2"></i>Agregar Alimento</span>
          </div>
          <div class="card-body">
            <input type="hidden" name="agregar_alimento" value="1">
            <input type="hidden" name="fecha_registro" value="<?php echo $fecha_ver; ?>">

            <div style="margin-bottom:14px;">
              <label class="form-label">Buscar Alimento</label>
              <input type="text" id="buscador" class="form-control" placeholder="Escribe para buscar..."
                autocomplete="off">
              <input type="hidden" name="id_alimento" id="id_alimento_hidden" required>
              <div id="lista-sugerencias"
                style="display:none; position:absolute; z-index:200; background:#fff; border:1px solid var(--border); border-radius:6px; max-height:200px; overflow-y:auto; width:292px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
              </div>
              <div id="alimento-sel"
                style="display:none; margin-top:8px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:6px; padding:8px 12px; font-size:0.82rem; color:#15803D;">
              </div>
            </div>

            <div style="margin-bottom:14px;">
              <label class="form-label">Momento del día</label>
              <select name="momento" class="form-select">
                <option value="Desayuno">Desayuno</option>
                <option value="Almuerzo" selected>Almuerzo</option>
                <option value="Cena">Cena</option>
                <option value="Snack">Snack</option>
              </select>
            </div>

            <div style="margin-bottom:8px;">
              <label class="form-label">Cantidad (gramos)</label>
              <input type="number" name="gramos" id="gramos" class="form-control" value="100" min="1" max="2000"
                oninput="actualizarCal()">
            </div>

            <!-- Preview de calorías -->
            <div id="preview-cal"
              style="display:none; background:#FEF2F2; border:1px solid #FCA5A5; border-radius:6px; padding:10px 14px; margin-top:4px; text-align:center;">
              <span style="font-family:'Oswald',sans-serif; font-size:1.5rem; font-weight:700; color:var(--red);"
                id="cal-preview">0</span>
              <span style="font-size:0.8rem; color:var(--muted);"> kcal estimadas</span>
            </div>

          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-red" style="width:100%; justify-content:center;" id="btn-agregar"
              disabled>
              <i class="ti ti-plus me-1"></i>Agregar al Registro
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
  const alimentos = <?php echo $alimentos_json; ?>;
  const limite = <?php echo $limite; ?>;
  let selAlimento = null;

  // ── Buscador ─────────────────────────────────────────────
  const inp = document.getElementById('buscador');
  const lista = document.getElementById('lista-sugerencias');
  const hidden = document.getElementById('id_alimento_hidden');
  const selDiv = document.getElementById('alimento-sel');
  const btnAg = document.getElementById('btn-agregar');

  inp.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    lista.innerHTML = '';
    selAlimento = null;
    hidden.value = '';
    selDiv.style.display = 'none';
    btnAg.disabled = true;
    document.getElementById('preview-cal').style.display = 'none';

    if (q.length < 2) { lista.style.display = 'none'; return; }

    const res = alimentos.filter(a => a.nombre.toLowerCase().includes(q) || a.categoria.toLowerCase().includes(q)).slice(0, 12);
    if (!res.length) { lista.style.display = 'none'; return; }

    res.forEach(a => {
      const div = document.createElement('div');
      div.style.cssText = 'padding:9px 14px; cursor:pointer; border-bottom:1px solid #F3F4F6; font-size:0.85rem;';
      div.innerHTML = `<strong>${a.nombre}</strong> <span style="color:#9CA3AF; font-size:0.76rem;">${a.categoria}</span><br><span style="color:#B71C1C; font-weight:700;">${a.calorias_100g} kcal/100g</span>`;
      div.addEventListener('mouseenter', () => div.style.background = '#F9FAFB');
      div.addEventListener('mouseleave', () => div.style.background = '#fff');
      div.addEventListener('click', () => {
        selAlimento = a;
        hidden.value = a.id_alimento;
        inp.value = a.nombre;
        lista.style.display = 'none';
        selDiv.style.display = 'block';
        selDiv.innerHTML = `<i class="ti ti-circle-check"></i> <strong>${a.nombre}</strong> — ${a.calorias_100g} kcal por 100g`;
        btnAg.disabled = false;
        actualizarCal();
      });
      lista.appendChild(div);
    });
    lista.style.display = 'block';
    // Position
    const rect = inp.getBoundingClientRect();
    lista.style.width = inp.offsetWidth + 'px';
  });

  document.addEventListener('click', e => { if (!lista.contains(e.target) && e.target !== inp) lista.style.display = 'none'; });

  function actualizarCal() {
    if (!selAlimento) return;
    const g = parseInt(document.getElementById('gramos').value) || 100;
    const cal = Math.round(selAlimento.calorias_100g * g / 100);
    document.getElementById('cal-preview').textContent = cal;
    document.getElementById('preview-cal').style.display = 'block';
  }

  // ── Gráfica 7 días ────────────────────────────────────────
  <?php if (!empty($chart_labels)): ?>
    const ctx = document.getElementById('chart7dias');
    const labelsChart = <?php echo json_encode($chart_labels); ?>;
    const dataChart = <?php echo json_encode($chart_data); ?>;

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labelsChart,
        datasets: [{
          label: 'kcal consumidas',
          data: dataChart,
          backgroundColor: dataChart.map(v => v > limite * 1.05 ? 'rgba(220,38,38,0.7)' : (v >= limite * 0.95 ? 'rgba(217,119,6,0.7)' : 'rgba(21,128,61,0.65)')),
          borderRadius: 5
        }, {
          label: 'Límite (' + limite.toLocaleString() + ' kcal)',
          data: new Array(labelsChart.length).fill(limite),
          type: 'line',
          borderColor: '#B71C1C',
          borderDash: [5, 3],
          borderWidth: 2,
          pointRadius: 0,
          fill: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' kcal' } }
        }
      }
    });
  <?php endif; ?>
</script>

<?php include 'footer.php'; ?>