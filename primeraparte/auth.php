<?php
// auth.php
// Este archivo gestiona la autenticación y las sesiones del sistema.
// Debe ser incluido al inicio de cualquier página que requiera protección.

// ── INICIO DE SESIÓN ──
// Verifica si la sesión no ha sido iniciada aún, para evitar errores de "session already started"
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── PROTECCIÓN DE RUTAS ──
// Obtenemos el nombre del archivo actual al que el usuario intenta acceder
$pagina_actual = basename($_SERVER['PHP_SELF']);
// Definimos un arreglo con las páginas que no requieren iniciar sesión (públicas)
$paginas_publicas = ['login.php', 'registro.php', 'setup_db.php'];

// Si el usuario no tiene una sesión activa (no existe 'usuario_id') y la página actual NO es pública,
// lo redirigimos obligatoriamente a la pantalla de inicio de sesión.
if (!isset($_SESSION['usuario_id']) && !in_array($pagina_actual, $paginas_publicas)) {
    header("Location: login.php");
    exit();
}

// ── REDIRECCIÓN DE USUARIOS AUTENTICADOS ──
// Si el usuario ya inició sesión e intenta acceder a 'login.php',
// lo redirigimos a su panel correspondiente (Index para admin, InicioSocio para socios)
if (isset($_SESSION['usuario_id']) && $pagina_actual === 'login.php') {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador') {
        header("Location: index.php"); // Panel de Administración
    } else {
        header("Location: inicioSocio.php"); // Panel exclusivo de Socios
    }
    exit();
}

// ── FUNCIONES DE VERIFICACIÓN DE ROLES ──

/**
 * Verifica si el usuario actual tiene el rol de Administrador.
 * Útil para mostrar u ocultar elementos del menú y proteger opciones sensibles.
 * @return bool True si es Administrador, False de lo contrario.
 */
function esAdministrador()
{
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador';
}

/**
 * Verifica si el usuario actual tiene el rol de Socio.
 * @return bool True si es Socio, False de lo contrario.
 */
function esSocio()
{
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Socio';
}
?>