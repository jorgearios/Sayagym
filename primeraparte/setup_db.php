<?php
// setup_db.php — Diagnóstico y configuración inicial
// ELIMINAR este archivo cuando el sistema esté en producción
$conexion = new mysqli('localhost', 'root', '', 'sistema_gym');
if ($conexion->connect_error) {
    die("<b style='color:red'>Error de conexión: " . $conexion->connect_error . "</b><br>Verifica que MySQL esté corriendo y que la BD 'sistema_gym' exista.");
}
$conexion->set_charset("utf8mb4");

$log = [];
$errores = [];

// ── 1. Tabla usuarios ──────────────────────────────────────
$tabla_ok = $conexion->query("SHOW TABLES LIKE 'usuarios'")->num_rows > 0;
if (!$tabla_ok) {
    $errores[] = "La tabla <b>usuarios</b> NO existe. Importa el script SQL primero.";
} else {
    $log[] = "✓ Tabla <b>usuarios</b> existe.";
    $admins = $conexion->query("SELECT id_usuario, usuario, nombre_completo, rol, estado FROM usuarios WHERE rol='Administrador'");
    if ($admins->num_rows === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $ok = $conexion->query("INSERT INTO usuarios (nombre_completo, usuario, password, rol, estado) VALUES ('Admin Principal', 'admin', '$hash', 'Administrador', 'activo')");
        if ($ok) {
            $log[] = "✅ Administrador creado: <b>usuario=admin / contraseña=admin123</b>";
        } else {
            $errores[] = "Error al crear admin: " . $conexion->error;
        }
    } else {
        $log[] = "✓ Administrador(es) encontrado(s):";
        while ($a = $admins->fetch_assoc()) {
            $log[] = "&nbsp;&nbsp;→ ID:{$a['id_usuario']} | usuario:<b>{$a['usuario']}</b> | nombre:{$a['nombre_completo']} | estado:{$a['estado']}";
        }
    }
}

// ── 2. Reset de contraseña ─────────────────────────────────
if (isset($_POST['reset_pass'])) {
    $nueva = $_POST['nueva_pass'];
    $u_target = $conexion->real_escape_string($_POST['usuario_admin']);
    if (strlen($nueva) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $hash_nuevo = password_hash($nueva, PASSWORD_DEFAULT);
        $r = $conexion->query("UPDATE usuarios SET password='$hash_nuevo', estado='activo' WHERE usuario='$u_target' AND rol='Administrador'");
        if ($r && $conexion->affected_rows > 0) {
            $log[] = "✅ Contraseña actualizada para <b>$u_target</b>.";
        } elseif ($conexion->affected_rows === 0) {
            $errores[] = "No se encontró usuario <b>$u_target</b> con rol Administrador.";
        } else {
            $errores[] = "Error: " . $conexion->error;
        }
    }
}

// ── 3. Columna password en socios ─────────────────────────
if ($conexion->query("SHOW TABLES LIKE 'socios'")->num_rows > 0) {
    $col_pass = $conexion->query("SHOW COLUMNS FROM socios LIKE 'password'")->num_rows > 0;
    if (!$col_pass) {
        $conexion->query("ALTER TABLE socios ADD COLUMN password VARCHAR(255) DEFAULT NULL AFTER correo");
        $log[] = "✅ Columna <b>password</b> agregada a socios.";
    } else {
        $log[] = "✓ Columna <b>password</b> en socios: OK.";
    }
}

// ── 4. Tablas módulos nuevos ───────────────────────────────
foreach (['evaluaciones_fisicas', 'alimentos_calorias', 'socio_calorias_limite', 'socio_consumo_calorico', 'consumo_detalle'] as $t) {
    $existe = $conexion->query("SHOW TABLES LIKE '$t'")->num_rows > 0;
    if ($existe) {
        $count = $conexion->query("SELECT COUNT(*) as n FROM $t")->fetch_assoc()['n'];
        $log[] = "✓ Tabla <b>$t</b> — $count registro(s).";
    } else {
        $errores[] = "⚠ Tabla <b>$t</b> no existe — importa <code>sistema_gym_v2.sql</code>.";
    }
}

// ── 5. Test de credenciales ────────────────────────────────
if (isset($_POST['test_login'])) {
    $u = $conexion->real_escape_string($_POST['test_usuario']);
    $p = $_POST['test_pass'];
    $row = $conexion->query("SELECT password, rol FROM usuarios WHERE usuario='$u' LIMIT 1")->fetch_assoc();
    if (!$row) {
        $errores[] = "❌ Usuario <b>$u</b> no encontrado en tabla <b>usuarios</b>.";
    } elseif (password_verify($p, $row['password'])) {
        $log[] = "✅ TEST OK: usuario <b>$u</b> (rol: {$row['rol']}) — contraseña correcta. Puedes iniciar sesión.";
    } else {
        $errores[] = "❌ TEST FALLIDO: contraseña incorrecta para <b>$u</b>. Usa el formulario de reset.";
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sayagym — Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600&family=Oswald:wght@600&display=swap"
        rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #F0F0F0;
            color: #1A1A1A;
            padding: 30px 20px
        }

        .wrap {
            max-width: 680px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px
        }

        h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.6rem;
            color: #7F0000
        }

        .sub {
            font-size: 0.85rem;
            color: #6B7280;
            margin-top: 4px
        }

        .card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #E5E7EB;
            padding: 20px 24px
        }

        .card h2 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        .item {
            padding: 7px 11px;
            border-radius: 5px;
            margin-bottom: 5px;
            font-size: 0.87rem;
            line-height: 1.5
        }

        .ok {
            background: #DCFCE7;
            color: #166534
        }

        .err {
            background: #FEE2E2;
            color: #991B1B
        }

        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            margin-top: 12px;
            text-transform: uppercase;
            letter-spacing: .3px
        }

        input[type=text],
        input[type=password] {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #E5E7EB;
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif
        }

        input:focus {
            border-color: #B71C1C;
            outline: none
        }

        .btn {
            display: block;
            width: 100%;
            margin-top: 12px;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            color: #fff
        }

        .btn-red {
            background: #B71C1C
        }

        .btn-red:hover {
            background: #7F0000
        }

        .btn-blue {
            background: #1565C0
        }

        .btn-blue:hover {
            background: #0D47A1
        }

        .btn-dark {
            background: #1A1A1A;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            padding: 11px 24px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #fff
        }

        .warn {
            background: #FEF3C7;
            border-left: 3px solid #D97706;
            color: #92400E;
            padding: 10px 14px;
            border-radius: 0 6px 6px 0;
            font-size: 0.82rem
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div>
            <h1>Sayagym — Setup & Diagnóstico</h1>
            <p class="sub">Configuración inicial del sistema. Elimina este archivo en producción.</p>
        </div>

        <div class="warn">
            ⚠ <strong>Seguridad:</strong> este archivo no requiere contraseña. Renómbralo o elimínalo cuando el sistema
            esté funcionando.
        </div>

        <div class="card">
            <h2>Estado del sistema</h2>
            <?php foreach ($log as $l): ?>
                <div class="item ok"><?php echo $l; ?></div><?php endforeach; ?>
            <?php foreach ($errores as $e): ?>
                <div class="item err"><?php echo $e; ?></div><?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Resetear contraseña del Administrador</h2>
            <p style="font-size:.83rem;color:#6B7280;margin-bottom:4px;">Úsalo si el login dice "Contraseña incorrecta".
            </p>
            <form method="POST">
                <label>Usuario admin</label>
                <input type="text" name="usuario_admin" value="admin" required>
                <label>Nueva contraseña</label>
                <input type="password" name="nueva_pass" placeholder="Mínimo 6 caracteres" required>
                <button type="submit" name="reset_pass" class="btn btn-red">Actualizar contraseña</button>
            </form>
        </div>

        <div class="card">
            <h2>Probar credenciales antes de entrar</h2>
            <p style="font-size:.83rem;color:#6B7280;margin-bottom:4px;">Verifica que el usuario y contraseña funcionen
                correctamente.</p>
            <form method="POST">
                <label>Usuario</label>
                <input type="text" name="test_usuario" value="admin" required>
                <label>Contraseña</label>
                <input type="password" name="test_pass" placeholder="Contraseña a verificar" required>
                <button type="submit" name="test_login" class="btn btn-blue">Probar credenciales</button>
            </form>
        </div>

        <div>
            <a href="login.php" class="btn-dark">→ Ir al Login</a>
        </div>
    </div>
</body>

</html>