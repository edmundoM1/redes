<?php
/**
 * Script de autenticación de usuarios
 * Punto 3: Evalúa si ya hay sesión iniciada (por si el usuario hace reload)
 */

// Autenticación: preferimos usar la BD si la tabla `usuarios` existe.
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'datos_conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PUNTO 3: Verificar si ya hay sesión activa (por si el usuario recarga la página)
if (isset($_SESSION['usuario_id']) && isset($_SESSION['login'])) {
    // Ya hay sesión activa, redirigir a la aplicación (módulo en carpeta app_module)
    header('Location: app_module/index.php');
    exit();
}

// Si no es POST, redirigir al login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit();
}

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$passwordUsuario = isset($_POST['passwordlogin']) ? trim($_POST['passwordlogin']) : '';

if (empty($login) || empty($passwordUsuario)) {
    echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Login - Error</title></head><body>";
    echo "<h2>Error: campos vacíos</h2>";
    echo "<p><a href=\"login.html\">Volver al login</a></p>";
    echo "</body></html>";
    exit();
}

// Intentar autenticación contra la BD si la función existe
$usuario = false;
if (function_exists('autenticar_usuario')) {
    $usuario = autenticar_usuario($login, $passwordUsuario);
}

if ($usuario !== false && is_array($usuario)) {
    // Autenticación por BD exitosa
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['login'] = $usuario['login'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['nombres'] = $usuario['nombres'];

    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);

    // Incrementar contador en BD si la función existe
    if (function_exists('incrementar_contador_sesiones')) {
        incrementar_contador_sesiones($usuario['id_usuario']);
        // Leer el nuevo valor
        if (function_exists('obtener_contador_sesiones')) {
            $_SESSION['contador_sesiones'] = obtener_contador_sesiones($usuario['id_usuario']);
        } else {
            $_SESSION['contador_sesiones'] = isset($usuario['contador_sesiones']) ? intval($usuario['contador_sesiones']) + 1 : 1;
        }
    } else {
        // Si no existe la función, fallback al valor en la fila (si existe)
        $_SESSION['contador_sesiones'] = isset($usuario['contador_sesiones']) ? intval($usuario['contador_sesiones']) + 1 : 1;
    }

} else {
    // Fallback: autenticación local simple (si la BD no está disponible)
    $validUsers = [
        'admin' => ['password' => 'admin123', 'id' => 1, 'apellido' => 'Administrador', 'nombres' => 'Sistema']
    ];

    if (!isset($validUsers[$login]) || $validUsers[$login]['password'] !== $passwordUsuario) {
        echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Login fallido</title></head><body>";
        echo "<h2>Credenciales inválidas</h2>";
        echo "<p>El usuario o la contraseña son incorrectos.</p>";
        echo "<p><a href=\"login.html\">Volver al login</a></p>";
        echo "</body></html>";
        exit();
    }

    // Autenticación local exitosa - crear/actualizar sesión y contador persistente simple
    $userInfo = $validUsers[$login];
    $_SESSION['usuario_id'] = $userInfo['id'];
    $_SESSION['login'] = $login;
    $_SESSION['apellido'] = $userInfo['apellido'];
    $_SESSION['nombres'] = $userInfo['nombres'];

    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);

    // Persistir contador simple por usuario en un JSON local (fallback)
    $counterFile = __DIR__ . '/contador_sesiones.json';
    $counters = [];
    if (file_exists($counterFile)) {
        $raw = file_get_contents($counterFile);
        $counters = json_decode($raw, true) ?: [];
    }

    $prev = isset($counters[$login]) ? intval($counters[$login]) : 0;
    $now = $prev + 1;
    $counters[$login] = $now;
    file_put_contents($counterFile, json_encode($counters, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $_SESSION['contador_sesiones'] = $now;

}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Empleados sistema</title>

</head>
<body>
    <div>
        <p>Login: <?php echo htmlspecialchars($_SESSION['login']); ?></p>
        <p>Session ID: <?php echo session_id(); ?></p>
        <p>Contador de sesiones: <?php echo intval($_SESSION['contador_sesiones']); ?></p>

        <h3>Acciones</h3>
        <ul>
            <li><a href="app_module/index.php">Entrar al CRUD (Módulo 1)</a></li>
            <li><a href="logout.php">Cerrar sesión (session_destroy)</a></li>
        </ul>
    </div>
</body>
</html>

<?php
exit();
?>
