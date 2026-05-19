<?php
/**
 * Instalador de la aplicación Gestor de Tareas — Bunglebuild S.L.
 *
 * Este script crea la estructura de la base de datos e inicializa
 * los datos necesarios para el funcionamiento de la aplicación.
 * Lee la configuración del fichero app/config o del .env de Laravel.
 *
 * Ubicación: /install/index.php
 * Acceso:    http://localhost/install/
 *
 * @author  Alumno DWES
 * @version 1.0
 * @date    2024-12-01
 */

// Cargar variables de entorno desde el .env de Laravel
$envFile = dirname(__DIR__) . '/.env';
$config  = [];
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        if (strpos(trim($linea), '#') === 0) continue;
        if (strpos($linea, '=') !== false) {
            [$clave, $valor] = explode('=', $linea, 2);
            $config[trim($clave)] = trim($valor, " \t\n\r\0\x0B\"");
        }
    }
}

$dbHost = $config['DB_HOST']     ?? '127.0.0.1';
$dbPort = $config['DB_PORT']     ?? '3306';
$dbName = $config['DB_DATABASE'] ?? 'gestor_tareas';
$dbUser = $config['DB_USERNAME'] ?? 'root';
$dbPass = $config['DB_PASSWORD'] ?? '';

$mensaje = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conectar sin especificar BD para poder crearla
        $pdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
            $dbUser, $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Crear base de datos si no existe
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");

        // Borrar tablas existentes
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("DROP TABLE IF EXISTS tareas");
        $pdo->exec("DROP TABLE IF EXISTS usuarios");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Crear tabla usuarios
        $pdo->exec("CREATE TABLE usuarios (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre     VARCHAR(150)              NOT NULL,
            usuario    VARCHAR(80)               NOT NULL UNIQUE,
            password   VARCHAR(255)              NOT NULL,
            rol        ENUM('admin','operario')  NOT NULL DEFAULT 'operario',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Crear tabla tareas
        $pdo->exec("CREATE TABLE tareas (
            id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nif                     VARCHAR(20)           NOT NULL,
            persona_contacto        VARCHAR(150)          NOT NULL,
            telefono                VARCHAR(30)           NULL,
            email                   VARCHAR(150)          NOT NULL,
            descripcion             TEXT                  NOT NULL,
            anotaciones_anteriores  TEXT                  NULL,
            anotaciones_posteriores TEXT                  NULL,
            direccion               VARCHAR(255)          NULL,
            poblacion               VARCHAR(100)          NULL,
            cp                      CHAR(5)               NULL,
            provincia               CHAR(2)               NULL,
            estado                  ENUM('B','P','R','C') NOT NULL DEFAULT 'P',
            fecha_creacion          TIMESTAMP             DEFAULT CURRENT_TIMESTAMP,
            fecha_realizacion       DATE                  NULL,
            operario                VARCHAR(80)           NULL,
            fichero_resumen         VARCHAR(255)          NULL,
            CONSTRAINT fk_operario FOREIGN KEY (operario) REFERENCES usuarios(usuario)
                ON UPDATE CASCADE ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Insertar datos iniciales
        $pdo->exec("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES
            ('Administrador','admin','admin123','admin'),
            ('Operario Demo','operario','oper123','operario')");

        $mensaje = '✔ Instalación completada correctamente. Base de datos creada e inicializada.';

    } catch (PDOException $e) {
        $error = 'Error de base de datos: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Instalador — Gestor de Tareas</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; background:#111; color:#eee; max-width:600px; margin:60px auto; padding:20px; }
    h1   { color:#f5c842; border-bottom:2px solid #f5c842; padding-bottom:8px; }
    .ok  { background:rgba(76,175,120,.2); border:1px solid #4caf78; color:#4caf78; padding:12px; border-radius:4px; margin:16px 0; }
    .err { background:rgba(224,82,82,.2);  border:1px solid #e05252; color:#e05252; padding:12px; border-radius:4px; margin:16px 0; }
    table { width:100%; border-collapse:collapse; margin:16px 0; }
    td, th { padding:8px 12px; border:1px solid #333; text-align:left; }
    th { background:#222; color:#f5c842; }
    button { background:#f5c842; color:#111; border:none; padding:10px 24px; border-radius:4px; font-size:15px; font-weight:700; cursor:pointer; }
    button:hover { opacity:.85; }
</style>
</head>
<body>
<h1>⚒ Instalador — Bunglebuild S.L.</h1>
<p>Este instalador creará la base de datos y cargará los datos iniciales.</p>

<?php if ($mensaje): ?><div class="ok"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<h3>Configuración detectada (.env)</h3>
<table>
    <tr><th>Host</th><td><?= htmlspecialchars($dbHost) ?></td></tr>
    <tr><th>Puerto</th><td><?= htmlspecialchars($dbPort) ?></td></tr>
    <tr><th>Base de datos</th><td><?= htmlspecialchars($dbName) ?></td></tr>
    <tr><th>Usuario</th><td><?= htmlspecialchars($dbUser) ?></td></tr>
    <tr><th>Contraseña</th><td><?= str_repeat('*', strlen($dbPass)) ?: '(vacía)' ?></td></tr>
</table>

<p><strong>⚠ Atención:</strong> Esta operación borrará todas las tablas existentes y volverá a crearlas.</p>

<form method="post">
    <button type="submit">🚀 Instalar / Reinstalar base de datos</button>
</form>

<?php if ($mensaje): ?>
<p style="margin-top:20px;"><a href="../public/index.php" style="color:#f5c842;">→ Ir a la aplicación</a></p>
<?php endif; ?>
</body>
</html>
