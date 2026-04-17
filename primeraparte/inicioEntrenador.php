<?php
/**
 * Archivo: inicioEntrenador.php
 * Descripción: Panel principal del Entrenador con KPIs, socios asignados,
 *              rutinas activas y evaluaciones recientes.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
include 'header.php';

// ── Validar acceso por rol ────────────────────────────────────────────────────
if (!esEntrenador() && !esAdministrador()) {
    echo "<div class='container-xl mt-4'><div class='alert alert-danger'>Acceso exclusivo para entrenadores.</div></div>";
    include 'footer.php';
    exit();
}

$nombre_entrenador = $_SESSION['nombre'];
$id_sesion         = $_SESSION['usuario_id'];
$hoy               = date('Y-m-d');

// ── Vincular usuario de sesión con registro en tabla entrenadores ─────────────
// Si el usuario es un Entrenador, usamos su ID de sesión directamente.
// Si es Administrador viendo el panel de un entrenador, intentaremos buscar al entrenador.

$id_entrenador = 0;
$especialidad  = 'General';
$turno         = 'Completo';

if (esEntrenador()) {
    $id_entrenador = (int)$id_sesion;
    $registro_entrenador = $conexion->query(
        "SELECT id_entrenador, nombre, especialidad, turno
         FROM entrenadores
         WHERE id_entrenador = $id_entrenador AND estado = 'activo'
         LIMIT 1"
    )->fetch_assoc();
} else {
    // Si es Administrador, buscamos por nombre (comportamiento original para vista previa)
    $nombre_escapado     = $conexion->real_escape_string($nombre_entrenador);
    $registro_entrenador = $conexion->query(
        "SELECT id_entrenador, nombre, especialidad, turno
         FROM entrenadores
         WHERE nombre = '$nombre_escapado' AND estado = 'activo'
         LIMIT 1"
    )->fetch_assoc();

    if (!$registro_entrenador) {
        $primer_nombre       = $conexion->real_escape_string(explode(' ', trim($nombre_entrenador))[0] ?? '');
        $registro_entrenador = $conexion->query(
            "SELECT id_entrenador, nombre, especialidad, turno
             FROM entrenadores
             WHERE nombre LIKE '%$primer_nombre%' AND estado = 'activo'
             LIMIT 1"
        )->fetch_assoc();
    }
}

if ($registro_entrenador) {
    $id_entrenador = (int)$registro_entrenador['id_entrenador'];
    $especialidad  = $registro_entrenador['especialidad'] ?? 'General';
    $turno         = $registro_entrenador['turno']        ?? 'Completo';
}

// ── KPI: total socios asignados ───────────────────────────────────────────────
$total_socios = $id_entrenador
    ? (int)$conexion->query("SELECT COUNT(*) as n FROM socios WHERE id_entrenador=$id_entrenador AND estado!='inactivo'")->fetch_assoc()['n']
    : 0;

// ── KPI: socios con membresía activa ─────────────────────────────────────────
$socios_activos = $id_entrenador
    ? (int)$conexion->query("SELECT COUNT(*) as n FROM socios WHERE id_entrenador=$id_entrenador AND estado='activo' AND fecha_vencimiento>='$hoy'")->fetch_assoc()['n']
    : 0;

// ── KPI: rutinas distintas asignadas a sus socios ────────────────────────────
$rutinas_activas = $id_entrenador
    ? (int)$conexion->query("SELECT COUNT(DISTINCT sr.id_rutina) as n FROM socio_rutina sr JOIN socios s ON sr.id_socio=s.id_socio WHERE s.id_entrenador=$id_entrenador")->fetch_assoc()['n']
    : 0;

// ── KPI: evaluaciones registradas en el mes actual ───────────────────────────
$primer_dia_mes  = date('Y-m-01');
$evaluaciones_mes = $id_entrenador
    ? (int)$conexion->query("SELECT COUNT(*) as n FROM evaluaciones_fisicas WHERE id_entrenador=$id_entrenador AND fecha BETWEEN '$primer_dia_mes' AND '$hoy'")->fetch_assoc()['n']
    : 0;

// ── KPI: socios sin ninguna evaluación registrada ────────────────────────────
$sin_evaluacion = $id_entrenador
    ? (int)$conexion->query("SELECT COUNT(*) as n FROM socios s WHERE s.id_entrenador=$id_entrenador AND s.estado!='inactivo' AND NOT EXISTS (SELECT 1 FROM evaluaciones_fisicas ef WHERE ef.id_socio=s.id_socio)")->fetch_assoc()['n']
    : 0;

// ── Lista completa de socios asignados ────────────────────────────────────────
$lista_socios = null;
if ($id_entrenador) {
    $lista_socios = $conexion->query(
        "SELECT s.id_socio, s.nombre, s.apellido, s.foto, s.estado, s.fecha_vencimiento,
                m.nombre AS nombre_membresia,
                (SELECT COUNT(*) FROM socio_rutina sr WHERE sr.id_socio=s.id_socio) AS total_rutinas,
                (SELECT COUNT(*) FROM evaluaciones_fisicas ef WHERE ef.id_socio=s.id_socio) AS total_evaluaciones,
                (SELECT fecha FROM evaluaciones_fisicas ef2 WHERE ef2.id_socio=s.id_socio ORDER BY ef2.fecha DESC LIMIT 1) AS ultima_evaluacion
         FROM socios s
         LEFT JOIN membresias m ON s.id_membresia=m.id_membresia
         WHERE s.id_entrenador=$id_entrenador AND s.estado!='inactivo'
         ORDER BY s.nombre ASC"
    );
}

// ── Últimas 8 rutinas asignadas a sus socios ──────────────────────────────────
$rutinas_recientes = null;
if ($id_entrenador) {
    $rutinas_recientes = $conexion->query(
        "SELECT r.nombre_rutina, r.nivel,
                s.nombre AS snombre, s.apellido AS sape, sr.fecha_inicio
         FROM socio_rutina sr
         JOIN rutinas r ON sr.id_rutina=r.id_rutina
         JOIN socios  s ON sr.id_socio=s.id_socio
         WHERE s.id_entrenador=$id_entrenador
         ORDER BY sr.fecha_inicio DESC LIMIT 8"
    );
}

// ── Últimas 6 evaluaciones registradas por este entrenador ───────────────────
$evaluaciones_recientes = null;
if ($id_entrenador) {
    $evaluaciones_recientes = $conexion->query(
        "SELECT ef.fecha, ef.peso, ef.imc, ef.objetivo,
                s.nombre AS snombre, s.apellido AS sape, s.id_socio
         FROM evaluaciones_fisicas ef
         JOIN socios s ON ef.id_socio=s.id_socio
         WHERE ef.id_entrenador=$id_entrenador
         ORDER BY ef.fecha DESC LIMIT 6"
    );
}
?>

<div class="page-wrapper">
<div class="container-xl mt-4">

  <!-- ── HERO ──────────────────────────────────────────────────────────── -->
  <div style="background:linear-gradient(135deg,#B45309 0%,#D97706 100%);
              padding:28px 30px;border-radius:var(--radius);
              display:flex;align-items:center;justify-content:space-between;
              margin-bottom:24px;box-shadow:var(--shadow);">
    <div>
      <div style="font-family:'Oswald',sans-serif;font-size:2rem;font-weight:700;
                  color:#fff;line-height:1.1;margin-bottom:4px;">
        Panel de Entrenamiento
      </div>
      <div style="font-size:1rem;color:rgba(255,255,255,.9);margin-bottom:6px;">
        Hola, <?php echo htmlspecialchars($nombre_entrenador); ?>.
        Aquí está el resumen de tus socios.
      </div>
      <div style="font-size:.82rem;color:rgba(255,255,255,.8);">
        <i class="ti ti-calendar me-1"></i><?php echo date('d \\d\\e F \\d\\e Y'); ?>
        &nbsp;·&nbsp;
        <i class="ti ti-barbell me-1"></i><?php echo htmlspecialchars($especialidad); ?>
        &nbsp;·&nbsp;
        <i class="ti ti-clock me-1"></i>Turno <?php echo htmlspecialchars($turno); ?>
      </div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
      <a href="asignarRutina.php" class="btn"
         style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);">
        <i class="ti ti-clipboard-list me-1"></i>Asignar Rutina
      </a>
      <a href="nuevaEvaluacion.php" class="btn"
         style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);">
        <i class="ti ti-report-medical me-1"></i>Nueva Evaluación
      </a>
    </div>
  </div>

  <?php if (!$id_entrenador): ?>
  <div class="alert" style="background:#FEF3C7;color:#92400E;border-left:4px solid #D97706;margin-bottom:20px;">
    <i class="ti ti-alert-triangle me-1"></i>
    <strong>Aviso:</strong> Tu usuario no está vinculado a un entrenador activo.
    Verifica que <code>nombre_completo</code> en <code>usuarios</code> coincida
    con <code>nombre</code> en <code>entrenadores</code>.
  </div>
  <?php endif; ?>

  <!-- ── KPIs ──────────────────────────────────────────────────────────── -->
  <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;">

    <div class="card" style="text-align:center;padding:18px;">
      <div style="font-size:2.2rem;font-family:'Oswald',sans-serif;font-weight:700;">
        <?php echo $total_socios; ?>
      </div>
      <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">Socios asignados</div>
    </div>

    <div class="card" style="text-align:center;padding:18px;border-left:3px solid var(--green);">
      <div style="font-size:2.2rem;font-family:'Oswald',sans-serif;font-weight:700;color:var(--green);">
        <?php echo $socios_activos; ?>
      </div>
      <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">Membresías activas</div>
    </div>

    <div class="card" style="text-align:center;padding:18px;border-left:3px solid var(--blue);">
      <div style="font-size:2.2rem;font-family:'Oswald',sans-serif;font-weight:700;color:var(--blue);">
        <?php echo $rutinas_activas; ?>
      </div>
      <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">Rutinas distintas</div>
    </div>

    <div class="card" style="text-align:center;padding:18px;border-left:3px solid #D97706;">
      <div style="font-size:2.2rem;font-family:'Oswald',sans-serif;font-weight:700;color:#D97706;">
        <?php echo $evaluaciones_mes; ?>
      </div>
      <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">Evaluaciones (mes)</div>
    </div>

    <div class="card" style="text-align:center;padding:18px;
         border-left:3px solid <?php echo $sin_evaluacion > 0 ? 'var(--red)' : 'var(--muted)'; ?>;">
      <div style="font-size:2.2rem;font-family:'Oswald',sans-serif;font-weight:700;
                  color:<?php echo $sin_evaluacion > 0 ? 'var(--red)' : 'var(--muted)'; ?>;">
        <?php echo $sin_evaluacion; ?>
      </div>
      <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">Sin evaluación</div>
    </div>

  </div>

  <!-- ── GRID PRINCIPAL ──────────────────────────────────────────────────── -->
  <div style="display:grid;grid-template-columns:1fr 310px;gap:20px;align-items:start;">

    <!-- TABLA DE SOCIOS ASIGNADOS -->
    <div class="card">
      <div class="card-header gray"
           style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card-title">Mis Socios Asignados</h3>
        <a href="socios.php" class="btn btn-outline"
           style="padding:5px 12px;font-size:.78rem;">
          <i class="ti ti-external-link me-1"></i>Ver todos
        </a>
      </div>
      <div class="table-responsive">
        <table class="gym-table">
          <thead>
            <tr>
              <th>Socio</th>
              <th>Plan</th>
              <th>Vence</th>
              <th>Rutinas</th>
              <th>Evaluaciones</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$lista_socios || $lista_socios->num_rows === 0): ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:30px;color:var(--muted);">
                <?php echo $id_entrenador ? 'No tienes socios asignados aún.' : 'Sin vinculación a entrenador.'; ?>
              </td>
            </tr>
          <?php else: ?>
          <?php while ($s = $lista_socios->fetch_assoc()):
            $memb_activa   = (!empty($s['fecha_vencimiento']) && $s['fecha_vencimiento'] >= $hoy);
            $dias_rest     = $memb_activa ? (int)floor((strtotime($s['fecha_vencimiento']) - strtotime($hoy)) / 86400) : 0;
            $alerta_pronto = $memb_activa && $dias_rest <= 7;
          ?>
            <tr>
              <!-- Socio -->
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <?php if (!empty($s['foto'])): ?>
                  <img src="<?php echo htmlspecialchars($s['foto']); ?>"
                       style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                  <?php else: ?>
                  <div style="width:34px;height:34px;border-radius:50%;background:#F3F4F6;
                               display:flex;align-items:center;justify-content:center;">
                    <i class="ti ti-user" style="color:#9CA3AF;"></i>
                  </div>
                  <?php endif; ?>
                  <div>
                    <div class="td-name">
                      <?php echo htmlspecialchars($s['nombre'].' '.$s['apellido']); ?>
                    </div>
                    <div class="td-muted small">#<?php echo $s['id_socio']; ?></div>
                  </div>
                </div>
              </td>

              <!-- Plan -->
              <td class="td-muted">
                <?php echo htmlspecialchars($s['nombre_membresia'] ?? '—'); ?>
              </td>

              <!-- Fecha vencimiento -->
              <td>
                <?php if (!empty($s['fecha_vencimiento'])): ?>
                <span style="font-size:.82rem;font-weight:600;
                  color:<?php echo !$memb_activa ? 'var(--red)' : ($alerta_pronto ? '#D97706' : 'var(--green)'); ?>;">
                  <?php echo date('d/m/Y', strtotime($s['fecha_vencimiento'])); ?>
                </span>
                <?php if (!$memb_activa): ?>
                <br><span style="font-size:.7rem;color:var(--red);">Vencida</span>
                <?php elseif ($alerta_pronto): ?>
                <br><span style="font-size:.7rem;color:#D97706;"><?php echo $dias_rest; ?> días</span>
                <?php endif; ?>
                <?php else: ?>
                <span class="td-muted">—</span>
                <?php endif; ?>
              </td>

              <!-- Rutinas -->
              <td>
                <span class="badge <?php echo (int)$s['total_rutinas'] > 0 ? 'badge-blue' : 'badge-gray'; ?>"
                      style="font-size:.72rem;">
                  <?php echo (int)$s['total_rutinas']; ?>
                </span>
              </td>

              <!-- Evaluaciones -->
              <td>
                <?php if ((int)$s['total_evaluaciones'] > 0): ?>
                <span class="badge badge-green" style="font-size:.72rem;">
                  <?php echo (int)$s['total_evaluaciones']; ?>
                </span>
                <?php if (!empty($s['ultima_evaluacion'])): ?>
                <div class="td-muted" style="font-size:.7rem;">
                  <?php echo date('d/m/Y', strtotime($s['ultima_evaluacion'])); ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <span class="badge badge-red" style="font-size:.72rem;">Pendiente</span>
                <?php endif; ?>
              </td>

              <!-- Acciones -->
              <td>
                <div class="btn-list">
                  <a href="nuevaEvaluacion.php?id_socio=<?php echo $s['id_socio']; ?>"
                     class="btn btn-icon" title="Nueva evaluación física">
                    <i class="ti ti-report-medical"></i>
                  </a>
                  <a href="asignarRutina.php?id_socio=<?php echo $s['id_socio']; ?>"
                     class="btn btn-icon" title="Asignar rutina">
                    <i class="ti ti-clipboard-list"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PANEL DERECHO -->
    <div style="display:flex;flex-direction:column;gap:16px;">

      <!-- Accesos rápidos -->
      <div class="card">
        <div class="card-header gray"><h3 class="card-title">Accesos Rápidos</h3></div>
        <div class="card-body" style="padding:12px;display:flex;flex-direction:column;gap:8px;">
          <a href="rutinas.php" class="btn btn-outline"
             style="justify-content:flex-start;gap:10px;padding:9px 12px;">
            <i class="ti ti-clipboard-list" style="color:var(--blue);font-size:1rem;"></i>
            Catálogo de Rutinas
          </a>
          <a href="asignarRutina.php" class="btn btn-outline"
             style="justify-content:flex-start;gap:10px;padding:9px 12px;">
            <i class="ti ti-user-check" style="color:var(--green);font-size:1rem;"></i>
            Asignar Rutina
          </a>
          <a href="evaluaciones.php" class="btn btn-outline"
             style="justify-content:flex-start;gap:10px;padding:9px 12px;">
            <i class="ti ti-report-medical" style="color:var(--red);font-size:1rem;"></i>
            Historial Evaluaciones
          </a>
          <a href="nuevaEvaluacion.php" class="btn btn-red"
             style="justify-content:flex-start;gap:10px;padding:9px 12px;">
            <i class="ti ti-plus" style="font-size:1rem;"></i>
            Nueva Evaluación
          </a>
        </div>
      </div>

      <!-- Rutinas recientes -->
      <div class="card">
        <div class="card-header gray"><h3 class="card-title">Rutinas Recientes</h3></div>
        <?php if (!$rutinas_recientes || $rutinas_recientes->num_rows === 0): ?>
        <p style="padding:16px;color:var(--muted);font-size:.82rem;text-align:center;">
          Sin rutinas asignadas.
        </p>
        <?php else: ?>
        <ul style="list-style:none;margin:0;padding:0;">
        <?php while ($rut = $rutinas_recientes->fetch_assoc()): ?>
        <li style="padding:9px 14px;border-bottom:1px solid var(--border);
                    display:flex;align-items:flex-start;gap:8px;">
          <i class="ti ti-clipboard-list"
             style="color:var(--blue);margin-top:2px;flex-shrink:0;font-size:.9rem;"></i>
          <div style="flex:1;min-width:0;">
            <div style="font-size:.82rem;font-weight:600;
                         white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?php echo htmlspecialchars($rut['nombre_rutina']); ?>
            </div>
            <div style="font-size:.73rem;color:var(--muted);">
              <?php echo htmlspecialchars($rut['snombre'].' '.$rut['sape']); ?>
              &nbsp;·&nbsp;
              <span class="badge badge-blue" style="font-size:.65rem;">
                <?php echo htmlspecialchars($rut['nivel']); ?>
              </span>
            </div>
          </div>
          <span style="font-size:.7rem;color:var(--muted);white-space:nowrap;">
            <?php echo date('d/m', strtotime($rut['fecha_inicio'])); ?>
          </span>
        </li>
        <?php endwhile; ?>
        </ul>
        <?php endif; ?>
      </div>

      <!-- Evaluaciones recientes -->
      <div class="card">
        <div class="card-header gray"><h3 class="card-title">Evaluaciones Recientes</h3></div>
        <?php if (!$evaluaciones_recientes || $evaluaciones_recientes->num_rows === 0): ?>
        <p style="padding:16px;color:var(--muted);font-size:.82rem;text-align:center;">
          Sin evaluaciones registradas.
        </p>
        <?php else: ?>
        <ul style="list-style:none;margin:0;padding:0;">
        <?php while ($ev = $evaluaciones_recientes->fetch_assoc()): ?>
        <li style="padding:9px 14px;border-bottom:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="flex:1;min-width:0;">
              <div style="font-size:.82rem;font-weight:600;">
                <?php echo htmlspecialchars($ev['snombre'].' '.$ev['sape']); ?>
              </div>
              <div style="font-size:.73rem;color:var(--muted);">
                <?php echo !empty($ev['peso']) ? 'Peso: '.$ev['peso'].' kg' : ''; ?>
                <?php echo !empty($ev['imc'])  ? ' · IMC: '.$ev['imc'] : ''; ?>
              </div>
              <?php if (!empty($ev['objetivo'])): ?>
              <div style="font-size:.72rem;color:var(--muted);">
                <?php echo htmlspecialchars($ev['objetivo']); ?>
              </div>
              <?php endif; ?>
            </div>
            <a href="nuevaEvaluacion.php?id_socio=<?php echo $ev['id_socio']; ?>"
               class="btn btn-icon" style="flex-shrink:0;margin-left:6px;" title="Ver evaluación">
              <i class="ti ti-eye"></i>
            </a>
          </div>
          <div style="font-size:.7rem;color:var(--muted);margin-top:3px;">
            <i class="ti ti-calendar" style="font-size:.75rem;"></i>
            <?php echo date('d/m/Y', strtotime($ev['fecha'])); ?>
          </div>
        </li>
        <?php endwhile; ?>
        </ul>
        <?php endif; ?>
      </div>

    </div><!-- fin panel derecho -->
  </div><!-- fin grid principal -->
</div><!-- fin container -->
</div><!-- fin page-wrapper -->

<?php include 'footer.php'; ?>
