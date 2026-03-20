<?php
// auth.php - Control de acceso y sesiones
// Iniciamos la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtenemos el nombre del archivo en el que estamos actualmente
$pagina_actual = basename($_SERVER['PHP_SELF']);

// Páginas públicas que no requieren inicio de sesión
$paginas_publicas = ['login.php', 'registro.php', 'setup_db.php'];

// Si el usuario NO ha iniciado sesión y NO está en una de las páginas públicas...
if (!isset($_SESSION['usuario_id']) && !in_array($pagina_actual, $paginas_publicas)) {
    // Lo redirigimos a login.php
    header("Location: /Sayagym/primeraparte/login.php");
    exit();
}

// Si ya inició sesión pero intenta acceder al login otra vez...
if (isset($_SESSION['usuario_id']) && $pagina_actual === 'login.php') {
    // Lo mandamos al inicio (dashboard)
    header("Location: /Sayagym/primeraparte/index.php");
    exit();
}

// Función sencilla para verificar si quien está viendo es Administrador
function esAdministrador() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador';
}

// Función para verificar si quien está viendo es Socio
function esSocio() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Socio';
}
?>
