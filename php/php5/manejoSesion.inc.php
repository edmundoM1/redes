<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['login'])) {
    // No hay sesión, redirigir al login

    // Si la petición se originó desde dentro de la carpeta del módulo (ahora 'app_module'),
    // usamos la ruta relativa correcta hacia el `login.html` que está en la raíz.
    $loginPath = (strpos($_SERVER['SCRIPT_NAME'], '/app_module/') !== false)
                 ? '../login.html'
                 : 'login.html';

    header("Location: $loginPath");
    exit();
}

?>
