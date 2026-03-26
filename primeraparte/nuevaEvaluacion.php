<?php
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

$id_socio_pre = isset($_GET['id_socio']) ? (int)$_GET['id_socio'] : 0;

if ($_POST) {
    $id_s  = (int)$_POST['id_socio'];
    $id_e  = !empty($_POST['id_entrenador']) ? (int)$_POST['id_entrenador'] : null;
    $fecha = $conexion->real_escape_string($_POST['fecha']);

    $peso  = !empty($_POST['peso'])             ? (float)$_POST['peso']             : null;
    $alt   = !empty($_POST['altura'])           ? (float)$_POST['altura']           : null;
    $grasa = !empty($_POST['porcentaje_grasa']) ? (float)$_POST['porcentaje_grasa'] : null;
    $musc  = !empty($_POST['masa_muscular'])    ? (float)$_POST['masa_muscular']    : null;
    $pecho = !empty($_POST['pecho'])            ? (float)$_POST['pecho']            : null;
    $cin   = !empty($_POST['cintura'])          ? (float)$_POST['cintura']          : null;
    $cad   = !empty($_POST['cadera'])           ? (float)$_POST['cadera']           : null;
    $bic   = !empty($_POST['bicep'])            ? (float)$_POST['bicep']            : null;
    $mus   = !empty($_POST['muslo'])            ? (float)$_POST['muslo']            : null;
    $obj   = $conexion->real_escape_string($_POST['objetivo'] ?? '');
    $notas = $conexion->real_escape_string($_POST['notas'] ?? '');

    // Calcular IMC
    $imc = null;
    if ($peso && $alt && $alt > 0) {
        $alt_m = $alt / 100;
        $imc   = round($peso / ($alt_m * $alt_m), 2);
    }

    $id_e_sql   = $id_e   ? $id_e   : 'NULL';
    $peso_sql   = $peso   ? $peso   : 'NULL';
    $alt_sql    = $alt    ? $alt    : 'NULL';
    $imc_sql    = $imc    ? $imc    : 'NULL';
    $grasa_sql  = $grasa  ? $grasa  : 'NULL';
    $musc_sql   = $musc   ? $musc   : 'NULL';
    $pecho_sql  = $pecho  ? $pecho  : 'NULL';
    $cin_sql    = $cin    ? $cin    : 'NULL';
    $cad_sql    = $cad    ? $cad    : 'NULL';
    $bic_sql    = $bic    ? $bic    : 'NULL';
    $mus_sql    = $mus    ? $mus    : 'NULL';

    $conexion->query("INSERT INTO evaluaciones_fisicas
        (id_socio, id_entrenador, fecha, peso, altura, imc, porcentaje_grasa, masa_muscular,
         pecho, cintura, cadera, bicep, muslo, objetivo, notas)
        VALUES ($id_s, $id_e_sql, '$fecha', $peso_sql, $alt_sql, $imc_sql, $grasa_sql, $musc_sql,
                $pecho_sql, $cin_sql, $cad_sql, $bic_sql, $mus_sql, '$obj', '$notas')");

    echo "<script>window.location='evaluaciones.php?id_socio=$id_s&res=guardada';</script>"; exit;
}

$socios      = $conexion->query("SELECT id_socio, nombre, apellido FROM socios ORDER BY nombre ASC");
$entrenadores = $conexion->query("SELECT id_entrenador, nombre FROM entrenadores WHERE estado='activo' ORDER BY nombre ASC");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <form method="POST" class="card" style="max-width:860px; margin:0 auto;">
      <div class="card-header" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
        <span class="card-title" style="color:#fff;"><i class="ti ti-ruler-measure me-2"></i>Nueva Evaluación Física</span>
      </div>
      <div class="card-body">

        <!-- Encabezado -->
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:20px;">
          <div>
            <label class="form-label">Socio</label>
            <select name="id_socio" class="form-select" required>
              <option value="">— Seleccionar socio —</option>
              <?php while ($s = $socios->fetch_assoc()): ?>
              <option value="<?php echo $s['id_socio']; ?>" <?php if($id_socio_pre==$s['id_socio']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($s['nombre'].' '.$s['apellido']); ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Entrenador (opcional)</label>
            <select name="id_entrenador" class="form-select">
              <option value="">— Sin asignar —</option>
              <?php while ($en = $entrenadores->fetch_assoc()): ?>
              <option value="<?php echo $en['id_entrenador']; ?>"><?php echo htmlspecialchars($en['nombre']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Fecha de Evaluación</label>
            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
        </div>

        <!-- Peso y Talla -->
        <div class="hr-text"><span>Composición Corporal</span></div>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-top:16px;">
          <div>
            <label class="form-label">Peso (kg)</label>
            <input type="number" step="0.1" name="peso" id="peso" class="form-control" placeholder="70.5" oninput="calcIMC()">
          </div>
          <div>
            <label class="form-label">Altura (cm)</label>
            <input type="number" step="0.1" name="altura" id="altura" class="form-control" placeholder="175" oninput="calcIMC()">
          </div>
          <div>
            <label class="form-label">IMC</label>
            <input type="number" step="0.01" name="imc" id="imc" class="form-control" placeholder="Auto" readonly style="background:#F9FAFB; cursor:default;">
            <small id="imc-label" style="font-size:0.75rem; color:var(--muted);"></small>
          </div>
          <div>
            <label class="form-label">% Grasa Corporal</label>
            <input type="number" step="0.1" name="porcentaje_grasa" class="form-control" placeholder="15.0">
          </div>
          <div>
            <label class="form-label">Masa Muscular (kg)</label>
            <input type="number" step="0.1" name="masa_muscular" class="form-control" placeholder="35.0">
          </div>
          <div>
            <label class="form-label">Objetivo</label>
            <select name="objetivo" class="form-select">
              <option value="">— Sin objetivo —</option>
              <option value="Pérdida de peso">Pérdida de peso</option>
              <option value="Ganancia muscular">Ganancia muscular</option>
              <option value="Resistencia">Resistencia</option>
              <option value="Mantenimiento">Mantenimiento</option>
              <option value="Rehabilitación">Rehabilitación</option>
            </select>
          </div>
        </div>

        <!-- Medidas -->
        <div class="hr-text" style="margin-top:20px;"><span>Medidas Corporales (cm)</span></div>
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-top:16px;">
          <div>
            <label class="form-label">Pecho</label>
            <input type="number" step="0.1" name="pecho" class="form-control" placeholder="cm">
          </div>
          <div>
            <label class="form-label">Cintura</label>
            <input type="number" step="0.1" name="cintura" class="form-control" placeholder="cm">
          </div>
          <div>
            <label class="form-label">Cadera</label>
            <input type="number" step="0.1" name="cadera" class="form-control" placeholder="cm">
          </div>
          <div>
            <label class="form-label">Bícep</label>
            <input type="number" step="0.1" name="bicep" class="form-control" placeholder="cm">
          </div>
          <div>
            <label class="form-label">Muslo</label>
            <input type="number" step="0.1" name="muslo" class="form-control" placeholder="cm">
          </div>
        </div>

        <!-- Notas -->
        <div style="margin-top:18px;">
          <label class="form-label">Notas del Evaluador</label>
          <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones adicionales, condición física general, recomendaciones..."></textarea>
        </div>

      </div>
      <div class="card-footer">
        <a href="evaluaciones.php" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-red"><i class="ti ti-device-floppy me-1"></i>Guardar Evaluación</button>
      </div>
    </form>
  </div>
</div>

<script>
function calcIMC() {
    const peso   = parseFloat(document.getElementById('peso').value);
    const altura = parseFloat(document.getElementById('altura').value);
    const imcInput = document.getElementById('imc');
    const imcLabel = document.getElementById('imc-label');

    if (peso > 0 && altura > 0) {
        const altM = altura / 100;
        const imc  = peso / (altM * altM);
        imcInput.value = imc.toFixed(2);

        let cat = '';
        if      (imc < 18.5) cat = '⚠ Bajo peso';
        else if (imc < 25)   cat = '✓ Peso normal';
        else if (imc < 30)   cat = '⚠ Sobrepeso';
        else                 cat = '✗ Obesidad';
        imcLabel.textContent = cat;
    } else {
        imcInput.value = '';
        imcLabel.textContent = '';
    }
}
</script>

<?php include 'footer.php'; ?>
