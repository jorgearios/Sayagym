<?php
/**
 * Archivo: login.php
 * Descripción: Pantalla principal de inicio de sesión de la plataforma.
 * Parte del sistema integral de gestión Sayagym.
 */

// login.php — Adaptado con lógica del proyecto de referencia
// Correcciones: backticks en `password`, sin session_start() doble,
// soporte para usuario O correo en admin, compatibilidad bcrypt + texto plano
include 'config.php';  // config.php ya llama a auth.php y session_start()

$error = "";

$error = "";

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] === 'Administrador') {
        header("Location: index.php");
    } elseif ($_SESSION['rol'] === 'Entrenador') {
        header("Location: inicioEntrenador.php");
    } else {
        header("Location: inicioSocio.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim($_POST['identificador'] ?? '');
    $password_raw  = $_POST['password'] ?? '';

    if (empty($identificador) || empty($password_raw)) {
        $error = "Por favor, completa todos los campos.";
    } else {

        // ── 1. Buscar en tabla de usuarios (Administradores / Empleados) ─────
        // Preparamos la consulta para evitar inyecciones SQL
        $stmt = $conexion->prepare(
            "SELECT id_usuario, nombre_completo, `password`, rol
             FROM `usuarios`
             WHERE usuario = ? AND estado = 'activo'
             LIMIT 1"
        );
        $row_admin = null;
        if ($stmt) {
            $stmt->bind_param("s", $identificador);
            $stmt->execute();
            $row_admin = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        if ($row_admin) {
            // Soporte para contraseña en texto plano (compatibilidad vieja) O hash con bcrypt
            $hash = $row_admin['password'];
            // Verificamos matemáticamente la contraseña
            $ok   = ($password_raw === $hash) || password_verify($password_raw, $hash);

            if ($ok) {
                // Generar variables de sesión
                $_SESSION['usuario_id'] = $row_admin['id_usuario'];
                $_SESSION['nombre']     = $row_admin['nombre_completo'];
                $_SESSION['rol']        = $row_admin['rol'];
                
                if ($_SESSION['rol'] === 'Entrenador') {
                    header("Location: inicioEntrenador.php");
                } else {
                    header("Location: index.php"); // Administrador / Recepcionista
                }
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }

        } else {
            // ── 2. Buscar en tabla de socios (Clientes del Gimnasio) ──────────────────────────
            // Si no era admin, verificamos si es un socio usando su correo
            $stmt2 = $conexion->prepare(
                "SELECT id_socio, nombre, apellido, `password`
                 FROM `socios`
                 WHERE correo = ? AND estado != 'inactivo'
                 LIMIT 1"
            );
            $row_socio = null;
            if ($stmt2) {
                $stmt2->bind_param("s", $identificador);
                $stmt2->execute();
                $row_socio = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
            }

            if ($row_socio) {
                $hash = $row_socio['password'];
                $ok   = ($password_raw === $hash) || password_verify($password_raw, $hash);

                if ($ok) {
                    $_SESSION['usuario_id'] = $row_socio['id_socio'];
                    $_SESSION['nombre']     = $row_socio['nombre'] . ' ' . $row_socio['apellido'];
                    $_SESSION['rol']        = 'Socio';
                    header("Location: inicioSocio.php");
                    exit();
                } else {
                    $error = "Contraseña incorrecta.";
                }
            } else {
                $error = "Usuario o correo no encontrado, o cuenta inactiva.";
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
    <title>Sayagym | Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --red:#B71C1C; --red-dark:#7F0000; --bg:#F0F0F0; --card:#FFFFFF; --text:#1A1A1A; --muted:#6B7280; --border:#E5E7EB; --radius:8px; --shadow:0 4px 20px rgba(0,0,0,0.08); }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-container { width:100%; max-width:420px; padding:20px; }
        .login-card { background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; border:1px solid var(--border); }
        .login-header { background:linear-gradient(135deg,var(--red-dark) 0%,var(--red) 100%); padding:30px 20px; text-align:center; color:white; }
        .login-header img { max-height:60px; background:white; padding:6px 12px; border-radius:6px; margin-bottom:12px; box-shadow:0 2px 10px rgba(0,0,0,.2); }
        .login-title { font-family:'Oswald',sans-serif; font-size:1.4rem; letter-spacing:.5px; margin:0; }
        .login-body { padding:30px; }
        .form-label { display:block; font-size:.85rem; font-weight:600; color:#374151; margin-bottom:8px; }
        .form-control { width:100%; padding:12px 16px; border:1.5px solid var(--border); border-radius:6px; font-size:.95rem; font-family:'DM Sans',sans-serif; margin-bottom:20px; outline:none; transition:border-color .2s; }
        .form-control:focus { border-color:var(--red); }
        .btn-login { background:var(--red); color:white; width:100%; padding:12px; border:none; border-radius:6px; font-size:1rem; font-weight:600; font-family:'DM Sans',sans-serif; cursor:pointer; transition:background .2s; display:flex; justify-content:center; align-items:center; gap:8px; }
        .btn-login:hover { background:var(--red-dark); }
        .alert { background:#FEE2E2; color:#DC2626; padding:12px; border-radius:6px; border-left:4px solid #DC2626; font-size:.85rem; margin-bottom:20px; font-weight:500; }
        .login-footer { text-align:center; padding:20px; background:#FAFAFA; border-top:1px solid var(--border); font-size:.8rem; color:var(--muted); }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <img src="../Sayagym%20logo.png" alt="Sayagym Logo" onerror="this.style.display='none'">
            <h1 class="login-title">Bienvenido a Sayagym</h1>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert"><i class="ti ti-alert-circle me-1"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <label class="form-label">Usuario o Correo Electrónico</label>
                <input type="text" name="identificador" class="form-control"
                       placeholder="Ej. admin o correo@mail.com" required autocomplete="username">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required autocomplete="current-password">
                <button type="submit" class="btn-login">
                    <i class="ti ti-login"></i> Entrar al Sistema
                </button>
                <div style="text-align:center;margin-top:20px;font-size:.9rem;">
                    <a href="registro.php" style="color:var(--red);text-decoration:none;font-weight:600;">
                        ¿No tienes cuenta? Regístrate aquí
                    </a>
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
