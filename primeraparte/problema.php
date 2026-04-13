<?php
/**
 * Archivo: problema.php
 * Descripción: Página de registro de tickets, reportes o problemas para los usuarios.
 * Parte del sistema integral de gestión Sayagym.
 */

// ver_problema.php
// Sube este archivo a primeraparte/ y ábrelo en el navegador
// Te muestra EXACTAMENTE por qué falla el login
// BÓRRALO después de usarlo

// Conexión directa sin incluir nada más
$c = new mysqli('127.0.0.1', 'e11a5294076c2dca0c0117243bb6dcff951d5cb5da79f0ca', '', 'sistema_gym');
if ($c->connect_error) {
    die("<h2 style='color:red;font-family:sans-serif'>
    No se puede conectar a MySQL<br><br>
    Error: " . $c->connect_error . "<br><br>
    Verifica que XAMPP esté corriendo y que la BD 'sistema_gym' exista.
    </h2>");
}
$c->set_charset("utf8mb4");

// Si se envió el formulario de corrección
$accion = "";
if (isset($_POST['corregir'])) {
    $hash_nuevo = password_hash('admin123', PASSWORD_DEFAULT);
    // Borrar cualquier admin anterior y crear uno limpio
    $c->query("DELETE FROM usuarios WHERE usuario = 'admin'");
    $c->query("INSERT INTO `usuarios` 
               (`nombre_completo`, `usuario`, `password`, `rol`, `estado`) 
               VALUES ('Admin Principal', 'admin', '$hash_nuevo', 'Administrador', 'activo')");
    // Leer de vuelta
    $fila_nueva = $c->query("SELECT `password` FROM `usuarios` WHERE `usuario`='admin'")->fetch_assoc();
    $ok = password_verify('admin123', $fila_nueva['password']);
    if ($ok) {
        $accion = "SUCCESS";
    } else {
        $accion = "FAIL";
    }
}

// ─── DIAGNÓSTICO ────────────────────────────────────────────────────────────

// 1. ¿Existe la tabla usuarios?
$existe_tabla = $c->query("SHOW TABLES LIKE 'usuarios'")->num_rows > 0;

// 2. ¿Cuántos usuarios hay?
$total_usuarios = 0;
$usuarios_lista = [];
if ($existe_tabla) {
    $res = $c->query("SELECT `id_usuario`, `usuario`, `rol`, `estado`, `password` FROM `usuarios`");
    $total_usuarios = $res->num_rows;
    while ($u = $res->fetch_assoc()) {
        $usuarios_lista[] = $u;
    }
}

// 3. Buscar admin específicamente
$admin_row = null;
$admin_existe = false;
if ($existe_tabla) {
    $res2 = $c->query("SELECT `id_usuario`, `usuario`, `rol`, `estado`, `password` 
                       FROM `usuarios` WHERE `usuario` = 'admin' LIMIT 1");
    if ($res2 && $res2->num_rows > 0) {
        $admin_row = $res2->fetch_assoc();
        $admin_existe = true;
    }
}

// 4. Si existe, verificar la contraseña
$verify_admin123 = false;
$verify_password = false;
$verify_admin = false;
$hash_en_bd = "";
if ($admin_row) {
    $hash_en_bd = $admin_row['password'];
    $verify_admin123 = password_verify('admin123', $hash_en_bd);
    $verify_password = password_verify('password', $hash_en_bd);
    $verify_admin = password_verify('admin', $hash_en_bd);
}

// 5. Generar un hash fresco para mostrar cómo debe verse
$hash_ejemplo = password_hash('admin123', PASSWORD_DEFAULT);
$verify_ejemplo = password_verify('admin123', $hash_ejemplo);

// 6. Verificar prepare() con backtick (igual que login.php corregido)
$prepare_ok = false;
if ($existe_tabla) {
    $stmt = $c->prepare("SELECT `id_usuario`, `nombre_completo`, `password`, `rol` FROM `usuarios` WHERE `usuario` = ? AND `estado` = 'activo' LIMIT 1");
    if ($stmt) {
        $u_test = 'admin';
        $stmt->bind_param("s", $u_test);
        $stmt->execute();
        $fila_prepare = $stmt->get_result()->fetch_assoc();
        $prepare_ok = ($fila_prepare !== null);
        $stmt->close();
    }
}

function ic($cond)
{
    return $cond
        ? "<span style='color:#15803d;font-weight:700'>✅ SÍ</span>"
        : "<span style='color:#dc2626;font-weight:700'>❌ NO</span>";
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Diagnóstico Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: system-ui, sans-serif;
            background: #f3f4f6;
            padding: 30px 16px;
            color: #1a1a1a
        }

        .wrap {
            max-width: 680px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 14px
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px 24px
        }

        .card h2 {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6b7280;
            margin-bottom: 14px
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: .9rem
        }

        .row:last-child {
            border-bottom: none
        }

        .lbl {
            color: #374151
        }

        code {
            font-family: monospace;
            background: #f3f4f6;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: .78rem;
            word-break: break-all;
            display: inline-block;
            max-width: 380px
        }

        .banner {
            padding: 14px 18px;
            border-radius: 7px;
            font-size: .95rem;
            line-height: 1.6;
            margin-bottom: 4px
        }

        .banner.ok {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #16a34a
        }

        .banner.err {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626
        }

        .banner.warn {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #d97706
        }

        button {
            background: #b71c1c;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 10px
        }

        button:hover {
            background: #7f0000
        }

        .go {
            display: block;
            text-align: center;
            background: #15803d;
            color: #fff;
            padding: 13px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 6px
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem
        }

        td,
        th {
            padding: 7px 10px;
            border: 1px solid #e5e7eb;
            text-align: left
        }

        th {
            background: #f9fafb;
            font-weight: 700
        }
    </style>
</head>

<body>
    <div class="wrap">

        <?php if ($accion === "SUCCESS"): ?>
            <div class="banner ok">
                ✅ <strong>¡Listo! Admin creado y verificado correctamente.</strong><br>
                Entra con: <strong>admin</strong> / <strong>admin123</strong>
            </div>
            <a href="login.php" class="go">→ Ir al Login ahora</a>

        <?php elseif ($accion === "FAIL"): ?>
            <div class="banner err">
                ❌ El hash se guardó pero password_verify sigue fallando. El problema es de configuración del servidor
                (collation o extensión bcrypt). Contacta a tu hosting.
            </div>
        <?php endif; ?>

        <!-- PASO 1 -->
        <div class="card">
            <h2>Paso 1 — Tabla usuarios</h2>
            <div class="row"><span class="lbl">¿Existe la tabla usuarios?</span><?php echo ic($existe_tabla); ?></div>
            <div class="row"><span class="lbl">Total de usuarios
                    registrados</span><span><?php echo $total_usuarios; ?></span></div>
            <div class="row"><span class="lbl">¿Existe el usuario "admin"?</span><?php echo ic($admin_existe); ?></div>
            <?php if ($admin_existe): ?>
                <div class="row"><span class="lbl">Estado del admin</span>
                    <span><?php echo $admin_row['estado'] === 'activo'
                        ? "<span style='color:#15803d;font-weight:700'>activo ✅</span>"
                        : "<span style='color:#dc2626;font-weight:700'>" . $admin_row['estado'] . " ❌</span>"; ?></span>
                </div>
                <div class="row"><span class="lbl">Rol</span><span><?php echo $admin_row['rol']; ?></span></div>
                <div class="row"><span class="lbl">Hash en
                        BD</span><code><?php echo htmlspecialchars($hash_en_bd); ?></code></div>
            <?php endif; ?>
        </div>

        <!-- PASO 2 -->
        <?php if ($admin_existe): ?>
            <div class="card">
                <h2>Paso 2 — ¿A qué contraseña corresponde el hash?</h2>
                <div class="row"><span class="lbl">¿El hash acepta
                        <strong>"admin123"</strong>?</span><?php echo ic($verify_admin123); ?></div>
                <div class="row"><span class="lbl">¿El hash acepta "password"?</span><?php echo ic($verify_password); ?>
                </div>
                <div class="row"><span class="lbl">¿El hash acepta "admin"?</span><?php echo ic($verify_admin); ?></div>

                <?php if ($verify_admin123): ?>
                    <div class="banner ok" style="margin-top:12px">
                        ✅ El hash es correcto para "admin123". El problema es otro (cookies de sesión o login.php sin backtick
                        en `password`).
                        <br>Cierra el navegador completamente, ábrelo de nuevo y entra con <strong>admin / admin123</strong>.
                    </div>
                <?php elseif ($verify_password): ?>
                    <div class="banner warn" style="margin-top:12px">
                        ⚠️ El hash corresponde a la contraseña <strong>"password"</strong> (no a "admin123"). Usa el botón de
                        abajo para corregirlo.
                    </div>
                <?php else: ?>
                    <div class="banner err" style="margin-top:12px">
                        ❌ El hash no corresponde a ninguna contraseña conocida. Hay que recrear el admin.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- PASO 3 -->
        <div class="card">
            <h2>Paso 3 — Verificar que password_hash funciona en este servidor</h2>
            <div class="row"><span class="lbl">Hash generado ahora
                    mismo</span><code><?php echo htmlspecialchars($hash_ejemplo); ?></code></div>
            <div class="row"><span class="lbl">¿password_verify funciona?</span><?php echo ic($verify_ejemplo); ?></div>
            <div class="row"><span class="lbl">prepare() con backtick funciona</span><?php echo ic($prepare_ok); ?>
            </div>
        </div>

        <!-- PASO 4: Todos los usuarios -->
        <?php if ($total_usuarios > 0): ?>
            <div class="card">
                <h2>Todos los usuarios en la BD</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Hash (inicio)</th>
                    </tr>
                    <?php foreach ($usuarios_lista as $u): ?>
                        <tr>
                            <td><?php echo $u['id_usuario']; ?></td>
                            <td><strong><?php echo htmlspecialchars($u['usuario']); ?></strong></td>
                            <td><?php echo $u['rol']; ?></td>
                            <td><?php echo $u['estado']; ?></td>
                            <td><code><?php echo htmlspecialchars(substr($u['password'], 0, 25)); ?>...</code></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <!-- SOLUCIÓN -->
        <?php if (!$verify_admin123 || !$admin_existe): ?>
            <div class="card">
                <h2>Solución — Recrear admin con contraseña correcta</h2>
                <p style="font-size:.88rem;color:#6b7280;margin-bottom:12px">
                    Esto borra el usuario "admin" actual y lo crea de nuevo con la contraseña <strong>admin123</strong>.
                </p>
                <form method="POST">
                    <button type="submit" name="corregir" value="1">
                        🔑 Recrear admin → admin / admin123
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>El hash es correcto — problema de sesión o cookie</h2>
                <div class="banner warn">
                    El hash en la BD acepta "admin123" correctamente. El problema es que el navegador tiene una sesión o
                    cookie vieja.<br><br>
                    <strong>Solución:</strong><br>
                    1. Cierra el navegador completamente (todos los tabs)<br>
                    2. Vuelve a abrir y entra a <code>localhost/Sayagym/primeraparte/login.php</code><br>
                    3. O abre en modo incógnito (Ctrl+Shift+N)
                </div>
                <a href="login.php" class="go">→ Ir al Login en modo normal</a>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>