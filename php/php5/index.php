<?php
/**
 * Punto de entrada principal de la aplicación
 * Evalúa si hay sesión activa desde la primera línea
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si existe sesión activa
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['login'])) {
    // No hay sesión, redirigir al formulario de login
    header('Location: login.html');
    exit();
}

// Si llegamos aquí, hay sesión válida - redirigir a la página de bienvenida
header('Location: ingreso_sistema.php');
exit();
?>
