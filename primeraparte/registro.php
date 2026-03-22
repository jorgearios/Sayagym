<?php
// registro.php - Registro de Nuevos Usuarios/Socios
session_start();
include 'config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Verificar si la columna password existe, si no, crearla
    $check_col = $conexion->query("SHOW COLUMNS FROM socios LIKE 'password'");
    if ($check_col->num_rows == 0) {
        $hash_default = password_hash('123456', PASSWORD_DEFAULT);
        $conexion->query("ALTER TABLE socios ADD COLUMN password VARCHAR(255) DEFAULT '$hash_default' AFTER correo");
    }

    // Validar si el correo ya existe
    $stmt_val = $conexion->prepare("SELECT id_socio FROM socios WHERE correo = ?");
    if (!$stmt_val) {
        $mensaje = "<div class='alert'>Error interno: " . $conexion->error . "</div>";
    } else {
        $stmt_val->bind_param("s", $correo);
        $stmt_val->execute();
        if ($stmt_val->get_result()->num_rows > 0) {
            $mensaje = "<div class='alert'>El correo ya está registrado. Intenta <a href='login.php' style='color:inherit;text-decoration:underline;'>iniciar sesión</a>.</div>";
        } else {
            $fecha_registro = date('Y-m-d');
            $stmt_ins = $conexion->prepare("INSERT INTO socios (nombre, apellido, correo, telefono, password, estado, fecha_registro, fecha_vencimiento) VALUES (?, ?, ?, ?, ?, 'activo', ?, ?)");
            if (!$stmt_ins) {
                $mensaje = "<div class='alert'>Error al preparar registro: " . $conexion->error . "</div>";
            } else {
                $stmt_ins->bind_param("sssssss", $nombre, $apellido, $correo, $telefono, $password, $fecha_registro, $fecha_registro);
                if ($stmt_ins->execute()) {
                    $mensaje = "<div class='alert' style='background:var(--green-lt); color:var(--green); border-left-color:var(--green);'>¡Cuenta creada! Ahora puedes <a href='login.php' style='color:inherit; text-decoration:underline;'>iniciar sesión</a>.</div>";
                } else {
                    $mensaje = "<div class='alert'>Error: " . $conexion->error . "</div>";
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sayagym | Crear Cuenta</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --red: #B71C1C;
            --red-dark: #7F0000;
            --bg: #F0F0F0;
            --card: #FFFFFF;
            --text: #1A1A1A;
            --muted: #6B7280;
            --border: #E5E7EB;
            --radius: 8px;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --green: #15803D;
            --green-lt: #DCFCE7;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding:20px; }
        .login-container { width: 100%; max-width: 500px; }
        .login-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; border: 1px solid var(--border); }
        .login-header { background: linear-gradient(135deg, var(--red-dark) 0%, var(--red) 100%); padding: 30px 20px; text-align: center; color: white; }
        .login-title { font-family: 'Oswald', sans-serif; font-size: 1.4rem; letter-spacing: 0.5px; margin: 0; }
        .login-body { padding: 30px; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem; font-family: 'DM Sans', sans-serif; margin-bottom: 16px; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: var(--red); }
        .btn-login { background: var(--red); color: white; width: 100%; padding: 12px; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; margin-top:10px; }
        .btn-login:hover { background: var(--red-dark); }
        .alert { background: #FEE2E2; color: #DC2626; padding: 12px; border-radius: 6px; border-left: 4px solid #DC2626; font-size: 0.85rem; margin-bottom: 20px; font-weight: 500; }
        .row { display: flex; gap: 12px; }
        .col { flex: 1; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1 class="login-title">Crear Cuenta - Sayagym</h1>
            <div style="font-size:0.9rem; margin-top:6px; opacity:0.9;">¡Únete a nuestra familia y ponte en forma!</div>
        </div>
        <div class="login-body">
            
            <?php echo $mensaje; ?>

            <form action="registro.php" method="POST">
                <div class="row">
                    <div class="col">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="col">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required>
                    </div>
                </div>
                
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" placeholder="usuario@email.com" required>

                <label class="form-label">Teléfono (Celular)</label>
                <input type="text" name="telefono" class="form-control" placeholder="10 dígitos">
                
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Crea una contraseña segura" required>
                
                <button type="submit" class="btn-login"><i class="ti ti-user-plus"></i> Registrarme Ahora</button>

                <div style="text-align: center; margin-top: 20px; font-size: 0.9rem;">
                    <a href="login.php" style="color: var(--muted); text-decoration: none; font-weight: 500;"><i class="ti ti-arrow-left"></i> Volver a Iniciar Sesión</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
