<?php
// inicioSocio.php - Interfaz o Dashboard principal para el Socio
include 'config.php';
include 'header.php';

// Validar que efectivamente es un Socio
if (!esSocio()) {
  echo "<div class='container-xl mt-4'><div class='alert alert-danger'>Acceso exclusivo para miembros y socios.</div></div>";
  include 'footer.php';
  exit();
}

$id_socio = $_SESSION['usuario_id'];

// Obtener la información del socio actual
$stmt = $conexion->prepare("
    SELECT s.*, m.nombre as plan 
    FROM socios s 
    LEFT JOIN membresias m ON s.id_membresia = m.id_membresia 
    WHERE s.id_socio = ?
");
$stmt->bind_param("i", $id_socio);
$stmt->execute();
$socio = $stmt->get_result()->fetch_assoc();

$hoy = date('Y-m-d');
$esta_vencido = strtotime($socio['fecha_vencimiento']) < strtotime($hoy);
$color_estado = $esta_vencido ? 'badge-red' : 'badge-green';
$texto_estado = $esta_vencido ? 'VENCIDO' : 'ACTIVO';
if ($socio['estado'] == 'inactivo') {
  $color_estado = 'badge-secondary';
  $texto_estado = 'INACTIVO';
}
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- Hero del Socio -->
    <div class="dash-hero" style="background: linear-gradient(135deg, var(--blue) 0%, var(--blue-light) 100%);">
      <div style="z-index:1;">
        <div class="hero-title">Mi Tablero Personal</div>
        <div class="hero-sub">Hola de nuevo, <?php echo htmlspecialchars($socio['nombre']); ?>! Bienvenido a Sayagym.
        </div>
        <div class="hero-date" style="color:#BBDEFB;"><i
            class="ti ti-calendar me-1"></i><?php echo date('d \d\e F \d\e Y'); ?></div>
      </div>
    </div>

    <!-- Información de Membresía -->
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card h-100">
          <div class="card-header gray">
            <span class="card-title">Resumen de mi Membresía</span>
          </div>
          <div class="card-body">
            <div
              style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:12px;">
              <span style="color:var(--muted); font-size:0.9rem;">Plan Actual</span>
              <span
                style="font-weight:600; color:var(--text);"><?php echo $socio['plan'] ?: 'Sin membresía asignada'; ?></span>
            </div>
            <div
              style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:12px;">
              <span style="color:var(--muted); font-size:0.9rem;">Fecha de Vencimiento</span>
              <span
                style="font-weight:600; color:var(--text);"><?php echo date('d/m/Y', strtotime($socio['fecha_vencimiento'])); ?></span>
            </div>
            <div
              style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:12px;">
              <span style="color:var(--muted); font-size:0.9rem;">Estado de mi Cuenta</span>
              <span class="badge <?php echo $color_estado; ?>"><?php echo $texto_estado; ?></span>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:var(--muted); font-size:0.9rem;">Correo Vinculado</span>
              <span
                style="font-weight:600; color:var(--text);"><?php echo htmlspecialchars($socio['correo'] ?: 'No proporcionado'); ?></span>
            </div>
          </div>
          <?php if ($esta_vencido || $socio['estado'] == 'inactivo') { ?>
            <div class="card-footer" style="background:#FEE2E2;">
              <span style="color:var(--danger); font-size:0.85rem;"><i class="ti ti-alert-triangle"></i> Acércate a
                recepción para renovar tu membresía y seguir disfrutando del gimnasio.</span>
            </div>
          <?php } ?>
        </div>
      </div>

      <div class="col-md-6 mb-4">
        <!-- QR Code de Acceso -->
        <div class="card h-100">
          <div class="card-header gray">
            <span class="card-title">Mi Código QR de Acceso</span>
          </div>
          <div class="card-body"
            style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:28px;">
            <?php
            // Generar qr_codigo si el socio no tiene uno
            if (empty($socio['qr_codigo'])) {
              $qr_code = 'SGY-' . $socio['id_socio'] . '-' . strtoupper(bin2hex(random_bytes(4)));
              $conexion->query("UPDATE socios SET qr_codigo='$qr_code' WHERE id_socio={$socio['id_socio']}");
              $socio['qr_codigo'] = $qr_code;
            }
            ?>
            <div id="qrcode" style="margin-bottom:16px;"></div>
            <p
              style="font-family:'Oswald',sans-serif; font-size:1rem; font-weight:600; color:var(--text); letter-spacing:2px; margin-bottom:4px;">
              <?php echo htmlspecialchars($socio['qr_codigo']); ?>
            </p>
            <p style="font-size:0.8rem; color:var(--muted); text-align:center;">
              Muestra este código en recepción para registrar tu entrada al gimnasio.
            </p>
          </div>
        </div>
      </div>
    </div>

  </div><!-- container-xl -->

  <!-- Acceso rápido nutrición -->
  <div class="container-xl" style="margin-top:16px;">
    <div class="card">
      <div class="card-body"
        style="padding:18px 22px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:14px;">
          <div
            style="width:44px;height:44px;border-radius:10px;background:#DCFCE7;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#15803D;flex-shrink:0;">
            <i class="ti ti-salad"></i>
          </div>
          <div>
            <div style="font-weight:700; font-size:0.95rem;">Mi Seguimiento Nutricional</div>
            <div style="font-size:0.8rem; color:var(--muted);">Registra tus alimentos y controla tus calorías diarias.
            </div>
          </div>
        </div>
        <a href="miNutricion.php?id_socio=<?php echo $id_socio; ?>" class="btn btn-red">
          <i class="ti ti-arrow-right me-1"></i>Ver mi nutrición
        </a>
      </div>
    </div>
  </div>

</div><!-- page-wrapper -->

<?php include 'footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  new QRCode(document.getElementById("qrcode"), {
    text: "<?php echo htmlspecialchars($socio['qr_codigo'], ENT_QUOTES); ?>",
    width: 160, height: 160,
    colorDark: "#1A1A1A", colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.M
  });
</script>