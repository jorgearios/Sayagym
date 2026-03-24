<?php
// Incluimos nuestro archivo de configuración para poder interactuar con la base de datos
include 'config.php';

// Incluimos la parte superior de la página (menú, diseño base, etc.)
include 'header.php';
?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- Encabezado de la página -->
    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Gestión de Socios</h2>
        <p class="page-subtitle">Administra el padrón de miembros activos e inactivos del gimnasio.</p>
      </div>
      <div class="col-auto">
        <!-- Botón que lleva al formulario para inscribir un nuevo socio -->
        <a href="nuevoSocio.php" class="btn btn-red">
          <i class="ti ti-user-plus"></i> Nuevo Socio
        </a>
      </div>
    </div>

    <!-- Mensajes de éxito al editar o eliminar -->
    <!-- isset($_GET['res']) verifica si en la URL hay una variable llamada "res" de "resultado" -->
    <?php if (isset($_GET['res'])) { ?>
      <!-- Usamos un condicional corto (ternario) para elegir el color del mensaje (rojo o verde) -->
      <div class="alert <?php echo $_GET['res'] == 'eliminado' ? 'alert-danger' : 'alert-success'; ?>">
        <?php echo $_GET['res'] == 'eliminado' ? '✓ Socio eliminado correctamente del sistema.' : '✓ Información del socio actualizada correctamente.'; ?>
      </div>
    <?php
}?>

    <!-- Contenedor principal de la tabla -->
    <div class="card">
      <div class="card-header gray">
        <h3 class="card-title">Listado de Socios</h3>
      </div>
      
      <div class="table-responsive">
        <table class="gym-table">
          <!-- Cabecera de la tabla (Nombres de las columnas) -->
          <thead>
            <tr>
              <th>Socio / Contacto</th>
              <th>Plan</th>
              <th>Entrenador</th>
              <th>Vencimiento</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          
          <!-- Cuerpo de la tabla (Donde van los datos de los usuarios) -->
          <tbody>
            <?php
// Preparamos nuestra orden de SQL (consulta a la base de datos)
// JOIN nos permite traer datos de otras tablas usando IDs en común
$sql = "SELECT s.*, COALESCE(m.nombre, 'Sin plan') as plan, e.nombre as nombre_profe 
                    FROM socios s 
                    LEFT JOIN membresias m ON s.id_membresia = m.id_membresia 
                    LEFT JOIN entrenadores e ON s.id_entrenador = e.id_entrenador
                    ORDER BY s.id_socio DESC";

// Mandamos ejecutar la consulta
$res = $conexion->query($sql);

// Recorremos los resultados usando un ciclo "while" que dice:
// "Mientras haya filas en los resultados, asigamos una a la variable $row (fila)"
while ($row = $res->fetch_assoc()) {

  // Guardamos y convertimos las fechas en un formato numérico para poder compararlas fácil
  $vence = strtotime($row['fecha_vencimiento']);
  $hoy = strtotime(date('Y-m-d'));

  // Verificamos si la fecha de vencimiento es más antigua (menor) que hoy
  $vencido = $vence < $hoy;

  // Evaluamos qué estado tiene este socio para pintarle una etiqueta ("badge") diferente
  if ($row['estado'] == 'inactivo') {
    $status_class = 'badge-secondary';
    $status_text = 'INACTIVO';
  }
  else if ($vencido) {
    $status_class = 'badge-red';
    $status_text = 'VENCIDO';
  }
  else {
    $status_class = 'badge-green';
    $status_text = 'ACTIVO';
  }
?>
            
            <!-- Comienza la fila de un socio -->
            <tr>
              
              <!-- Columna 1: Foto y Contactos -->
              <td>
                <div style="display:flex; align-items:center; gap:12px;">
                  
                  <!-- Verificamos si el socio tiene una imagen guardada (no está vacía) -->
                  <?php if (!empty($row['foto'])) { ?>
                    <!-- Mostramos la imagen -->
                    <img src="<?php echo htmlspecialchars($row['foto']); ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                  <?php
  }
  else { ?>
                    <!-- Si no hay foto, generamos un círculo gris con un ícono -->
                    <div style="width:40px;height:40px;border-radius:50%;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:#9CA3AF;"><i class="ti ti-user"></i></div>
                  <?php
  }?>
                  
                  <div>
                    <!-- Imprimimos nombre y apellido concatenados -->
                    <div class="td-name"><?php echo $row['nombre'] . " " . $row['apellido']; ?></div>
                    
                    <!-- Imprimimos el teléfono y el correo (si tiene) -->
                    <div class="td-muted">
                      <?php echo htmlspecialchars($row['telefono']); ?>
                      <?php if ($row['correo']) {
    echo " &middot; " . htmlspecialchars($row['correo']);
  }?>
                    </div>
                  </div>
                  
                </div>
              </td>
              
              <!-- Columna 2: Etiqueta azul con el nombre del Plan/Membresía -->
              <td><span class="badge badge-blue"><?php echo $row['plan']; ?></span></td>
              
              <!-- Columna 3: Nombre del entrenador (o aviso si no tiene asignado) -->
              <td class="td-muted">
                <i class="ti ti-barbell me-1"></i>
                <!-- El símbolo ? se usa como if corto para decir: si hay profe, muéstralo, si no (:) muestra "Sin asignar" -->
                <?php echo $row['nombre_profe'] ? $row['nombre_profe'] : '<span style="color:#9CA3AF">Sin asignar</span>'; ?>
              </td>
              
              <!-- Columna 4: Fecha de Vencimiento -->
              <td>
                <!-- Si venció, la fecha es roja, si no, es verde -->
                <span class="<?php echo $vencido ? 'text-red' : 'text-green'; ?> fw-bold small">
                  <!-- Formateamos la fecha a Día/Mes/Año -->
                  <?php echo date('d/m/Y', $vence); ?>
                </span>
              </td>
              
              <!-- Columna 5: Etiqueta de Activo/Inactivo/Vencido -->
              <td>
                <span class="badge <?php echo $status_class; ?>">
                  <?php echo $status_text; ?>
                </span>
              </td>
              
              <!-- Columna 6: Interfaz de Botones para Editar y Borrar -->
              <td>
                <div class="btn-list">
                  <!-- Botón Editar que manda el "id_socio" por la URL al darle click -->
                  <a href="editarSocio.php?id=<?php echo $row['id_socio']; ?>" class="btn btn-icon edit" title="Editar este registro">
                    <i class="ti ti-edit"></i>
                  </a>
                  
                  <!-- Botón Eliminar que igual manda el ID por la URL, pero antes lanza una ventana emergente ("confirm") de JavaScript -->
                  <a href="eliminarSocio.php?id=<?php echo $row['id_socio']; ?>" 
                     class="btn btn-icon" title="Eliminar registro"
                     onclick="return confirm('¿Estás totalmente seguro de que quieres eliminar a este socio del sistema?');">
                    <i class="ti ti-trash"></i>
                  </a>
                </div>
              </td>
              
            </tr>
            <!-- Se cierra la fila del socio -->
            
            <?php
} // Fin del ciclo while, esto se repetirá por cada socio encontrado
?>
          </tbody>
        </table>
      </div>
      
      <!-- Parte inferior de la tabla -->
      <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted small">Mostrando todos los registros guardados en el sistema</p>
      </div>
      
    </div>
  </div>
</div>

<!-- Incluimos la parte inferior (footer) -->
<?php include 'footer.php'; ?>
