<?php
/**
 * Archivo: logout.php
 * Descripción: Script para destruir la sesión y cerrar el acceso seguro.
 * Parte del sistema integral de gestión Sayagym.
 */

session_start();
session_destroy();
header("Location: login.php");
exit();
?>