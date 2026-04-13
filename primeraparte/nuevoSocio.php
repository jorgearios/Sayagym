<?php
/**
 * Archivo: nuevoSocio.php
 * Descripción: Formulario de registro para dar de alta a un socio nuevo en el sistema.
 * Parte del sistema integral de gestión Sayagym.
 */

include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }
include 'header.php';

// Calcula límite calórico diario según edad y género (del proyecto referencia)
function obtenerLimiteCaloricoAutomatico($fecha_nacimiento, $genero) {
    if (empty($fecha_nacimiento)) return 2000;
    $edad = (new DateTime($fecha_nacimiento))->diff(new DateTime())->y;
    $gen  = strtolower($genero ?? '');
    if (in_array($gen, ['femenino', 'f'])) {
        if ($edad <= 13) return 1600;
        if ($edad <= 18) return 1800;
        if ($edad <= 30) return 2000;
        if ($edad <= 50) return 1800;
        return 1600;
    } else {
        if ($edad <= 13) return 1800;
        if ($edad <= 18) return 2200;
        if ($edad <= 30) return 2400;
        if ($edad <= 50) return 2200;
        return 2000;
    }
}

if ($_POST) {
    $nom    = $conexion->real_escape_string($_POST['nombre']);
    $ape    = $conexion->real_escape_string($_POST['apellido']);
    $gen    = $conexion->real_escape_string($_POST['genero'] ?? 'Masculino');
    $tel    = $conexion->real_escape_string($_POST['telefono']);
    $tel_e  = $conexion->real_escape_string($_POST['contacto_emergencia']);
    $cor    = $conexion->real_escape_string($_POST['correo']);
    $dir    = $conexion->real_escape_string($_POST['direccion']);
    $f_nac  = $_POST['fecha_nacimiento'];
    $id_mem = $_POST['id_membresia'];
    $id_ent = !empty($_POST['id_entrenador']) ? $_POST['id_entrenador'] : "NULL";
    $f_reg  = date('Y-m-d');
    $lim_cal = (int)($_POST['limite_calorias'] ?? obtenerLimiteCaloricoAutomatico($f_nac, $gen));

    $foto_path = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fn   = time() . '_' . rand(1000,9999) . '.' . $ext;
        $dest = "imagenes/" . $fn;
        if (!is_dir('imagenes')) mkdir('imagenes', 0755, true);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) $foto_path = $dest;
    }

    $f_ven = $f_reg;
    if ($id_mem) {
        $mr = $conexion->query("SELECT duracion_meses FROM membresias WHERE id_membresia=$id_mem");
        if ($mr && $mr->num_rows > 0) {
            $meses = $mr->fetch_assoc()['duracion_meses'];
            $f_ven = date('Y-m-d', strtotime("+$meses months"));
        }
    }

    $pass_sql = "NULL";
    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pass_sql = "'" . $conexion->real_escape_string($hash) . "'";
    }

    // Columna genero y limite_calorias — si no existen en tu BD, el SQL falla con gracia
    $col_genero = $conexion->query("SHOW COLUMNS FROM socios LIKE 'genero'")->num_rows > 0;
    $col_limite  = $conexion->query("SHOW COLUMNS FROM socios LIKE 'limite_calorias'")->num_rows > 0;

    if (!$col_genero)  $conexion->query("ALTER TABLE socios ADD COLUMN genero VARCHAR(20) DEFAULT 'Masculino' AFTER apellido");
    if (!$col_limite)  $conexion->query("ALTER TABLE socios ADD COLUMN limite_calorias INT DEFAULT 2000");

    $sql = "INSERT INTO socios
        (nombre,apellido,genero,telefono,contacto_emergencia,correo,password,
         direccion,fecha_nacimiento,fecha_registro,fecha_vencimiento,
         id_membresia,id_entrenador,estado,foto,qr_codigo,limite_calorias)
        VALUES
        ('$nom','$ape','$gen','$tel','$tel_e','$cor',$pass_sql,
         '$dir','$f_nac','$f_reg','$f_ven',
         $id_mem,$id_ent,'activo','$foto_path','SGY-TMP',$lim_cal)";

    if ($conexion->query($sql)) {
        $nuevo_id = $conexion->insert_id;
        $qr = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $nuevo_id;
        $conexion->query("UPDATE socios SET qr_codigo='$qr' WHERE id_socio=$nuevo_id");
        if ($id_mem) {
            $conexion->query("INSERT INTO socios_membresias (id_socio,id_membresia,fecha_inicio,fecha_fin,estado)
                              VALUES ($nuevo_id,$id_mem,'$f_reg','$f_ven','activa')");
        }
        echo "<script>window.location='socios.php';</script>";
    } else {
        die("Error: " . $conexion->error);
    }
}
?>
<div class="page-wrapper">
  <div class="container-xl mt-4">
    <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

      <form method="POST" class="card" enctype="multipart/form-data">
        <div class="card-header red">
          <span class="card-title"><i class="ti ti-user-plus me-2"></i>Nueva Inscripción de Socio</span>
        </div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div><label class="form-label">Nombre(s)</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Juan"></div>
            <div><label class="form-label">Apellido(s)</label>
              <input type="text" name="apellido" class="form-control" required placeholder="Pérez"></div>
            <div><label class="form-label">Género</label>
              <select name="genero" class="form-select" id="sel-genero" onchange="actualizarLimite()">
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
              </select></div>
            <div><label class="form-label">Fecha de Nacimiento</label>
              <input type="date" name="fecha_nacimiento" class="form-control" id="inp-fnac" onchange="actualizarLimite()"></div>
            <div><label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control" placeholder="834 000 0000"></div>
            <div><label class="form-label">Contacto de Emergencia</label>
              <input type="text" name="contacto_emergencia" class="form-control"></div>
            <div><label class="form-label">Correo Electrónico</label>
              <input type="email" name="correo" class="form-control" placeholder="socio@ejemplo.com"></div>
            <div><label class="form-label">Contraseña (acceso socio)</label>
              <input type="password" name="password" class="form-control" placeholder="Opcional"></div>
            <div style="grid-column:1/-1;"><label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-control" placeholder="Calle, Número y Colonia"></div>
            <div style="grid-column:1/-1;"><label class="form-label">Foto de Perfil</label>
              <input type="file" name="foto" class="form-control" accept="image/*"></div>
          </div>
          <div class="hr-text mt-3"><span>Plan, Entrenador y Nutrición</span></div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:12px;">
            <div><label class="form-label">Plan de Membresía</label>
              <select name="id_membresia" class="form-select" required>
                <option value="">Seleccione un plan...</option>
                <?php $mems=$conexion->query("SELECT * FROM membresias WHERE estado='activo'");
                while($m=$mems->fetch_assoc()) echo "<option value='{$m['id_membresia']}'>{$m['nombre']} - \${$m['precio']}</option>"; ?>
              </select></div>
            <div><label class="form-label">Entrenador</label>
              <select name="id_entrenador" class="form-select">
                <option value="">-- Sin entrenador --</option>
                <?php $ps=$conexion->query("SELECT id_entrenador,nombre FROM entrenadores WHERE estado='activo'");
                while($p=$ps->fetch_assoc()) echo "<option value='{$p['id_entrenador']}'>{$p['nombre']}</option>"; ?>
              </select></div>
            <div><label class="form-label">Límite Calórico (kcal/día)</label>
              <input type="number" name="limite_calorias" id="inp-limite" class="form-control" value="2000">
              <small style="font-size:.72rem;color:var(--muted);" id="lbl-limite">Se ajusta por edad y género</small></div>
          </div>
        </div>
        <div class="card-footer">
          <a href="index.php" class="btn btn-link">Cancelar</a>
          <button type="submit" class="btn btn-red"><i class="ti ti-check me-1"></i>Registrar Socio</button>
        </div>
      </form>

      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="motive-panel">
          <div class="line1">Regístrate y<br>haz un cambio</div>
          <div class="line2">ahora en tu vida</div>
          <div style="font-family:'Oswald',sans-serif;font-size:1.5rem;font-weight:700;color:#F5A623;margin-top:8px;">¡TÚ PUEDES LOGRARLO!</div>
        </div>
        <div class="card" style="border-left:4px solid #6D28D9;">
          <div class="card-body" style="padding:16px;">
            <p style="font-size:.78rem;color:#6D28D9;font-weight:700;text-transform:uppercase;margin-bottom:6px;">
              <i class="ti ti-flame me-1"></i> Calorías automáticas</p>
            <p style="font-size:.83rem;color:#4B5563;line-height:1.6;">
              Al ingresar fecha de nacimiento y género, el límite calórico se calcula automáticamente según estándares por edad.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
const tablas = {
    m:[[0,13,1800],[14,18,2200],[19,30,2400],[31,50,2200],[51,150,2000]],
    f:[[0,13,1600],[14,18,1800],[19,30,2000],[31,50,1800],[51,150,1600]]
};
function actualizarLimite(){
    const fn=document.getElementById('inp-fnac').value;
    const gn=document.getElementById('sel-genero').value.toLowerCase();
    if(!fn) return;
    const hoy=new Date(), nac=new Date(fn);
    let edad=hoy.getFullYear()-nac.getFullYear();
    if(hoy<new Date(nac.setFullYear(hoy.getFullYear()))) edad--;
    const key=(gn==='femenino')?'f':'m';
    let lim=key==='f'?1600:2000;
    for(const[min,max,cal] of tablas[key]){ if(edad>=min&&edad<=max){lim=cal;break;} }
    document.getElementById('inp-limite').value=lim;
    document.getElementById('lbl-limite').textContent=lim+' kcal — estimado para '+edad+' años';
}
</script>
<?php include 'footer.php'; ?>
