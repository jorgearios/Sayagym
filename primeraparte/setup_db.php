<?php
// Script temporal para actualizar la base de datos
include 'config.php';

// Añadir la columna de contraseña a los socios si no existe
$check_col = $conexion->query("SHOW COLUMNS FROM socios LIKE 'password'");
if ($check_col->num_rows == 0) {
    // Generamos un hash simple, por ejemplo '123456'
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $conexion->query("ALTER TABLE socios ADD COLUMN password VARCHAR(255) DEFAULT '$hash' AFTER correo");
    echo "Columna password añadida correctamente a los socios (Contraseña default: 123456).<br>";
}
else {
    echo "La columna password ya existe en la tabla socios.<br>";
}

// Comprobar si existe un admin
$check_admin = $conexion->query("SELECT * FROM usuarios WHERE rol = 'Admin'");
if ($check_admin->num_rows == 0) {
    $hash_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $conexion->query("INSERT INTO usuarios (nombre_completo, usuario, password, rol, estado) VALUES ('Admin Principal', 'admin', '$hash_admin', 'Administrador', 'activo')");
    echo "Administrador creado correctamente (Usuario: admin / Clave: admin123).<br>";
}
else {
    echo "Ya existe al menos un Administrador en la tabla usuarios.<br>";
}

echo "Configuración inicial completada.";
?>
