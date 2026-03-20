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
        <div class="hero-sub">Hola de nuevo, <?php echo htmlspecialchars($socio['nombre']); ?>! Bienvenido a Sayagym.</div>
        <div class="hero-date" style="color:#BBDEFB;"><i class="ti ti-calendar me-1"></i><?php echo date('d \d\e F \d\e Y'); ?></div>
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
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:12px;">
                        <span style="color:var(--muted); font-size:0.9rem;">Plan Actual</span>
                        <span style="font-weight:600; color:var(--text);"><?php echo $socio['plan'] ?: 'Sin membresía asignada'; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:12px;">
                        <span style="color:var(--muted); font-size:0.9rem;">Fecha de Vencimiento</span>
                        <span style="font-weight:600; color:var(--text);"><?php echo date('d/m/Y', strtotime($socio['fecha_vencimiento'])); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:12px;">
                        <span style="color:var(--muted); font-size:0.9rem;">Estado de mi Cuenta</span>
                        <span class="badge <?php echo $color_estado; ?>"><?php echo $texto_estado; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--muted); font-size:0.9rem;">Correo Vinculado</span>
                        <span style="font-weight:600; color:var(--text);"><?php echo htmlspecialchars($socio['correo'] ?: 'No proporcionado'); ?></span>
                    </div>
                </div>
                <?php if ($esta_vencido || $socio['estado'] == 'inactivo') { ?>
                <div class="card-footer" style="background:#FEE2E2;">
                    <span style="color:var(--danger); font-size:0.85rem;"><i class="ti ti-alert-triangle"></i> Acércate a recepción para renovar tu membresía y seguir disfrutando del gimnasio.</span>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <!-- Espacio reservado para cosas futuras del socio, como rutinas, métricas o código QR -->
            <div class="card h-100" style="display:flex; align-items:center; justify-content:center; flex-direction:column; background:#F9FAFB; border:2px dashed var(--border);">
                <i class="ti ti-qrcode" style="font-size:4rem; color:var(--muted); margin-bottom:16px;"></i>
                <div style="font-weight:600; color:var(--text);">Mi Código de Acceso</div>
                <div style="font-size:0.85rem; color:var(--muted);">Muestra esto en recepción para entrar.</div>
            </div>
        </div>
    </div>

  </div>
</div>

<?php include 'footer.php'; ?>
