<?php
// diagnostico.php
// Sube este archivo al servidor, ábrelo en el navegador, copia el resultado
// ELIMÍNALO DESPUÉS de usarlo — no dejarlo en producción

// Conectar directo (sin pasar por config.php ni auth.php)
$host = '127.0.0.1';   // ← mismo que configgg.php
$user = 'admin';        // ← mismo que configgg.php
$pass = 'e11a5294076c2dca0c0117243bb6dcff951d5cb5da79f0ca'; // ← mismo que configgg.php
$db = 'sistema_gym';

// Si estás en LOCAL cambia a:
// $host = 'localhost'; $user = 'root'; $pass = ''; $db = 'sistema_gym';

$c = new mysqli($host, $user, $pass, $db);

// Acción: resetear contraseña desde URL ?reset=1
if (isset($_GET['reset'])) {
  $nuevo_hash = password_hash('admin123', PASSWORD_DEFAULT);
  $c->query("UPDATE usuarios SET password='$nuevo_hash', estado='activo' WHERE usuario='admin'");
  echo "<div style='background:#d4edda;padding:14px;border-radius:6px;font-family:monospace;margin-bottom:12px;'>";
  echo "✅ Contraseña reseteada. Filas afectadas: " . $c->affected_rows . "<br>";
  echo "Intenta entrar con: <b>admin / admin123</b></div>";
}

// Acción: crear admin si no existe ?crear=1
if (isset($_GET['crear'])) {
  $nuevo_hash = password_hash('admin123', PASSWORD_DEFAULT);
  $c->query("INSERT INTO usuarios (nombre_completo, usuario, password, rol, estado)
               VALUES ('Admin Principal', 'admin', '$nuevo_hash', 'Administrador', 'activo')");
  echo "<div style='background:#d4edda;padding:14px;border-radius:6px;font-family:monospace;margin-bottom:12px;'>";
  echo "✅ Admin creado. ID insertado: " . $c->insert_id . "<br>";
  echo "Intenta entrar con: <b>admin / admin123</b></div>";
}

?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Diagnóstico Login Sayagym</title>
  <style>
    body {
      font-family: monospace;
      background: #f5f5f5;
      padding: 24px;
      color: #1a1a1a
    }

    h2 {
      font-family: sans-serif;
      font-size: 1rem;
      color: #374151;
      border-bottom: 2px solid #e5e7eb;
      padding-bottom: 6px;
      margin: 20px 0 10px
    }

    .box {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 14px 18px;
      margin-bottom: 16px
    }

    .ok {
      background: #dcfce7;
      color: #166534;
      padding: 4px 10px;
      border-radius: 4px;
      display: inline-block
    }

    .err {
      background: #fee2e2;
      color: #991b1b;
      padding: 4px 10px;
      border-radius: 4px;
      display: inline-block
    }

    .warn {
      background: #fef3c7;
      color: #92400e;
      padding: 4px 10px;
      border-radius: 4px;
      display: inline-block
    }

    table {
      border-collapse: collapse;
      width: 100%
    }

    td,
    th {
      padding: 7px 10px;
      border: 1px solid #e5e7eb;
      text-align: left;
      font-size: 0.85rem
    }

    th {
      background: #f9fafb;
      font-weight: 700
    }

    .btn {
      display: inline-block;
      background: #b71c1c;
      color: #fff;
      padding: 8px 18px;
      border-radius: 5px;
      text-decoration: none;
      font-family: sans-serif;
      font-size: 0.85rem;
      margin-right: 8px;
      margin-top: 8px
    }

    .btn-blue {
      background: #1565c0
    }

    .btn-green {
      background: #15803d
    }
  </style>
</head>

<body>

  <?php if ($c->connect_error): ?>
    <div class="box">
      <span class="err">❌ ERROR DE CONEXIÓN A LA BASE DE DATOS</span><br><br>
      <b>Error:</b> <?php echo $c->connect_error; ?><br><br>
      <b>Parámetros usados:</b><br>
      Host: <?php echo $host; ?><br>
      User: <?php echo $user; ?><br>
      BD: <?php echo $db; ?><br><br>
      <b>Solución:</b> Verifica que el archivo <code>configgg.php</code> tenga los datos correctos del servidor.
    </div>
  <?php else: ?>

    <!-- PASO 1: Conexión -->
    <div class="box">
      <h2>1. Conexión a la base de datos</h2>
      <span class="ok">✓ Conexión exitosa a <b><?php echo $db; ?></b> en <b><?php echo $host; ?></b></span>
    </div>

    <!-- PASO 2: Tabla usuarios -->
    <div class="box">
      <h2>2. Tabla <code>usuarios</code></h2>
      <?php
      $t = $c->query("SHOW TABLES LIKE 'usuarios'");
      if ($t->num_rows === 0):
        ?>
        <span class="err">❌ La tabla <b>usuarios</b> NO EXISTE</span><br><br>
        Debes importar el script <code>sistema_gym_v2.sql</code> en phpMyAdmin.
      <?php else: ?>
        <span class="ok">✓ Tabla usuarios existe</span>
        <?php
        $rows = $c->query("SELECT id_usuario, nombre_completo, usuario, rol, estado, LEFT(password,30) as hash_inicio FROM usuarios");
        if ($rows->num_rows === 0):
          ?>
          <br><br>
          <span class="err">❌ La tabla está VACÍA — no hay ningún usuario</span><br><br>
          <a href="?crear=1" class="btn btn-green">✚ Crear admin ahora (admin/admin123)</a>
        <?php else: ?>
          <br><br>
          <b>Usuarios registrados (<?php echo $rows->num_rows; ?>):</b>
          <table>
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Hash (primeros 30 chars)</th>
            </tr>
            <?php while ($r = $rows->fetch_assoc()): ?>
              <tr>
                <td><?php echo $r['id_usuario']; ?></td>
                <td><b><?php echo htmlspecialchars($r['usuario']); ?></b></td>
                <td><?php echo htmlspecialchars($r['nombre_completo']); ?></td>
                <td><?php echo $r['rol']; ?></td>
                <td>
                  <?php if ($r['estado'] === 'activo'): ?>
                    <span class="ok"><?php echo $r['estado']; ?></span>
                  <?php else: ?>
                    <span class="err"><?php echo $r['estado']; ?></span>
                  <?php endif; ?>
                </td>
                <td><code><?php echo htmlspecialchars($r['hash_inicio']); ?>...</code></td>
              </tr>
            <?php endwhile; ?>
          </table>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- PASO 3: Verificar password_verify con admin123 -->
    <div class="box">
      <h2>3. Test de contraseña: ¿funciona <code>admin / admin123</code>?</h2>
      <?php
      $row_admin = $c->query("SELECT password, estado FROM usuarios WHERE usuario='admin' LIMIT 1")->fetch_assoc();
      if (!$row_admin):
        ?>
        <span class="err">❌ Usuario <b>admin</b> no encontrado</span><br><br>
        <a href="?crear=1" class="btn btn-green">✚ Crear admin (admin/admin123)</a>
      <?php else:
        $verify = password_verify('admin123', $row_admin['password']);
        ?>
        <?php if ($verify): ?>
          <span class="ok">✅ password_verify('admin123', hash_en_bd) = TRUE — la contraseña es correcta</span>
          <br><br>
          <?php if ($row_admin['estado'] !== 'activo'): ?>
            <span class="err">❌ Pero el estado del usuario es: <b><?php echo $row_admin['estado']; ?></b> — debe ser
              'activo'</span><br>
            <a href="?reset=1" class="btn">Activar y resetear contraseña</a>
          <?php else: ?>
            <span class="ok">✓ Estado: activo</span><br><br>
            <b>✅ El login debería funcionar.</b> El problema puede ser de sesiones o de cookies. Intenta:
            <ol style="margin-top:8px;font-family:sans-serif;font-size:0.9rem;line-height:2;">
              <li>Borrar cookies del navegador</li>
              <li>Abrir en modo incógnito</li>
              <li>Entrar directamente a: <a href="login.php">login.php</a></li>
            </ol>
          <?php endif; ?>
        <?php else: ?>
          <span class="err">❌ password_verify('admin123', hash_en_bd) = FALSE — el hash en la BD no corresponde a
            'admin123'</span>
          <br><br>
          Hash actual en BD: <code><?php echo htmlspecialchars(substr($row_admin['password'], 0, 60)); ?>...</code><br><br>
          <a href="?reset=1" class="btn">🔑 Resetear a admin/admin123 ahora</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- PASO 4: Verificar prepare() -->
    <div class="box">
      <h2>4. Test de <code>prepare()</code> — igual que login.php</h2>
      <?php
      $stmt = $c->prepare("SELECT id_usuario, nombre_completo, password, rol FROM usuarios WHERE usuario = ? AND estado = 'activo'");
      if (!$stmt):
        ?>
        <span class="err">❌ prepare() falló: <?php echo $c->error; ?></span><br>
        Esto significa que login.php tampoco puede ejecutar la consulta.
      <?php else: ?>
        <span class="ok">✓ prepare() funciona correctamente</span>
        <?php
        $u = 'admin';
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $res = $stmt->get_result();
        $found = $res->fetch_assoc();
        if (!$found):
          ?>
          <br><br>
          <span class="err">❌ La consulta no devuelve filas para usuario='admin' con estado='activo'</span>
        <?php else: ?>
          <br><br>
          <span class="ok">✓ Consulta devuelve el usuario: <b><?php echo htmlspecialchars($found['nombre_completo']); ?></b>
            (rol: <?php echo $found['rol']; ?>)</span>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- PASO 5: Verificar php.ini session -->
    <div class="box">
      <h2>5. Configuración del servidor</h2>
      <table>
        <tr>
          <td><b>PHP version</b></td>
          <td><?php echo phpversion(); ?></td>
        </tr>
        <tr>
          <td><b>session.save_path</b></td>
          <td><?php echo ini_get('session.save_path') ?: '(default)'; ?></td>
        </tr>
        <tr>
          <td><b>session.cookie_httponly</b></td>
          <td><?php echo ini_get('session.cookie_httponly') ? 'on' : 'off'; ?></td>
        </tr>
        <tr>
          <td><b>output_buffering</b></td>
          <td><?php echo ini_get('output_buffering'); ?></td>
        </tr>
      </table>
    </div>

    <!-- Acciones de emergencia -->
    <div class="box">
      <h2>Acciones de emergencia</h2>
      <a href="?reset=1" class="btn">🔑 Resetear contraseña admin → admin123</a>
      <a href="?crear=1" class="btn btn-blue">✚ Crear admin nuevo</a>
      <a href="login.php" class="btn btn-green">→ Ir al Login</a>
    </div>

  <?php endif; ?>
</body>

</html>