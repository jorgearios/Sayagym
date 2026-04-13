<?php
/**
 * Archivo: entrenadores.php
 * Descripción: Listado principal y administración general de entrenadores.
 * Parte del sistema integral de gestión Sayagym.
 */


// 1. Incluimos la conexión a la base de datos
include 'config.php';
// 2. Incluimos el diseño principal de la página (menú, colores, etc.)
include 'header.php';

?>

<div class="page-wrapper">
  <div class="container-xl mt-4">

    <!-- Encabezado de la página -->
    <div class="row align-items-center mb-4">
      <div class="col">
        <h2 class="page-title">Equipo de Entrenadores</h2>
        <p class="page-subtitle">Gestión de personal y comisiones.</p>
      </div>
      <div class="col-auto">
        <!-- Botón para ir al formulario de registrar un nuevo entrenador -->
        <a href="nuevoEntrenador.php" class="btn btn-red">
          <i class="ti ti-plus"></i> Registrar Entrenador
        </a>
      </div>
    </div>

    <!-- Módulo de alertas (mensajes verdes o rojos) -->
    <!-- Revisa si la dirección web nos mandó la palabra "res" para mostrar un mensaje de éxito -->
    <?php if (isset($_GET['res'])) { ?>
      <!-- Usamos una condición corta para elegir el color del mensaje (rojo si fue 'eliminado', verde si fue 'editado') -->
      <div class="alert <?php echo $_GET['res'] == 'eliminado' ? 'alert-danger' : 'alert-success'; ?>">
        <?php echo $_GET['res'] == 'eliminado' ? '✓ Entrenador eliminado exitosamente del sistema.' : '✓ Entrenador actualizado correctamente en la base.'; ?>
      </div>
      <?php
    } ?>

    <!-- Tarjeta central con la tabla -->
    <div class="card">
      <div class="card-header gray">
        <h3 class="card-title">Listado de Entrenadores</h3>
      </div>

      <div class="table-responsive">
        <table class="gym-table">
          <!-- Cabeceras (Títulos de lo que significan las columnas) -->
          <thead>
            <tr>
              <th>Entrenador</th>
              <th>Contacto</th>
              <th>Turno / Especialidad</th>
              <th>Comisión</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <!-- Cuerpo de la tabla (Se rellena desde la base de datos) -->
          <tbody>
            <?php
            // Consultamos todos los entrenadores guardados, ordenados del más reciente al más viejo (DESC = Descendente)
            $res = $conexion->query("SELECT * FROM entrenadores ORDER BY id_entrenador DESC");

            // Usamos un ciclo while que repetirá todas estas filas html por cada entrenador que exista
            while ($e = $res->fetch_assoc()) {
              ?>
              <tr>
                <!-- Columna 1: Nombre Simple -->
                <td class="td-name"><?php echo $e['nombre']; ?></td>

                <!-- Columna 2: Datos de Contacto (Tel y Correo) -->
                <td>
                  <div><?php echo $e['telefono']; ?></div>
                  <div class="td-muted"><?php echo $e['correo']; ?></div>
                </td>

                <!-- Columna 3: Especialidades y Turno -->
                <td>
                  <div class="small"><?php echo $e['especialidad']; ?></div>
                  <!-- Imprimimos una pequeña etiqueta morada con su turno laboral -->
                  <span class="badge badge-purple"><?php echo $e['turno']; ?></span>
                </td>

                <!-- Columna 4: Comisión por alumno ($) -->
                <td>
                  <span class="fw-bold text-green font-oswald" style="font-size:1.05rem;">
                    <!-- La función number_format() le da un formato de precio o moneda (".00") bonito  -->
                    $<?php echo number_format($e['tarifa_comision'], 2); ?>
                  </span>
                </td>

                <!-- Columna 5: Estado Laboral (Activo/Inactivo) -->
                <td>
                  <span class="badge <?php echo $e['estado'] == 'activo' ? 'badge-green' : 'badge-gray'; ?>">
                    <!-- strtoupper() hace que cualquier palabra se escriba en MÁYUSCULAS ENORMES -->
                    <?php echo strtoupper($e['estado']); ?>
                  </span>
                </td>

                <!-- Columna 6: Interacciones y Botones -->
                <td>
                  <div class="btn-list">
                    <!-- Botón Editar que redirige a 'editarEntrenador.php' indicando el ID del sujeto de forma oculta (?id=X) -->
                    <a href="editarEntrenador.php?id=<?php echo $e['id_entrenador']; ?>" class="btn btn-icon edit"
                      title="Editar este registro">
                      <i class="ti ti-edit"></i>
                    </a>

                    <!-- Botón Eliminar que lanza una ventanita preguntóna javascript de precaución ("confirm") antes de continuar -->
                    <a href="eliminarEntrenador.php?id=<?php echo $e['id_entrenador']; ?>" class="btn btn-icon"
                      title="Eliminar registro"
                      onclick="return confirm('¿Estás totalmente seguro de intentar eliminar a este entrenador definitivo?');">
                      <i class="ti ti-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php
            } // Fin de nuestro gran ciclo while 
            ?>
          </tbody>
        </table>
      </div>

      <!-- Pie o rodapié inferior de nuestra tarjeta interactiva -->
      <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted small">Mostrando todos los registros del equipo del Gimnasio</p>
      </div>

    </div>
  </div>
</div>

<!-- Incorporamos hasta el fondo el diseño de cierre o pie de toda página -->
<?php include 'footer.php'; ?>