<?php
// nuevoPago.php - Formulario para cobrar y procesar pagos
include 'config.php';
include 'header.php';

if (!esAdministrador()) {
    echo "<div class='container-xl mt-4'><div class='alert alert-danger'>Acceso denegado.</div></div>";
    include 'footer.php';
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_socio = $_POST['id_socio'];
    $id_membresia = $_POST['id_membresia'];
    $metodo_pago = $_POST['metodo_pago'];
    $referencia = $_POST['referencia'];
    
    // Obtenemos los detalles de la membresía para saber cuánto cuesta y cuánto dura
    $stmt_mem = $conexion->prepare("SELECT precio, duracion_meses FROM membresias WHERE id_membresia = ?");
    $stmt_mem->bind_param("i", $id_membresia);
    $stmt_mem->execute();
    $membresia = $stmt_mem->get_result()->fetch_assoc();
    
    if ($membresia) {
        $monto = $membresia['precio'];
        $meses = $membresia['duracion_meses'];
        
        // 1. Guardamos el pago en la base de datos
        $stmt_pago = $conexion->prepare("INSERT INTO pagos (id_socio, id_membresia, monto, metodo_pago, referencia, estado) VALUES (?, ?, ?, ?, ?, 'pagado')");
        $stmt_pago->bind_param("iidss", $id_socio, $id_membresia, $monto, $metodo_pago, $referencia);
        
        if ($stmt_pago->execute()) {
            // 2. Actualizamos la vigencia del socio
            // Primero, vemos si actualmente ya tiene fecha de vencimiento y si aún le sirve
            $stmt_socio = $conexion->prepare("SELECT fecha_vencimiento FROM socios WHERE id_socio = ?");
            $stmt_socio->bind_param("i", $id_socio);
            $stmt_socio->execute();
            $datos_socio = $stmt_socio->get_result()->fetch_assoc();
            
            $hoy = date('Y-m-d');
            $fecha_base = $hoy; // Por defecto es a partir de hoy
            if ($datos_socio['fecha_vencimiento'] > $hoy) {
                // Si aún tiene días a favor, le sumamos a esa fecha
                $fecha_base = $datos_socio['fecha_vencimiento'];
            }
            
            // Calculamos la nueva fecha sumándole los meses de la membresía contratada
            $nueva_fecha = date('Y-m-d', strtotime("+$meses months", strtotime($fecha_base)));
            
            // Hacemos el UPDATE en la tabla de socios
            $stmt_update = $conexion->prepare("UPDATE socios SET id_membresia = ?, fecha_vencimiento = ?, estado = 'activo' WHERE id_socio = ?");
            $stmt_update->bind_param("isi", $id_membresia, $nueva_fecha, $id_socio);
            $stmt_update->execute();
            
            $mensaje = "<div class='alert alert-success'>Pago registrado y membresía actualizada correctamente. ¡Renovado hasta el ".date('d/m/Y', strtotime($nueva_fecha))."!</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar el pago: " . $conexion->error . "</div>";
        }
    }
}

// Consultamos los socios y membresías para llenar las opciones del formulario
$socios = $conexion->query("SELECT id_socio, nombre, apellido FROM socios ORDER BY nombre ASC");
$membresias = $conexion->query("SELECT id_membresia, nombre, precio FROM membresias WHERE estado = 'activo'");
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">
    
    <div class="row mb-4">
      <div class="col">
        <h1 class="page-title">Registrar Nuevo Pago</h1>
        <p class="page-subtitle">Procesa el pago y renueva automáticamente a un socio.</p>
      </div>
      <div class="col-auto">
        <a href="pagos.php" class="btn btn-outline"><i class="ti ti-arrow-left"></i> Volver a Caja</a>
      </div>
    </div>

    <div class="card" style="max-width: 600px;">
      <div class="card-header gray">
        <span class="card-title">Detalles de Facturación</span>
      </div>
      <div class="card-body">
        
        <?php echo $mensaje; ?>

        <form action="nuevoPago.php" method="POST">
            
            <div class="mb-3">
                <label class="form-label">Tercero / Socio</label>
                <select name="id_socio" class="form-select" required>
                    <option value="">-- Selecciona el Socio --</option>
                    <?php while ($s = $socios->fetch_assoc()): ?>
                    <option value="<?php echo $s['id_socio']; ?>"><?php echo $s['nombre'] . ' ' . $s['apellido']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Membresía a Contratar</label>
                <select name="id_membresia" class="form-select" required>
                    <option value="">-- Elige el plan --</option>
                    <?php while ($m = $membresias->fetch_assoc()): ?>
                    <option value="<?php echo $m['id_membresia']; ?>"><?php echo $m['nombre']; ?> ($<?php echo number_format($m['precio'], 2); ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-select" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta de Crédito / Débito</option>
                    <option value="Transferencia">Transferencia Bancaria</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Referencia (Opcional)</label>
                <input type="text" name="referencia" class="form-control" placeholder="Ej. Número de ticket o terminación de tarjeta">
            </div>

            <button type="submit" class="btn btn-green w-100" style="background:var(--green); color:white; justify-content:center;">
                <i class="ti ti-check"></i> Procesar Pago y Renovar
            </button>

        </form>
      </div>
    </div>

  </div>
</div>

<?php include 'footer.php'; ?>
