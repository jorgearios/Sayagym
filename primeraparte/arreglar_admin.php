<?php
// arreglar_admin.php
// Sube a primeraparte/, abre en navegador, sigue las instrucciones
// BORRA este archivo después de usarlo

// ── Conexión directa (LOCAL) ──────────────────────────────
$c = new mysqli('localhost', 'root', '', 'sistema_gym');
if ($c->connect_error) {
  die("<h2 style='color:red'>Error de conexión: " . $c->connect_error . "</h2>
    <p>Verifica que XAMPP/MySQL esté corriendo y que la base de datos se llame <b>sistema_gym</b></p>");
}
$c->set_charset("utf8mb4");

$mensaje = '';
$color = '#1a1a1a';

// ── ACCIÓN: generar hash fresco y guardarlo ───────────────
if (isset($_POST['nueva_pass']) && strlen($_POST['nueva_pass']) >= 4) {
  $nueva = $_POST['nueva_pass'];

  // Generar hash en ESTE mismo script (no depende de nada externo)
  $hash = password_hash($nueva, PASSWORD_DEFAULT);

  // Verificar inmediatamente que el hash funciona
  if (!password_verify($nueva, $hash)) {
    $mensaje = "ERROR CRÍTICO: password_hash/verify no funcionan en este servidor. Contacta al hosting.";
    $color = 'red';
  } else {
    // Actualizar por ID o por usuario, lo que encuentre
    $c->query("UPDATE usuarios SET password='$hash', estado='activo' WHERE usuario='admin'");
    $afectados = $c->affected_rows;

    if ($afectados === 0) {
      // No existe, lo creamos
      $c->query("INSERT INTO usuarios (nombre_completo, usuario, password, rol, estado)
                       VALUES ('Admin Principal', 'admin', '$hash', 'Administrador', 'activo')");
      $mensaje = "✅ Admin CREADO con contraseña: <b>$nueva</b> — ahora intenta entrar";
      $color = 'green';
    } else {
      // Verificar que quedó bien guardado
      $fila = $c->query("SELECT password FROM usuarios WHERE usuario='admin'")->fetch_assoc();
      if (password_verify($nueva, $fila['password'])) {
        $mensaje = "✅ Contraseña actualizada y VERIFICADA correctamente.<br>Entra con: <b>admin / $nueva</b>";
        $color = 'green';
      } else {
        $mensaje = "❌ El hash se guardó pero password_verify falla al releer. Problema de charset o BD.";
        $color = 'red';
      }
    }
  }
}

// ── ESTADO ACTUAL ─────────────────────────────────────────
$admins = $c->query("SELECT id_usuario, usuario, nombre_completo, rol, estado, password FROM usuarios WHERE rol='Administrador'");
$test_ok = false;
$admin_row = null;
if ($admins->num_rows > 0) {
  $admin_row = $admins->fetch_assoc();
  $test_ok = password_verify('admin123', $admin_row['password']);
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Arreglar Admin — Sayagym</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    body {
      font-family: system-ui, sans-serif;
      background: #f3f4f6;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 40px 20px
    }

    .card {
      background: #fff;
      border-radius: 10px;
      border: 1px solid #e5e7eb;
      padding: 28px 32px;
      width: 100%;
      max-width: 560px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, .08)
    }

    h1 {
      font-size: 1.2rem;
      color: #b71c1c;
      margin-bottom: 4px
    }

    .sub {
      font-size: 0.82rem;
      color: #6b7280;
      margin-bottom: 24px
    }

    .bloque {
      background: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 14px 16px;
      margin-bottom: 18px;
      font-size: 0.88rem
    }

    .bloque b {
      display: block;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: #6b7280;
      margin-bottom: 6px
    }

    .ok {
      color: #15803d;
      font-weight: 700
    }

    .err {
      color: #dc2626;
      font-weight: 700
    }

    .msg {
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 18px;
      font-size: 0.9rem;
      line-height: 1.5
    }

    label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: #374151;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: .3px
    }

    input {
      width: 100%;
      padding: 10px 12px;
      border: 1.5px solid #d1d5db;
      border-radius: 6px;
      font-size: 0.95rem;
      margin-bottom: 12px
    }

    input:focus {
      border-color: #b71c1c;
      outline: none
    }

    button {
      background: #b71c1c;
      color: #fff;
      border: none;
      width: 100%;
      padding: 12px;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer
    }

    button:hover {
      background: #7f0000
    }

    .ir {
      display: block;
      text-align: center;
      margin-top: 14px;
      color: #1565c0;
      font-weight: 600;
      font-size: 0.9rem
    }

    code {
      font-family: monospace;
      background: #f3f4f6;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 0.82rem;
      word-break: break-all
    }
  </style>
</head>

<body>
  <div class="card">
    <h1>🔧 Reparar acceso Admin — Sayagym</h1>
    <p class="sub">Escribe la contraseña que quieres usar y haz clic en Guardar. El sistema genera un hash fresco y lo
      verifica en el mismo instante.</p>

    <?php if ($mensaje): ?>
      <div class="msg"
        style="background:<?php echo $color === 'green' ? '#dcfce7' : '#fee2e2'; ?>;color:<?php echo $color === 'green' ? '#166534' : '#991b1b'; ?>;border-left:4px solid <?php echo $color === 'green' ? '#16a34a' : '#dc2626'; ?>">
        <?php echo $mensaje; ?>
      </div>
    <?php endif; ?>

    <!-- Estado actual -->
    <div class="bloque">
      <b>Estado actual en la BD</b>
      <?php if (!$admin_row): ?>
        <span class="err">❌ No existe ningún usuario con rol 'Administrador'</span>
      <?php else: ?>
        Usuario: <b><?php echo htmlspecialchars($admin_row['usuario']); ?></b> &nbsp;|&nbsp;
        Rol: <?php echo $admin_row['rol']; ?> &nbsp;|&nbsp;
        Estado:
        <?php echo $admin_row['estado'] === 'activo' ? '<span class="ok">activo</span>' : '<span class="err">' . $admin_row['estado'] . '</span>'; ?>
        <br><br>
        ¿El hash actual acepta "admin123"?
        <?php if ($test_ok): ?>
          <span class="ok">✅ SÍ — la contraseña admin123 debería funcionar</span><br>
          <small style="color:#6b7280">Si el hash es correcto y aún no puedes entrar, el problema es de sesiones PHP. Borra
            las cookies del navegador o prueba en incógnito.</small>
        <?php else: ?>
          <span class="err">❌ NO — el hash en BD no corresponde a admin123</span><br>
          <small style="color:#6b7280">Usa el formulario abajo para establecer una nueva contraseña.</small>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Formulario para nueva contraseña -->
    <form method="POST">
      <label>Nueva contraseña para el usuario admin</label>
      <input type="text" name="nueva_pass" value="admin123" required
        placeholder="Escribe la contraseña que quieres usar">
      <button type="submit">🔑 Guardar contraseña y verificar ahora mismo</button>
    </form>

    <?php if ($test_ok || ($mensaje && $color === 'green')): ?>
      <a href="login.php" class="ir">→ Ir al Login e intentar entrar</a>
    <?php endif; ?>

    <div class="bloque" style="margin-top:18px;">
      <b>Información técnica (por si el problema persiste)</b>
      PHP: <?php echo phpversion(); ?> &nbsp;|&nbsp;
      MySQL: <?php echo $c->server_info; ?><br>
      BD: <?php echo $c->query("SELECT DATABASE()")->fetch_row()[0]; ?><br>
      <?php if ($admin_row): ?>
        Hash en BD: <code><?php echo htmlspecialchars($admin_row['password']); ?></code>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>