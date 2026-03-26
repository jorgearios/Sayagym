<?php
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$hoy  = date('Y-m-d');
$hora = date('H:i:s');
$msg  = '';

// ── REGISTRAR SALIDA ─────────────────────────────────────
if (isset($_GET['salida'])) {
    $id = (int)$_GET['salida'];
    $conexion->query("UPDATE asistencia SET hora_salida='$hora' WHERE id_asistencia=$id AND hora_salida IS NULL");
    echo "<script>window.location='acceso.php?res=salida';</script>"; exit;
}

// ── REGISTRAR ENTRADA ────────────────────────────────────
if ($_POST && !empty($_POST['id_socio'])) {
    $id_socio = (int)$_POST['id_socio'];
    $medio    = $conexion->real_escape_string($_POST['medio'] ?? 'Manual');

    $socio = $conexion->query("SELECT id_socio, nombre, apellido, estado, fecha_vencimiento FROM socios WHERE id_socio=$id_socio")->fetch_assoc();

    if (!$socio) {
        $msg = "<div class='alert alert-danger'><i class='ti ti-alert-circle me-1'></i>Socio no encontrado.</div>";
    } elseif ($socio['estado'] === 'inactivo') {
        $msg = "<div class='alert alert-danger'><i class='ti ti-ban me-1'></i>Acceso denegado: cuenta inactiva.</div>";
    } elseif ($socio['fecha_vencimiento'] < $hoy) {
        $msg = "<div class='alert alert-danger'><i class='ti ti-alert-triangle me-1'></i>Acceso denegado: membresía vencida el " . date('d/m/Y', strtotime($socio['fecha_vencimiento'])) . ".</div>";
    } else {
        // Verificar que no tenga ya entrada abierta hoy
        $abierta = $conexion->query("SELECT id_asistencia FROM asistencia WHERE id_socio=$id_socio AND fecha='$hoy' AND hora_salida IS NULL")->fetch_assoc();
        if ($abierta) {
            $msg = "<div class='alert alert-danger'>El socio ya tiene una entrada registrada hoy sin salida.</div>";
        } else {
            $conexion->query("INSERT INTO asistencia (id_socio, fecha, hora_entrada, medio) VALUES ($id_socio, '$hoy', '$hora', '$medio')");
            $msg = "<div class='alert alert-success'><i class='ti ti-circle-check me-1'></i>Entrada registrada para <strong>" . htmlspecialchars($socio['nombre'].' '.$socio['apellido']) . "</strong> a las " . date('H:i') . ".</div>";
        }
    }
}

// ── ESTADÍSTICAS DEL DÍA ─────────────────────────────────
$total_hoy    = $conexion->query("SELECT COUNT(*) as t FROM asistencia WHERE fecha='$hoy'")->fetch_assoc()['t'];
$dentro_ahora = $conexion->query("SELECT COUNT(*) as t FROM asistencia WHERE fecha='$hoy' AND hora_salida IS NULL")->fetch_assoc()['t'];
$ya_salieron  = $total_hoy - $dentro_ahora;

// ── LISTADO DEL DÍA ──────────────────────────────────────
$registros = $conexion->query("
    SELECT a.*, s.nombre, s.apellido, s.foto
    FROM asistencia a
    JOIN socios s ON a.id_socio = s.id_socio
    WHERE a.fecha = '$hoy'
    ORDER BY a.hora_entrada DESC
");

// ── LISTA DE SOCIOS PARA EL SELECT ───────────────────────
$socios_activos = $conexion->query("SELECT id_socio, nombre, apellido FROM socios WHERE estado='activo' AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= '$hoy') ORDER BY nombre ASC");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Control de Acceso</h2>
        <p class="page-subtitle">Registro de entradas y salidas — <?php echo date('d \d\e F \d\e Y'); ?></p>
      </div>
    </div>

    <?php if (isset($_GET['res']) && $_GET['res']==='salida'): ?>
    <div class="alert alert-success"><i class="ti ti-logout me-1"></i>Salida registrada correctamente.</div>
    <?php endif; ?>
    <?php echo $msg; ?>

    <!-- KPIs ─────────────────────────────────────────── -->
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px;">
      <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2.4rem; font-family:'Oswald',sans-serif; font-weight:700; color:var(--text);"><?php echo $total_hoy; ?></div>
        <div style="font-size:0.8rem; color:var(--muted); margin-top:4px;">Visitas hoy</div>
      </div>
      <div class="card" style="text-align:center; padding:20px; border-left:3px solid var(--green);">
        <div style="font-size:2.4rem; font-family:'Oswald',sans-serif; font-weight:700; color:var(--green);"><?php echo $dentro_ahora; ?></div>
        <div style="font-size:0.8rem; color:var(--muted); margin-top:4px;">Dentro ahora</div>
      </div>
      <div class="card" style="text-align:center; padding:20px; border-left:3px solid var(--muted);">
        <div style="font-size:2.4rem; font-family:'Oswald',sans-serif; font-weight:700; color:var(--muted);"><?php echo $ya_salieron; ?></div>
        <div style="font-size:0.8rem; color:var(--muted); margin-top:4px;">Ya salieron</div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start;">

      <!-- TABLA DE REGISTROS ──────────────────────────── -->
      <div class="card">
        <div class="card-header gray">
          <h3 class="card-title">Movimientos de Hoy</h3>
        </div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr>
                <th>Socio</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Medio</th>
                <th>Estado</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($registros->num_rows === 0): ?>
              <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--muted);">Sin registros hoy todavía.</td></tr>
              <?php endif; ?>
              <?php while ($r = $registros->fetch_assoc()): ?>
              <tr>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <?php if (!empty($r['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($r['foto']); ?>" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                    <div style="width:34px;height:34px;border-radius:50%;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:#9CA3AF;"><i class="ti ti-user"></i></div>
                    <?php endif; ?>
                    <span class="td-name"><?php echo htmlspecialchars($r['nombre'].' '.$r['apellido']); ?></span>
                  </div>
                </td>
                <td class="fw-bold text-green"><?php echo substr($r['hora_entrada'],0,5); ?></td>
                <td class="td-muted"><?php echo $r['hora_salida'] ? substr($r['hora_salida'],0,5) : '—'; ?></td>
                <td><span class="badge badge-blue" style="font-size:0.7rem;"><?php echo htmlspecialchars($r['medio'] ?? 'Manual'); ?></span></td>
                <td>
                  <?php if (!$r['hora_salida']): ?>
                  <span class="badge badge-green">Dentro</span>
                  <?php else: ?>
                  <span class="badge badge-gray">Salió</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!$r['hora_salida']): ?>
                  <a href="acceso.php?salida=<?php echo $r['id_asistencia']; ?>" class="btn btn-icon" title="Registrar salida"
                     onclick="return confirm('¿Registrar salida ahora?');">
                    <i class="ti ti-logout"></i>
                  </a>
                  <?php else: ?>
                  <span style="color:var(--muted); font-size:0.8rem;">Completo</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FORMULARIO ENTRADA ──────────────────────────── -->
      <div style="display:flex; flex-direction:column; gap:16px;">
        <form method="POST" class="card">
          <div class="card-header" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
            <span class="card-title" style="color:#fff;"><i class="ti ti-login me-2"></i>Registrar Entrada</span>
          </div>
          <div class="card-body">
            <div style="margin-bottom:14px;">
              <label class="form-label">Buscar Socio</label>
              <select name="id_socio" class="form-select" required id="sel-socio">
                <option value="">— Selecciona un socio —</option>
                <?php while ($s = $socios_activos->fetch_assoc()): ?>
                <option value="<?php echo $s['id_socio']; ?>"><?php echo htmlspecialchars($s['nombre'].' '.$s['apellido']); ?> (#<?php echo $s['id_socio']; ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Medio de Acceso</label>
              <select name="medio" class="form-select">
                <option value="Manual">Manual (recepción)</option>
                <option value="QR">Código QR</option>
                <option value="Tarjeta">Tarjeta</option>
              </select>
            </div>
          </div>
          <div class="card-footer" style="justify-content:center;">
            <button type="submit" class="btn btn-red" style="width:100%; justify-content:center;">
              <i class="ti ti-door-enter me-2"></i>Registrar Entrada
            </button>
          </div>
        </form>

        <!-- Info rápida -->
        <div class="card" style="border-left:3px solid var(--gold);">
          <div class="card-body" style="padding:16px;">
            <p style="font-size:0.78rem; color:#92400E; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">
              <i class="ti ti-info-circle me-1"></i> Validación automática
            </p>
            <p style="font-size:0.83rem; color:#4B5563; line-height:1.6;">
              El sistema verifica que el socio tenga membresía vigente antes de registrar la entrada. Socios con membresía vencida o cuenta inactiva serán rechazados automáticamente.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
