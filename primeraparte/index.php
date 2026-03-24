<?php

// Incluimos el archivo de configuración para poder conectarnos a la base de datos
include 'config.php';

// Incluimos el encabezado de la página
include 'header.php';

// Si es socio, no deberia ver este panel. Lo redirigimos a su perfil.
if (esSocio()) {
  echo "<script>window.location='inicioSocio.php';</script>";
  exit();
}

// Guardamos la fecha de hoy en formato Año-Mes-Día para usarla en nuestros cálculos después
$hoy = date('Y-m-d');

// === SECCIÓN DE INDICADORES IMPORTANTES (KPIs) ===
// Aquí contamos diferentes datos clave para mostrarlos en los cuadros principales del inicio

// 1. Contamos el total de socios registrados en el gimnasio
// Hacemos una consulta a la base de datos para saber cuántos existen en total
$consulta_total_socios = $conexion->query("SELECT COUNT(*) as total FROM socios");
$total_socios = $consulta_total_socios->fetch_assoc()['total'];

// 2. Contamos cuántos socios están activos
// Un socio está "activo" si su fecha de vencimiento es igual o mayor al día de hoy
$consulta_activos = $conexion->query("SELECT COUNT(*) as total FROM socios WHERE fecha_vencimiento >= '$hoy'");
$activos = $consulta_activos->fetch_assoc()['total'];

// 3. Contamos cuántos socios están vencidos
// Es decir, su fecha de vencimiento ya pasó (es menor a la fecha actual)
$consulta_vencidos = $conexion->query("SELECT COUNT(*) as total FROM socios WHERE fecha_vencimiento < '$hoy'");
$vencidos = $consulta_vencidos->fetch_assoc()['total'];

// 4. Contamos el número de entrenadores (o "coaches") que tienen su estado como 'activo'
$consulta_entrenadores = $conexion->query("SELECT COUNT(*) as total FROM entrenadores WHERE estado = 'activo'");
$total_entrenadores = $consulta_entrenadores->fetch_assoc()['total'];


// === SECCIÓN DE DATOS PARA LAS TABLAS ===

// Obtenemos los últimos 5 socios que se inscribieron
// También "unimos" la tabla de membresías (usando JOIN) para saber el nombre de su plan
$recientes = $conexion->query("SELECT s.*, COALESCE(m.nombre, 'Sin plan') as plan FROM socios s LEFT JOIN membresias m ON s.id_membresia = m.id_membresia ORDER BY s.id_socio DESC LIMIT 5");

// Calculamos cuál será la fecha dentro de 7 días exactos
// Esto nos sirve para saber a qué personas se les va a vencer la membresía muy pronto
$limite = date('Y-m-d', strtotime('+7 days'));

// Obtenemos a los socios cuyas membresías van a vencer pronto (en los próximos 7 días) o que ya vencieron
$por_vencer = $conexion->query("SELECT nombre, apellido, fecha_vencimiento, estado FROM socios WHERE estado = 'inactivo' OR fecha_vencimiento < '$hoy' OR (fecha_vencimiento BETWEEN '$hoy' AND '$limite' AND estado = 'activo') ORDER BY estado DESC, fecha_vencimiento ASC LIMIT 10");

?>

<!-- Estilos CSS (No cambiamos el diseño visual, lo mantenemos igual) -->
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

    <!-- SECCIÓN HERO DEL PANEL DE INICIO -->
    <div class="dash-hero">
      <div style="z-index:1;">
        <div class="hero-title">Panel de Control</div>
        <div class="hero-sub">Bienvenida, Admin &mdash; Sayagym</div>
        <!-- Mostramos la fecha actual formatea en español -->
        <div class="hero-date"><i class="ti ti-calendar me-1"></i><?php echo date('d \d\e F \d\e Y'); ?></div>
      </div>
      <div class="hero-actions">
        <!-- Estos enlaces llevan a crear recursos nuevos -->
        <a href="nuevoEntrenador.php" class="btn-hero-out"><i class="ti ti-plus"></i> Entrenador</a>
        <a href="nuevoSocio.php" class="btn-hero-sol"><i class="ti ti-user-plus"></i> Inscribir Socio</a>
      </div>
    </div>

    <!-- SECCIÓN DE KPIs (Tarjetas con números importantes) -->
    <div class="kpi-strip">
      <!-- Tarjeta: Total de socios -->
      <a href="socios.php" class="ks-card c-blue">
        <div class="ks-icon"><i class="ti ti-users"></i></div>
        <div>
          <div class="ks-num"><?php echo $total_socios; ?></div>
          <div class="ks-label">Socios Registrados</div>
        </div>
      </a>
      <!-- Tarjeta: Membresías vigentes/activas -->
      <a href="membresias.php" class="ks-card c-green">
        <div class="ks-icon"><i class="ti ti-circle-check"></i></div>
        <div>
          <div class="ks-num"><?php echo $activos; ?></div>
          <div class="ks-label">Membresías Vigentes</div>
        </div>
      </a>
      <!-- Tarjeta: Membresías vencidas -->
      <a href="membresias.php" class="ks-card c-red">
        <div class="ks-icon"><i class="ti ti-alert-triangle"></i></div>
        <div>
          <div class="ks-num"><?php echo $vencidos; ?></div>
          <div class="ks-label">Membresías Vencidas</div>
        </div>
      </a>
      <!-- Tarjeta: Entrenadores totales -->
      <a href="entrenadores.php" class="ks-card c-gold">
        <div class="ks-icon"><i class="ti ti-barbell"></i></div>
        <div>
          <div class="ks-num"><?php echo $total_entrenadores; ?></div>
          <div class="ks-label">Coaches Activos</div>
        </div>
      </a>
    </div>

    <!-- SECCIÓN INFERIOR CON DOS COLUMNAS -->
    <div class="bottom-row">

      <!-- Columna 1: Últimas inscripciones -->
      <div class="card">
        <div class="card-header gray">
          <span class="card-title">Últimas Inscripciones</span>
          <a href="socios.php" class="btn btn-outline" style="padding:5px 12px; font-size:0.8rem;">Ver todos</a>
        </div>
        <div class="table-responsive">
          <table class="gym-table">
            <thead>
              <tr>
                <th>Socio</th>
                <th>Plan</th>
                <th>Registrado</th>
                <th>Vence</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php
// Recorremos los registros de los 5 socios más recientes con un ciclo while (mientras haya información)
while ($socio = $recientes->fetch_assoc()) {

  // Evaluamos si el socio ya está vencido comprobando si su fecha es menor a hoy
  $esta_vencido = strtotime($socio['fecha_vencimiento']) < strtotime($hoy);

  // Asignamos colores y textos para diferenciar visualmente el estado
  if ($socio['estado'] == 'inactivo') {
    $color_etiqueta = 'badge-secondary';
    $texto_etiqueta = 'INACTIVO';
  }
  else if ($esta_vencido) {
    $color_etiqueta = 'badge-red';
    $texto_etiqueta = 'VENCIDO';
  }
  else {
    $color_etiqueta = 'badge-green';
    $texto_etiqueta = 'ACTIVO';
  }
?>
              <tr>
                <!-- Imprimimos el nombre, el plan y las fechas formateadas -->
                <td class="td-name"><?php echo $socio['nombre'] . " " . $socio['apellido']; ?></td>
                <td><span class="badge badge-blue"><?php echo $socio['plan']; ?></span></td>
                <td class="td-muted"><?php echo date('d/m/Y', strtotime($socio['fecha_registro'])); ?></td>
                <td class="td-muted"><?php echo date('d/m/Y', strtotime($socio['fecha_vencimiento'])); ?></td>
                <!-- Imprimimos la etiqueta del estado con su color correspondiente -->
                <td><span class="badge <?php echo $color_etiqueta; ?>"><?php echo $texto_etiqueta; ?></span></td>
              </tr>
              <?php
} // Fin del ciclo while
?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Columna 2: Alertas de Vencimiento -->
      <div class="card">
        <div class="card-header gray">
          <span class="card-title" style="display:flex; align-items:center; gap:8px;">
            <span style="background:var(--danger-lt); color:var(--danger); padding:3px 9px; border-radius:999px; font-size:0.72rem; font-weight:700;">ALERTA</span>
            Membresías Inactivas
          </span>
        </div>
        <?php
// Guardamos las personas que van a vencer pronto en un arreglo (lista)
$personas_por_vencer = [];
while ($persona = $por_vencer->fetch_assoc()) {
  $personas_por_vencer[] = $persona;
}

// Si la lista está vacía, mostramos un mensaje de felicitación
if (count($personas_por_vencer) === 0) {
?>
        <div style="padding:44px 24px; text-align:center;">
          <i class="ti ti-circle-check" style="font-size:2.5rem; color:var(--green); display:block; margin-bottom:10px;"></i>
          <div style="font-weight:600; color:var(--text);">¡Todo en orden!</div>
          <div style="font-size:0.85rem; color:var(--muted); margin-top:4px;">Sin membresías próximas a vencer.</div>
        </div>
        <?php
}
else {
  // Si hay personas en la lista, usamos un ciclo for-each (por cada elemento)
  foreach ($personas_por_vencer as $persona) {

    // Calculamos cuántos días faltan para el vencimiento
    $diferencia = strtotime($persona['fecha_vencimiento']) - strtotime($hoy);
    // Dividimos entre 86400 (que son los segundos en un día) para saber los días
    $dias_restantes = (int)($diferencia / 86400);

    // Asignamos estilos según los días que les queden  
    if ($persona['estado'] == 'inactivo') {
      $alerta_color = 'badge-secondary';
      $alerta_texto = 'Inactivo';
    }
    else if ($dias_restantes < 0) {
      $alerta_color = 'badge-red';
      $alerta_texto = 'Vencido';
    }
    else {
      // Si faltan menos de dos días, se pone en rojo. Si faltan más, en amarillo (gold)
      $alerta_color = $dias_restantes <= 2 ? 'badge-red' : 'badge-gold';
      $alerta_texto = $dias_restantes === 0 ? 'Hoy' : "en $dias_restantes día" . ($dias_restantes > 1 ? 's' : '');
    }
?>
        <div class="alert-row">
          <div>
            <!-- Nombre de la persona -->
            <div class="alert-name"><?php echo $persona['nombre'] . " " . $persona['apellido']; ?></div>
            <!-- Qué día vence exactamente -->
            <div class="alert-sub">Vence el <?php echo date('d/m/Y', strtotime($persona['fecha_vencimiento'])); ?></div>
          </div>
          <!-- Etiqueta que avisa los días -->
          <span class="badge <?php echo $alerta_color; ?>">
            <?php echo $alerta_texto; ?>
          </span>
        </div>
        <?php
  } // Fin del ciclo foreach
} // Fin de la condición IF
?>
      </div>

    </div> <!-- Cierra el bottom-row -->
  </div> <!-- Cierra el container -->
</div> <!-- Cierra el page-wrapper -->

<!-- Por último, incluimos el pie de página -->
<?php include 'footer.php'; ?>
