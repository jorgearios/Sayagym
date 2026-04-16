<?php
/**
 * Archivo: inicioEntrenador.php
 * Descripción: Panel de inicio personalizado para los entrenadores del gimnasio.
 * Parte del sistema integral de gestión Sayagym.
 */

// inicioEntrenador.php - Interfaz o Dashboard principal para el Entrenador
include 'config.php';
include 'header.php';

// Validar que efectivamente es un Entrenador (o Admin, for flexibility)
if (!esEntrenador() && !esAdministrador()) {
    echo "<div class='container-xl mt-4'><div class='alert alert-danger'>Acceso exclusivo para entrenadores.</div></div>";
    include 'footer.php';
    exit();
}

$nombre_entrenador = $_SESSION['nombre'];
?>

<style>
/* Mimic styles from index/inicioSocio if they aren't globally loaded */
.dash-hero {
    padding: 30px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow);
}
.hero-title {
    font-family: 'Oswald', sans-serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
    margin-bottom: 6px;
}
.hero-sub {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.9);
    margin-bottom: 12px;
}
.hero-date {
    font-size: 0.9rem;
    font-weight: 500;
}
</style>

<div class="page-wrapper">
    <div class="container-xl mt-4">

        <!-- Hero del Entrenador -->
        <div class="dash-hero" style="background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 100%);">
            <div style="z-index:1;">
                <div class="hero-title">Panel de Entrenamiento</div>
                <div class="hero-sub">Hola, <?php echo htmlspecialchars($nombre_entrenador); ?>. Bienvenido a tu espacio de trabajo.</div>
                <div class="hero-date" style="color:#FFF;"><i class="ti ti-calendar me-1"></i><?php echo date('d \d\e F \d\e Y'); ?></div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Accesos Rapidos -->
            <div class="col-md-4 mb-4">
                <a href="socios.php" class="kpi-card" style="text-decoration:none;">
                    <div class="kpi-icon blue"><i class="ti ti-users"></i></div>
                    <div>
                        <div class="kpi-num" style="font-size: 1.5rem;">Mis Socios</div>
                        <div class="kpi-label">Gestiona los socios que entrenas</div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 mb-4">
                <a href="rutinas.php" class="kpi-card" style="text-decoration:none;">
                    <div class="kpi-icon green"><i class="ti ti-clipboard-list"></i></div>
                    <div>
                        <div class="kpi-num" style="font-size: 1.5rem;">Rutinas</div>
                        <div class="kpi-label">Asigna y crea planes de entrenamiento</div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 mb-4">
                <a href="evaluaciones.php" class="kpi-card" style="text-decoration:none;">
                    <div class="kpi-icon red"><i class="ti ti-report-medical"></i></div>
                    <div>
                        <div class="kpi-num" style="font-size: 1.5rem;">Evaluaciones</div>
                        <div class="kpi-label">Registra el progreso físico</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card h-100">
                    <div class="card-header gray">
                        <span class="card-title">Instrucciones Rápidas</span>
                    </div>
                    <div class="card-body">
                        <p>Desde este panel puedes navegar a las utilidades principales para realizar tu trabajo en Sayagym. Utiliza el menú superior para acceder rápidamente a:</p>
                        <ul style="margin-top:10px; padding-left:20px; color:var(--text); line-height:1.6;">
                            <li><strong>Mis Socios:</strong> Busca el perfil de tus alumnos mediante la tabla de socios general.</li>
                            <li><strong>Entrenamiento:</strong> Accede al catálogo de rutinas donde puedes asignar actividades específicas para cada alumno.</li>
                            <li><strong>Evaluaciones:</strong> Rellena y consulta las medidas, peso e IMC para llevar control de los indicadores de salud.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
