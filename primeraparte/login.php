<?php
// login.php - Página de inicio de sesión auto-detectiva
session_start();
include 'config.php';

$error = "";

// Si el usuario ya está conectado, ver qué dashboard le toca
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] === 'Administrador') {
        header("Location: index.php");
    } else {
        header("Location: inicioSocio.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identificador = $_POST['identificador'];
    $password = $_POST['password'];

    // 1. Intentar como Administrador (En la tabla de usuarios)
    $stmt_admin = $conexion->prepare("SELECT id_usuario, nombre_completo, password, rol FROM usuarios WHERE usuario = ? AND estado = 'activo'");
    $stmt_admin->bind_param("s", $identificador);
    $stmt_admin->execute();
    $res_admin = $stmt_admin->get_result();
    
    if ($row = $res_admin->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['usuario_id'] = $row['id_usuario'];
            $_SESSION['nombre'] = $row['nombre_completo'];
            $_SESSION['rol'] = 'Administrador';
            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        // 2. Si no es Admin, intentarlo como Socio (por correo)
        $stmt_socio = $conexion->prepare("SELECT id_socio, nombre, apellido, password FROM socios WHERE correo = ? AND estado != 'inactivo'");
        $stmt_socio->bind_param("s", $identificador);
        $stmt_socio->execute();
        $res_socio = $stmt_socio->get_result();
        
        if ($row_socio = $res_socio->fetch_assoc()) {
            if (password_verify($password, $row_socio['password'])) {
                $_SESSION['usuario_id'] = $row_socio['id_socio'];
                $_SESSION['nombre'] = $row_socio['nombre'] . ' ' . $row_socio['apellido'];
                $_SESSION['rol'] = 'Socio';
                header("Location: inicioSocio.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            // Si el correo no existe en socios tampoco...
            $error = "Usuario o correo no encontrado, o cuenta inactiva.";
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sayagym | Iniciar Sesión</title>
    
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
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { width: 100%; max-width: 420px; padding: 20px; }
        .login-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; border: 1px solid var(--border); }
        .login-header { background: linear-gradient(135deg, var(--red-dark) 0%, var(--red) 100%); padding: 30px 20px; text-align: center; color: white; }
        .login-header img { max-height: 60px; background: white; padding: 6px 12px; border-radius: 6px; margin-bottom: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .login-title { font-family: 'Oswald', sans-serif; font-size: 1.4rem; letter-spacing: 0.5px; margin: 0; }
        .login-body { padding: 30px; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem; font-family: 'DM Sans', sans-serif; margin-bottom: 20px; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: var(--red); }
        .btn-login { background: var(--red); color: white; width: 100%; padding: 12px; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-login:hover { background: var(--red-dark); }
        .alert { background: #FEE2E2; color: #DC2626; padding: 12px; border-radius: 6px; border-left: 4px solid #DC2626; font-size: 0.85rem; margin-bottom: 20px; font-weight: 500; }
        .login-footer { text-align: center; padding: 20px; background: #FAFAFA; border-top: 1px solid var(--border); font-size: 0.8rem; color: var(--muted); }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <img src="../Sayagym%20logo.png" alt="Sayagym Logo">
            <h1 class="login-title">Bienvenido a Sayagym</h1>
        </div>
        <div class="login-body">
            
            <?php if ($error !== "") { ?>
                <div class="alert"><i class="ti ti-alert-circle me-1"></i> <?php echo $error; ?></div>
            <?php } ?>

            <form action="login.php" method="POST">
                
                <label class="form-label">Usuario o Correo Eléctronico</label>
                <input type="text" name="identificador" class="form-control" placeholder="Ej. correo@mail.com o admin" required>
                
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                
                <button type="submit" class="btn-login"><i class="ti ti-login"></i> Entrar al Sistema</button>

                <div style="text-align: center; margin-top: 20px; font-size: 0.9rem;">
                    <a href="registro.php" style="color: var(--red); text-decoration: none; font-weight: 600;">¿No tienes una cuenta? Regístrate aquí</a>
                </div>

            </form>
        </div>
        <div class="login-footer">
            Sistema de Gestión Integral &copy; <?php echo date('Y'); ?>
        </div>
    </div>
</div>

</body>
</html>
