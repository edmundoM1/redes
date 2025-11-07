<?php
// Protección de sesión
include('../manejoSesion.inc.php');

// Incluir datos de conexión
include('../datos_conexion.php');

// Habilitar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla, solo en logs

// Simular demora
sleep(1);

// Recibir legajo a borrar
$legajo = isset($_POST['legajo']) ? trim($_POST['legajo']) : '';
if ($legajo === '') {
    echo "Error: no se recibió legajo a eliminar.";
    exit();
}

try {
    $pdo_local = obtenerConexion();
    if (!$pdo_local) throw new Exception('No se pudo conectar a la BD');

    $stmt = $pdo_local->prepare('DELETE FROM empleado WHERE legajo = :legajo');
    $stmt->bindParam(':legajo', $legajo);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo "No se encontró el empleado con legajo $legajo.";
    } else {
        echo "El empleado $legajo ha sido eliminado correctamente (BD).";
    }

} catch (Exception $e) {
    echo "Error al eliminar en BD: " . $e->getMessage();
}
?>
