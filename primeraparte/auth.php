<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);
$paginas_publicas = ['login.php', 'registro.php', 'setup_db.php'];

if (!isset($_SESSION['usuario_id']) && !in_array($pagina_actual, $paginas_publicas)) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['usuario_id']) && $pagina_actual === 'login.php') {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador') {
        header("Location: index.php");
    } else {
        header("Location: inicioSocio.php");
    }
    exit();
}

function esAdministrador() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador';
}

function esSocio() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Socio';
}
?>
