<?php
// reset_password.php
// Sube este archivo, ábrelo en el navegador, y listo.
// BÓRRALO del servidor después de usarlo.

$c = new mysqli('localhost', 'root', '', 'sistema_gym');
if ($c->connect_error) {
    die("<b style='color:red'>Sin conexión: " . $c->connect_error . "</b>");
}
$c->set_charset("utf8mb4");

// Generar hash aquí mismo y actualizarlo
$nueva_pass  = 'admin123';
$hash_fresco = password_hash($nueva_pass, PASSWORD_DEFAULT);
$verificado  = password_verify($nueva_pass, $hash_fresco); // debe ser true

$c->query("DELETE FROM usuarios WHERE usuario = 'admin'");
$c->query("INSERT INTO usuarios (nombre_completo, usuario, `password`, rol, estado)
           VALUES ('Admin Principal', 'admin', '$hash_fresco', 'Administrador', 'activo')");
$insertado = $c->insert_id;

// Leer de vuelta y verificar
$fila    = $c->query("SELECT `password`, estado FROM usuarios WHERE usuario='admin'")->fetch_assoc();
$ok_bd   = password_verify($nueva_pass, $fila['password']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reset Admin</title>
  <style>
    body { font-family: system-ui; background: #f3f4f6; display:flex; justify-content:center; padding:40px 20px; }
    .card { background:#fff; border-radius:10px; border:1px solid #e5e7eb; padding:30px; max-width:500px; width:100%; }
    h1 { font-size:1.1rem; margin-bottom:20px; color:#374151; }
    .ok  { background:#dcfce7; color:#166534; padding:12px 16px; border-radius:6px; margin:8px 0; font-size:.9rem; }
    .err { background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:6px; margin:8px 0; font-size:.9rem; }
    .btn { display:block; text-align:center; background:#b71c1c; color:#fff; padding:13px; border-radius:7px; text-decoration:none; font-weight:700; font-size:1rem; margin-top:20px; }
    .btn:hover { background:#7f0000; }
    code { font-family:monospace; background:#f3f4f6; padding:2px 6px; border-radius:3px; font-size:.82rem; }
  </style>
</head>
<body>
<div class="card">
  <h1>🔧 Reset de Administrador</h1>

  <div class="<?php echo $verificado ? 'ok' : 'err'; ?>">
    <?php echo $verificado ? '✅' : '❌'; ?> password_hash/verify funcionan en este PHP (<?php echo phpversion(); ?>)
  </div>

  <div class="<?php echo $insertado ? 'ok' : 'err'; ?>">
    <?php echo $insertado ? '✅' : '❌'; ?> Usuario admin <?php echo $insertado ? 'creado con ID '.$insertado : 'NO se pudo crear: '.$c->error; ?>
  </div>

  <div class="<?php echo $ok_bd ? 'ok' : 'err'; ?>">
    <?php echo $ok_bd ? '✅' : '❌'; ?> Verificación del hash guardado en BD: <?php echo $ok_bd ? 'CORRECTO' : 'FALLA — problema de charset o collation en la BD'; ?>
  </div>

  <?php if ($ok_bd): ?>
  <div class="ok" style="margin-top:12px; font-size:1rem; font-weight:700;">
    🎉 Todo listo. Entra con:<br><br>
    Usuario: <code>admin</code><br>
    Contraseña: <code>admin123</code>
  </div>
  <a href="login.php" class="btn">→ Ir al Login ahora</a>
  <?php else: ?>
  <div class="err" style="margin-top:12px;">
    El hash no se puede guardar/leer correctamente. Revisa el collation de la tabla usuarios (debe ser utf8mb4_unicode_ci).
  </div>
  <?php endif; ?>
</div>
</body>
</html>
