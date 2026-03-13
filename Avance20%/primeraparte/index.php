<?php

include 'config.php';
include 'header.php';


$hoy = date('Y-m-d');

// KPIs
$total_socios = $conexion->query("SELECT COUNT(*) as total FROM socios")->fetch_assoc()['total'];
$activos = $conexion->query("SELECT COUNT(*) as total FROM socios WHERE fecha_vencimiento >= '$hoy'")->fetch_assoc()['total'];
$vencidos = $conexion->query("SELECT COUNT(*) as total FROM socios WHERE fecha_vencimiento < '$hoy'")->fetch_assoc()['total'];
$total_entrenadores = $conexion->query("SELECT COUNT(*) as total FROM entrenadores WHERE estado = 'activo'")->fetch_assoc()['total'];

// Últimas inscripciones
$recientes = $conexion->query("SELECT s.*, m.nombre as plan FROM socios s JOIN membresias m ON s.id_membresia = m.id_membresia ORDER BY s.id_socio DESC LIMIT 5");

// Próximos a vencer (7 días)
$limite = date('Y-m-d', strtotime('+7 days'));
$por_vencer = $conexion->query("SELECT nombre, apellido, fecha_vencimiento FROM socios WHERE fecha_vencimiento BETWEEN '$hoy' AND '$limite' ORDER BY fecha_vencimiento ASC LIMIT 5");
?>

<style>
.dash-hero {
    background: linear-gradient(135deg, var(--red-dark) 0%, #C62828 60%, #B71C1C 100%);
    border-radius: 12px;
    padding: 32px 36px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.dash-hero::before {
    content: '';
    position: absolute;
    right: -40px; top: -40px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
}
.dash-hero::after {
    content: '';
    position: absolute;
    right: 60px; bottom: -70px;
    width: 170px; height: 170px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}
.hero-title { font-family:'Oswald',sans-serif; font-size:2rem; font-weight:700; color:#fff; letter-spacing:1px; line-height:1.1; }
.hero-sub   { color:rgba(255,255,255,0.7); font-size:0.9rem; margin-top:6px; }
.hero-date  { font-family:'Oswald',sans-serif; font-size:0.82rem; color:var(--gold); letter-spacing:1.5px; text-transform:uppercase; margin-top:12px; }
.hero-actions { display:flex; gap:10px; z-index:1; }
.btn-hero-out {
    background:rgba(255,255,255,0.12); border:1.5px solid rgba(255,255,255,0.3);
    color:#fff; padding:10px 20px; border-radius:6px;
    font-size:0.875rem; font-weight:600; text-decoration:none;
    display:flex; align-items:center; gap:6px; transition:background 0.2s;
}
.btn-hero-out:hover { background:rgba(255,255,255,0.22); color:#fff; }
.btn-hero-sol {
    background:#fff; color:var(--red); padding:10px 22px; border-radius:6px;
    font-size:0.875rem; font-weight:700; text-decoration:none;
    display:flex; align-items:center; gap:6px;
}
.btn-hero-sol:hover { opacity:0.9; color:var(--red); }

/* KPI strip — barra unida, fondo blanco */
.kpi-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:0; margin-bottom:24px; border-radius:10px; overflow:hidden; box-shadow:var(--shadow); border:1px solid var(--border); }
.ks-card {
    text-decoration:none; padding:24px 22px;
    display:flex; align-items:center; gap:16px;
    background:#fff;
    border-right:1px solid var(--border);
    transition:background 0.2s;
}
.ks-card:last-child { border-right:none; }
.ks-card:hover { background:#F9FAFB; }
.ks-icon { width:46px; height:46px; border-radius:10px; background:#F3F4F6; display:flex; align-items:center; justify-content:center; font-size:1.3rem; color:#6B7280; flex-shrink:0; }
.ks-num   { font-family:'Oswald',sans-serif; font-size:2.4rem; font-weight:700; color:var(--text); line-height:1; }
.ks-label { font-size:0.78rem; color:var(--muted); font-weight:500; margin-top:4px; }

/* Charts row */
.charts-row { display:grid; grid-template-columns:1fr 300px; gap:18px; margin-bottom:18px; }

/* Legend dona */
.donut-wrap { display:flex; flex-direction:column; align-items:center; }
.donut-legend { display:flex; flex-direction:column; gap:10px; width:100%; margin-top:18px; }
.legend-item { display:flex; align-items:center; gap:10px; font-size:0.85rem; }
.legend-dot  { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.legend-name { flex:1; color:var(--text); font-weight:500; }
.legend-val  { font-family:'Oswald',sans-serif; font-weight:700; color:var(--text); }

/* Bottom row */
.bottom-row { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.alert-row { display:flex; align-items:center; justify-content:space-between; padding:13px 20px; border-bottom:1px solid var(--border); }
.alert-row:last-child { border-bottom:none; }
.alert-name  { font-weight:600; color:var(--text); font-size:0.875rem; }
.alert-sub   { font-size:0.78rem; color:var(--muted); margin-top:2px; }
</style>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- HERO -->
    <div class="dash-hero">
      <div style="z-index:1;">
        <div class="hero-title">Panel de Control</div>
        <div class="hero-sub">Bienvenida, Admin &mdash; SayaGYM</div>
        <div class="hero-date"><i class="ti ti-calendar me-1"></i><?php echo date('d \d\e F \d\e Y'); ?></div>
      </div>
      <div class="hero-actions">
        <a href="nuevo_entrenador.php" class="btn-hero-out"><i class="ti ti-plus"></i> Entrenador</a>
        <a href="nuevo_socio.php" class="btn-hero-sol"><i class="ti ti-user-plus"></i> Inscribir Socio</a>
      </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-strip">
      <a href="socios.php" class="ks-card c-blue">
        <div class="ks-icon"><i class="ti ti-users"></i></div>
        <div>
          <div class="ks-num"><?php echo $total_socios; ?></div>
          <div class="ks-label">Socios Registrados</div>
        </div>
      </a>
      <a href="membresias.php" class="ks-card c-green">
        <div class="ks-icon"><i class="ti ti-circle-check"></i></div>
        <div>
          <div class="ks-num"><?php echo $activos; ?></div>
          <div class="ks-label">Membresías Vigentes</div>
        </div>
      </a>
      <a href="membresias.php" class="ks-card c-red">
        <div class="ks-icon"><i class="ti ti-alert-triangle"></i></div>
        <div>
          <div class="ks-num"><?php echo $vencidos; ?></div>
          <div class="ks-label">Membresías Vencidas</div>
        </div>
      </a>
      <a href="entrenadores.php" class="ks-card c-gold">
        <div class="ks-icon"><i class="ti ti-barbell"></i></div>
        <div>
          <div class="ks-num"><?php echo $total_entrenadores; ?></div>
          <div class="ks-label">Coaches Activos</div>
        </div>
      </a>
    </div>

    <!-- BOTTOM -->
    <div class="bottom-row">

      <div class="card">
        <div class="card-header gray">
          <span class="card-title">Últimas Inscripciones</span>
          <a href="socios.php" class="btn btn-outline" style="padding:5px 12px; font-size:0.8rem;">Ver todos</a>
        </div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead><tr><th>Socio</th><th>Plan</th><th>Registrado</th><th>Vence</th><th>Estado</th></tr></thead>
            <tbody>
              <?php while ($r = $recientes->fetch_assoc()):
  $ev = strtotime($r['fecha_vencimiento']) < strtotime($hoy);
?>
              <tr>
                <td class="td-name"><?php echo $r['nombre'] . " " . $r['apellido']; ?></td>
                <td><span class="badge badge-blue"><?php echo $r['plan']; ?></span></td>
                <td class="td-muted"><?php echo date('d/m/Y', strtotime($r['fecha_registro'])); ?></td>
                <td class="td-muted"><?php echo date('d/m/Y', strtotime($r['fecha_vencimiento'])); ?></td>
                <td><span class="badge <?php echo $ev ? 'badge-red' : 'badge-green'; ?>"><?php echo $ev ? 'VENCIDO' : 'ACTIVO'; ?></span></td>
              </tr>
              <?php
endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header gray">
          <span class="card-title" style="display:flex; align-items:center; gap:8px;">
            <span style="background:var(--danger-lt); color:var(--danger); padding:3px 9px; border-radius:999px; font-size:0.72rem; font-weight:700;">ALERTA</span>
            Vencen en 7 días
          </span>
        </div>
        <?php
$rows_vencer = [];
while ($pv = $por_vencer->fetch_assoc())
  $rows_vencer[] = $pv;
if (count($rows_vencer) === 0): ?>
        <div style="padding:44px 24px; text-align:center;">
          <i class="ti ti-circle-check" style="font-size:2.5rem; color:var(--green); display:block; margin-bottom:10px;"></i>
          <div style="font-weight:600; color:var(--text);">¡Todo en orden!</div>
          <div style="font-size:0.85rem; color:var(--muted); margin-top:4px;">Sin membresías próximas a vencer.</div>
        </div>
        <?php
else:
  foreach ($rows_vencer as $pv):
    $dias = (int)((strtotime($pv['fecha_vencimiento']) - strtotime($hoy)) / 86400);
?>
        <div class="alert-row">
          <div>
            <div class="alert-name"><?php echo $pv['nombre'] . " " . $pv['apellido']; ?></div>
            <div class="alert-sub">Vence el <?php echo date('d/m/Y', strtotime($pv['fecha_vencimiento'])); ?></div>
          </div>
          <span class="badge <?php echo $dias <= 2 ? 'badge-red' : 'badge-gold'; ?>">
            <?php echo $dias === 0 ? 'Hoy' : "en $dias día" . ($dias > 1 ? 's' : ''); ?>
          </span>
        </div>
        <?php
  endforeach;
endif; ?>
      </div>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
