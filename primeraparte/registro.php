<?php
/**
 * Archivo: registro.php
 * Descripción: Página pública de auto-registro para posibles clientes o usuarios nuevos.
 * Parte del sistema integral de gestión Sayagym.
 */

// registro.php - Registro de Nuevos Socios
session_start();
include 'config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $apellido = $conexion->real_escape_string($_POST['apellido']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $telefono = $conexion->real_escape_string($_POST['telefono']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $id_mem = !empty($_POST['id_membresia']) ? (int) $_POST['id_membresia'] : null;

    // Verificar y crear columna password si no existe
    $check_col = $conexion->query("SHOW COLUMNS FROM socios LIKE 'password'");
    if ($check_col->num_rows == 0) {
        $hash_default = password_hash('123456', PASSWORD_DEFAULT);
        $conexion->query("ALTER TABLE socios ADD COLUMN password VARCHAR(255) DEFAULT '$hash_default' AFTER correo");
    }

    // Validar si el correo ya está registrado
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
            $fecha_vencimiento = $fecha_registro; // default: hoy

            // Si eligió membresía, calculamos la fecha de vencimiento
            if ($id_mem) {
                $res_mem = $conexion->query("SELECT duracion_meses FROM membresias WHERE id_membresia = $id_mem");
                if ($res_mem && $res_mem->num_rows > 0) {
                    $meses = $res_mem->fetch_assoc()['duracion_meses'];
                    $fecha_vencimiento = date('Y-m-d', strtotime("+$meses months"));
                }
            }

            if ($id_mem) {
                $stmt_ins = $conexion->prepare(
                    "INSERT INTO socios (nombre, apellido, correo, telefono, password, estado, fecha_registro, fecha_vencimiento, id_membresia)
                     VALUES (?, ?, ?, ?, ?, 'activo', ?, ?, ?)"
                );
                $stmt_ins->bind_param("sssssssi", $nombre, $apellido, $correo, $telefono, $password, $fecha_registro, $fecha_vencimiento, $id_mem);
            } else {
                $stmt_ins = $conexion->prepare(
                    "INSERT INTO socios (nombre, apellido, correo, telefono, password, estado, fecha_registro, fecha_vencimiento)
                     VALUES (?, ?, ?, ?, ?, 'activo', ?, ?)"
                );
                $stmt_ins->bind_param("sssssss", $nombre, $apellido, $correo, $telefono, $password, $fecha_registro, $fecha_vencimiento);
            }

            if (!$stmt_ins) {
                $mensaje = "<div class='alert'>Error al preparar registro: " . $conexion->error . "</div>";
            } elseif ($stmt_ins->execute()) {
                $mensaje = "<div class='alert' style='background:#DCFCE7; color:#15803D; border-left-color:#15803D;'>
                    <strong>¡Cuenta creada exitosamente!</strong><br>
                    Ya puedes <a href='login.php' style='color:inherit; font-weight:700; text-decoration:underline;'>iniciar sesión</a> con tu correo y contraseña.
                </div>";
            } else {
                $mensaje = "<div class='alert'>Error: " . $conexion->error . "</div>";
            }
        }
    }
}

// Obtener membresías activas para mostrar en tarjetas
$mems = $conexion->query("SELECT id_membresia, nombre, precio, duracion_meses FROM membresias WHERE estado = 'activo' ORDER BY precio ASC");
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sayagym | Crear Cuenta</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --red: #B71C1C;
            --red-dark: #7F0000;
            --bg: #F0F0F0;
            --card: #FFFFFF;
            --text: #1A1A1A;
            --muted: #6B7280;
            --border: #E5E7EB;
            --radius: 8px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 540px;
        }

        .login-card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .login-header {
            background: linear-gradient(135deg, var(--red-dark) 0%, var(--red) 100%);
            padding: 28px 20px;
            text-align: center;
            color: white;
        }

        .login-header img {
            max-height: 56px;
            background: white;
            padding: 5px 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .login-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.4rem;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .login-body {
            padding: 28px;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            font-size: 0.92rem;
            font-family: 'DM Sans', sans-serif;
            margin-bottom: 14px;
            outline: none;
            transition: border-color 0.2s;
            appearance: auto;
            background: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(183, 28, 28, 0.1);
        }

        .btn-login {
            background: var(--red);
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
        }

        .btn-login:hover {
            background: var(--red-dark);
        }

        .alert {
            background: #FEE2E2;
            color: #DC2626;
            padding: 14px;
            border-radius: 6px;
            border-left: 4px solid #DC2626;
            font-size: 0.88rem;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .hr-div {
            border: none;
            border-top: 1px solid var(--border);
            margin: 20px 0;
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 14px;
        }

        /* Plan cards */
        .planes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .plan-label {
            display: block;
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 14px 12px;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            position: relative;
        }

        .plan-label:hover {
            border-color: var(--red);
            background: #FEF2F2;
        }

        .plan-label input[type=radio] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .plan-label.selected {
            border-color: var(--red);
            background: #FEF2F2;
        }

        .plan-check-icon {
            position: absolute;
            top: 8px;
            right: 10px;
            color: var(--red);
            display: none;
            font-size: 1rem;
        }

        .plan-label.selected .plan-check-icon {
            display: block;
        }

        .plan-name {
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--text);
            margin-bottom: 4px;
        }

        .plan-price {
            font-family: 'Oswald', sans-serif;
            font-size: 1.35rem;
            color: var(--red);
            font-weight: 700;
            line-height: 1;
        }

        .plan-dur {
            font-size: 0.76rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .login-footer {
            text-align: center;
            padding: 16px;
            background: #FAFAFA;
            border-top: 1px solid var(--border);
            font-size: 0.8rem;
            color: var(--muted);
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../Sayagym%20logo.png" alt="Sayagym" onerror="this.style.display='none'">
                <h1 class="login-title">Crear Cuenta — Sayagym</h1>
                <div style="font-size:0.88rem; margin-top:6px; opacity:0.85;">¡Únete y empieza tu transformación!</div>
            </div>

            <div class="login-body">
                <?php echo $mensaje; ?>

                <form action="registro.php" method="POST">

                    <!-- ── DATOS PERSONALES ── -->
                    <div class="section-title"><i class="ti ti-user me-1"></i>Datos Personales</div>
                    <div class="two-col">
                        <div>
                            <label class="form-label">Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Juan" required>
                        </div>
                        <div>
                            <label class="form-label">Apellido(s)</label>
                            <input type="text" name="apellido" class="form-control" placeholder="Pérez" required>
                        </div>
                    </div>

                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" placeholder="usuario@email.com" required>

                    <div class="two-col">
                        <div>
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" placeholder="10 dígitos">
                        </div>
                        <div>
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="Mínimo 6 caracteres" required>
                        </div>
                    </div>

                    <hr class="hr-div">

                    <!-- ── PLAN DE MEMBRESÍA ── -->
                    <div class="section-title"><i class="ti ti-credit-card me-1"></i>Elige tu Plan <span
                            style="font-weight:400; color:var(--muted); font-size:0.75rem;">(opcional)</span></div>

                    <?php if ($mems && $mems->num_rows > 0): ?>
                        <div class="planes-grid" id="planes-grid">
                            <?php while ($m = $mems->fetch_assoc()): ?>
                                <label class="plan-label" id="card-<?php echo $m['id_membresia']; ?>">
                                    <input type="radio" name="id_membresia" value="<?php echo $m['id_membresia']; ?>"
                                        onchange="selectPlan(<?php echo $m['id_membresia']; ?>)">
                                    <i class="ti ti-circle-check plan-check-icon"></i>
                                    <div class="plan-name"><?php echo htmlspecialchars($m['nombre']); ?></div>
                                    <div class="plan-price">$<?php echo number_format($m['precio'], 2); ?></div>
                                    <div class="plan-dur"><?php echo $m['duracion_meses']; ?>
                                        mes<?php echo $m['duracion_meses'] != 1 ? 'es' : ''; ?></div>
                                </label>
                            <?php endwhile; ?>
                        </div>
                        <p style="font-size:0.78rem; color:var(--muted); margin-bottom:16px;">
                            <i class="ti ti-info-circle"></i> Si no eliges un plan ahora, el administrador lo asignará en tu
                            primera visita.
                        </p>
                    <?php else: ?>
                        <p
                            style="font-size:0.85rem; color:var(--muted); padding:12px 14px; background:#F9FAFB; border-radius:6px; margin-bottom:16px; border:1px solid var(--border);">
                            <i class="ti ti-info-circle"></i> Los planes se asignan en recepción. Regístrate y acércate al
                            gimnasio para activar tu membresía.
                        </p>
                    <?php endif; ?>

                    <button type="submit" class="btn-login">
                        <i class="ti ti-user-plus"></i> Registrarme Ahora
                    </button>

                    <div style="text-align:center; margin-top:18px; font-size:0.88rem;">
                        <a href="login.php" style="color:var(--muted); text-decoration:none; font-weight:500;">
                            <i class="ti ti-arrow-left"></i> Ya tengo cuenta — Iniciar Sesión
                        </a>
                    </div>
                </form>
            </div>

            <div class="login-footer">
                Sistema de Gestión Integral &copy; <?php echo date('Y'); ?> — Sayagym
            </div>
        </div>
    </div>

    <script>
        function selectPlan(id) {
            document.querySelectorAll('.plan-label').forEach(c => c.classList.remove('selected'));
            var card = document.getElementById('card-' + id);
            if (card) card.classList.add('selected');
        }
    </script>
</body>

</html>