<?php
/**
 * Archivo: acceso.php
 * Descripción: Controlador de acceso e inicio de sesión en el sistema.
 * Parte del sistema integral de gestión Sayagym.
 */

// acceso.php — Control de acceso con lógica de toggle entrada/salida (adaptada del ZIP)
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$hoy      = date('Y-m-d');
$ahora    = date('Y-m-d H:i:s');
$hora_ini = date('Y-m-d 00:00:00');
$msg_tipo = ''; // 'ok' | 'error' | 'warn'
$msg_txt  = '';

// ── REGISTRAR SALIDA MANUAL ───────────────────────────────
if (isset($_GET['salida'])) {
    $id_a = (int)$_GET['salida'];
    $conexion->query("UPDATE asistencia SET hora_salida='".date('H:i:s')."' WHERE id_asistencia=$id_a AND hora_salida IS NULL");
    echo "<script>window.location='acceso.php?res=salida';</script>"; exit;
}

// ── REGISTRAR ENTRADA/SALIDA (lógica toggle del ZIP) ─────
if ($_POST && !empty($_POST['id_socio'])) {
    $id_socio = (int)$_POST['id_socio'];
    $medio    = $conexion->real_escape_string($_POST['medio'] ?? 'Manual');

    // Validar socio
    $socio = $conexion->query(
        "SELECT id_socio, nombre, apellido, estado, fecha_vencimiento FROM socios WHERE id_socio=$id_socio LIMIT 1"
    )->fetch_assoc();

    if (!$socio) {
        $msg_tipo = 'error'; $msg_txt = "Socio no encontrado.";
    } elseif ($socio['estado'] === 'inactivo') {
        $msg_tipo = 'error'; $msg_txt = "Acceso denegado: cuenta inactiva.";
    } elseif ($socio['fecha_vencimiento'] < $hoy) {
        $msg_tipo = 'warn';
        $msg_txt  = "Acceso bloqueado: membresía vencida el " . date('d/m/Y', strtotime($socio['fecha_vencimiento'])) . ".";
    } else {
        $nombre_completo = htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']);

        // Lógica toggle del ZIP: ¿tiene una entrada abierta hoy?
        $ultimo = $conexion->query(
            "SELECT id_asistencia, hora_salida FROM asistencia
             WHERE id_socio=$id_socio AND fecha='$hoy'
             ORDER BY id_asistencia DESC LIMIT 1"
        )->fetch_assoc();

        if ($ultimo && empty($ultimo['hora_salida'])) {
            // Tiene entrada sin salida → registrar SALIDA
            $hora_s = date('H:i:s');
            $conexion->query("UPDATE asistencia SET hora_salida='$hora_s' WHERE id_asistencia={$ultimo['id_asistencia']}");
            $msg_tipo = 'ok';
            $msg_txt  = "Salida registrada para <strong>$nombre_completo</strong> a las " . date('H:i') . ".";
        } else {
            // No tiene entrada abierta → registrar ENTRADA
            $hora_e = date('H:i:s');
            $conexion->query("INSERT INTO asistencia (id_socio, fecha, hora_entrada, medio) VALUES ($id_socio, '$hoy', '$hora_e', '$medio')");
            $msg_tipo = 'ok';
            $msg_txt  = "Entrada registrada para <strong>$nombre_completo</strong> a las " . date('H:i') . ".";
        }
    }
}

// ── KPIs ──────────────────────────────────────────────────
$total_hoy    = $conexion->query("SELECT COUNT(*) as n FROM asistencia WHERE fecha='$hoy'")->fetch_assoc()['n'];
$dentro_ahora = $conexion->query("SELECT COUNT(*) as n FROM asistencia WHERE fecha='$hoy' AND hora_salida IS NULL")->fetch_assoc()['n'];
$ya_salieron  = $total_hoy - $dentro_ahora;

// ── REGISTROS DEL DÍA ────────────────────────────────────
$registros = $conexion->query("
    SELECT a.*, s.nombre, s.apellido, s.foto
    FROM asistencia a
    JOIN socios s ON a.id_socio = s.id_socio
    WHERE a.fecha = '$hoy'
    ORDER BY a.id_asistencia DESC
");

// ── SOCIOS ACTIVOS PARA EL SELECT ────────────────────────
$socios_activos = $conexion->query(
    "SELECT id_socio, nombre, apellido FROM socios
     WHERE estado='activo' AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= '$hoy')
     ORDER BY nombre ASC"
);
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Control de Acceso</h2>
        <p class="page-subtitle"><?php echo date('d \d\e F \d\e Y'); ?> — Toca el nombre del socio o usa el formulario</p>
      </div>
    </div>

    <?php if (isset($_GET['res']) && $_GET['res']==='salida'): ?>
    <div class="alert alert-success"><i class="ti ti-logout me-1"></i>Salida registrada.</div>
    <?php endif; ?>

    <?php if ($msg_txt): ?>
    <div class="alert <?php echo $msg_tipo==='ok'?'alert-success':($msg_tipo==='warn'?'':' alert-danger'); ?>"
         <?php if($msg_tipo==='warn') echo 'style="background:#FEF3C7;color:#92400E;border-left:4px solid #D97706;"'; ?>>
      <?php echo $msg_txt; ?>
    </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
      <div class="card" style="text-align:center;padding:20px;">
        <div style="font-size:2.4rem;font-family:'Oswald',sans-serif;font-weight:700;"><?php echo $total_hoy; ?></div>
        <div style="font-size:.78rem;color:var(--muted);">Visitas hoy</div>
      </div>
      <div class="card" style="text-align:center;padding:20px;border-left:3px solid var(--green);">
        <div style="font-size:2.4rem;font-family:'Oswald',sans-serif;font-weight:700;color:var(--green);"><?php echo $dentro_ahora; ?></div>
        <div style="font-size:.78rem;color:var(--muted);">Dentro ahora</div>
      </div>
      <div class="card" style="text-align:center;padding:20px;border-left:3px solid var(--muted);">
        <div style="font-size:2.4rem;font-family:'Oswald',sans-serif;font-weight:700;color:var(--muted);"><?php echo $ya_salieron; ?></div>
        <div style="font-size:.78rem;color:var(--muted);">Ya salieron</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

      <!-- TABLA -->
      <div class="card">
        <div class="card-header gray"><h3 class="card-title">Movimientos de Hoy</h3></div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr><th>Socio</th><th>Entrada</th><th>Salida</th><th>Medio</th><th>Estado</th><th>Acción</th></tr>
            </thead>
            <tbody>
              <?php if ($registros->num_rows === 0): ?>
              <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted);">Sin movimientos hoy.</td></tr>
              <?php endif; ?>
              <?php while ($r = $registros->fetch_assoc()): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:10px;">
                    <?php if (!empty($r['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($r['foto']); ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                    <div style="width:32px;height:32px;border-radius:50%;background:#F3F4F6;display:flex;align-items:center;justify-content:center;"><i class="ti ti-user" style="color:#9CA3AF;"></i></div>
                    <?php endif; ?>
                    <span class="td-name"><?php echo htmlspecialchars($r['nombre'].' '.$r['apellido']); ?></span>
                  </div>
                </td>
                <td class="fw-bold text-green"><?php echo $r['hora_entrada'] ? substr($r['hora_entrada'],0,5) : '—'; ?></td>
                <td class="td-muted"><?php echo $r['hora_salida'] ? substr($r['hora_salida'],0,5) : '—'; ?></td>
                <td><span class="badge badge-blue" style="font-size:.7rem;"><?php echo htmlspecialchars($r['medio'] ?? 'Manual'); ?></span></td>
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
                     onclick="return confirm('¿Registrar salida ahora?');"><i class="ti ti-logout"></i></a>
                  <?php else: ?>
                  <span style="color:var(--muted);font-size:.78rem;">Completo</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FORMULARIO -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <form method="POST" class="card">
          <div class="card-header" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
            <span class="card-title" style="color:#fff;"><i class="ti ti-door-enter me-2"></i>Registrar Acceso</span>
          </div>
          <div class="card-body">
            <p style="font-size:.8rem;color:var(--muted);margin-bottom:14px;">
              Si el socio tiene entrada abierta, se registra la <strong>salida</strong> automáticamente.
            </p>
            <div style="margin-bottom:14px;">
              <label class="form-label">Socio</label>
              <select name="id_socio" class="form-select" required>
                <option value="">— Seleccionar socio —</option>
                <?php while ($s = $socios_activos->fetch_assoc()): ?>
                <option value="<?php echo $s['id_socio']; ?>"><?php echo htmlspecialchars($s['nombre'].' '.$s['apellido']); ?> (#<?php echo $s['id_socio']; ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Medio de acceso</label>
              <select name="medio" class="form-select">
                <option value="Manual">Manual (recepción)</option>
                <option value="QR">Código QR</option>
                <option value="Tarjeta">Tarjeta</option>
              </select>
            </div>
          </div>
          <div class="card-footer" style="justify-content:center;">
            <button type="submit" class="btn btn-red" style="width:100%;justify-content:center;">
              <i class="ti ti-door-enter me-2"></i>Registrar Entrada / Salida
            </button>
          </div>
        </form>

        <div class="card" style="border-left:3px solid var(--gold);">
          <div class="card-body" style="padding:16px;">
            <p style="font-size:.78rem;color:#92400E;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">
              <i class="ti ti-info-circle me-1"></i>Cómo funciona
            </p>
            <p style="font-size:.82rem;color:#4B5563;line-height:1.6;">
              El sistema detecta automáticamente si el socio ya tiene entrada registrada hoy sin salida. En ese caso registra la <strong>salida</strong>. Si ya salió o no ha entrado, registra una nueva <strong>entrada</strong>.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
