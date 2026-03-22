<?php
include 'config.php';

// 1. Añadir columna password a socios si no existe
$check_col = $conexion->query("SHOW COLUMNS FROM socios LIKE 'password'");
if ($check_col->num_rows == 0) {
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $conexion->query("ALTER TABLE socios ADD COLUMN password VARCHAR(255) DEFAULT '$hash' AFTER correo");
    echo "Columna password añadida a socios (contraseña default: 123456)<br>";
}
else {
    echo "Columna password ya existe en socios<br>";
}

// 2. Crear admin si no existe (busca por rol = 'Administrador')
$check_admin = $conexion->query("SELECT * FROM usuarios WHERE rol = 'Administrador' LIMIT 1");
if ($check_admin->num_rows == 0) {
    $hash_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $result = $conexion->query("INSERT INTO usuarios (nombre_completo, usuario, password, rol, estado) VALUES ('Admin Principal', 'admin', '$hash_admin', 'Administrador', 'activo')");
    if ($result) {
        echo "Administrador creado — Usuario: <strong>admin</strong> / Contraseña: <strong>admin123</strong><br>";
    }
    else {
        echo "Error al crear admin: " . $conexion->error . "<br>";
    }
}
else {
    echo "Ya existe un Administrador<br>";
}

echo "<br><strong>Listo.</strong> Ahora puedes ir al <a href='login.php'>Login</a>.";
?>
