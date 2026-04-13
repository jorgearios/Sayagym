<?php
/**
 * Archivo: nuevaRutina.php
 * Descripción: Generación y registro de nuevas agrupaciones de rutinas.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) {
  header("Location: login.php");
  exit();
}
include 'header.php';

if ($_POST) {
  $nom = $conexion->real_escape_string($_POST['nombre_rutina']);
  $desc = $conexion->real_escape_string($_POST['descripcion']);
  $niv = $conexion->real_escape_string($_POST['nivel']);

  $conexion->query("INSERT INTO rutinas (nombre_rutina, descripcion, nivel) VALUES ('$nom','$desc','$niv')");
  $id_rutina = $conexion->insert_id;

  // Insertar ejercicios
  if (!empty($_POST['ejercicio_id'])) {
    foreach ($_POST['ejercicio_id'] as $k => $eid) {
      $eid = (int) $eid;
      if ($eid <= 0)
        continue;
      $series = (int) ($_POST['series'][$k] ?? 3);
      $reps = (int) ($_POST['repeticiones'][$k] ?? 10);
      $orden = (int) ($_POST['orden'][$k] ?? ($k + 1));
      $conexion->query("INSERT INTO rutina_ejercicio (id_rutina, id_ejercicio, orden, series, repeticiones) VALUES ($id_rutina, $eid, $orden, $series, $reps)");
    }
  }
  echo "<script>window.location='rutinas.php?res=creada';</script>";
  exit;
}

$ejercicios = $conexion->query("SELECT id_ejercicio, nombre, grupo_muscular FROM ejercicios ORDER BY grupo_muscular, nombre ASC");
$lista_ej = [];
while ($e = $ejercicios->fetch_assoc())
  $lista_ej[] = $e;
$ej_json = json_encode($lista_ej);
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <form method="POST" class="card" style="max-width:860px; margin:0 auto;">
      <div class="card-header" style="background:linear-gradient(135deg,#4A148C,#7B1FA2);">
        <span class="card-title" style="color:#fff;"><i class="ti ti-playlist-add me-2"></i>Nueva Rutina de
          Entrenamiento</span>
      </div>
      <div class="card-body">

        <!-- Datos generales -->
        <div style="display:grid; grid-template-columns:1fr 1fr 160px; gap:16px; margin-bottom:24px;">
          <div>
            <label class="form-label">Nombre de la Rutina</label>
            <input type="text" name="nombre_rutina" class="form-control" required placeholder="Ej. Fuerza Full Body A">
          </div>
          <div>
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control" placeholder="Objetivo o notas">
          </div>
          <div>
            <label class="form-label">Nivel</label>
            <select name="nivel" class="form-select">
              <option value="Principiante">Principiante</option>
              <option value="Intermedio" selected>Intermedio</option>
              <option value="Avanzado">Avanzado</option>
            </select>
          </div>
        </div>

        <div class="hr-text"><span>Ejercicios de la Rutina</span></div>

        <?php if (empty($lista_ej)): ?>
          <div class="alert alert-danger" style="margin-top:12px;">
            <i class="ti ti-alert-circle me-1"></i> No hay ejercicios en el catálogo.
            <a href="ejercicios.php" style="color:inherit; font-weight:700;">Agrega ejercicios primero</a>.
          </div>
        <?php else: ?>

          <!-- Tabla de ejercicios dinámica -->
          <div style="margin-top:16px;">
            <table style="width:100%; border-collapse:collapse;" id="tabla-ejercicios">
              <thead>
                <tr style="background:#F9FAFB; border-bottom:2px solid var(--border);">
                  <th
                    style="padding:10px 12px; font-size:0.78rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:1px; text-align:left;">
                    #</th>
                  <th
                    style="padding:10px 12px; font-size:0.78rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:1px; text-align:left;">
                    Ejercicio</th>
                  <th
                    style="padding:10px 12px; font-size:0.78rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:1px; width:90px;">
                    Series</th>
                  <th
                    style="padding:10px 12px; font-size:0.78rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:1px; width:90px;">
                    Reps</th>
                  <th style="padding:10px 12px; width:50px;"></th>
                </tr>
              </thead>
              <tbody id="filas-ejercicios">
                <!-- Fila inicial (template) -->
              </tbody>
            </table>
            <button type="button" onclick="agregarFila()" class="btn btn-outline" style="margin-top:12px;">
              <i class="ti ti-plus me-1"></i>Agregar Ejercicio
            </button>
          </div>

        <?php endif; ?>
      </div>
      <div class="card-footer">
        <a href="rutinas.php" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-red"><i class="ti ti-device-floppy me-1"></i>Guardar Rutina</button>
      </div>
    </form>

  </div>
</div>

<script>
  const ejercicios = <?php echo $ej_json; ?>;
  let contador = 0;

  function buildSelect(selected) {
    let html = '<option value="">— Seleccionar —</option>';
    let grupo = '';
    ejercicios.forEach(e => {
      if (e.grupo_muscular !== grupo) {
        if (grupo) html += '</optgroup>';
        html += '<optgroup label="' + e.grupo_muscular + '">';
        grupo = e.grupo_muscular;
      }
      const sel = (e.id_ejercicio == selected) ? 'selected' : '';
      html += '<option value="' + e.id_ejercicio + '" ' + sel + '>' + e.nombre + '</option>';
    });
    if (grupo) html += '</optgroup>';
    return html;
  }

  function agregarFila(eid, series, reps) {
    contador++;
    const tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid var(--border)';
    tr.innerHTML = `
        <td style="padding:10px 12px; color:var(--muted); font-size:0.85rem;">${contador}</td>
        <td style="padding:8px 12px;">
            <input type="hidden" name="orden[]" value="${contador}">
            <select name="ejercicio_id[]" class="form-select" style="margin-bottom:0;" required>
                ${buildSelect(eid || 0)}
            </select>
        </td>
        <td style="padding:8px 12px;">
            <input type="number" name="series[]" class="form-control" value="${series || 3}" min="1" max="20" style="text-align:center;">
        </td>
        <td style="padding:8px 12px;">
            <input type="number" name="repeticiones[]" class="form-control" value="${reps || 10}" min="1" max="100" style="text-align:center;">
        </td>
        <td style="padding:8px 12px;">
            <button type="button" onclick="this.closest('tr').remove(); renumerar();" class="btn btn-icon" title="Quitar">
                <i class="ti ti-x"></i>
            </button>
        </td>`;
    document.getElementById('filas-ejercicios').appendChild(tr);
  }

  function renumerar() {
    const rows = document.querySelectorAll('#filas-ejercicios tr');
    rows.forEach((tr, i) => {
      tr.cells[0].textContent = i + 1;
      const hidden = tr.querySelector('input[name="orden[]"]');
      if (hidden) hidden.value = i + 1;
    });
    contador = rows.length;
  }

  // Iniciar con 3 filas vacías
  agregarFila(); agregarFila(); agregarFila();
</script>

<?php include 'footer.php'; ?>