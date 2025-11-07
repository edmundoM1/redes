<?php
// Protección de sesión
include('../manejoSesion.inc.php');

include('../datos_conexion.php');

// Habilitar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla, solo en logs

// Simular demora breve
sleep(1);

// Recibir datos del formulario (nombres adaptados a empleados)
$legajo_original = isset($_POST['legajo_original']) ? trim($_POST['legajo_original']) : '';
$cod_tipo = isset($_POST['cod_tipo']) ? trim($_POST['cod_tipo']) : '';
$apellido_y_nombres = isset($_POST['apellido_y_nombres']) ? trim($_POST['apellido_y_nombres']) : '';
$sueldo_basico = isset($_POST['sueldo_basico']) ? trim($_POST['sueldo_basico']) : '';
$fecha_de_ingreso = isset($_POST['fecha_de_ingreso']) ? trim($_POST['fecha_de_ingreso']) : '';
$dni = isset($_POST['dni']) ? trim($_POST['dni']) : '';

if ($legajo_original === '' || $cod_tipo === '' || $apellido_y_nombres === '') {
    echo "Error: campos obligatorios faltantes (legajo_original, cod_tipo o apellido_y_nombres).";
    exit();
}

try {
    $pdo_local = obtenerConexion();
    if (!$pdo_local) throw new Exception('No se pudo conectar a la BD');

    // Si se subió un archivo, leerlo
    $documento_blob = null;
    if (isset($_FILES['archivoDocumento']) && $_FILES['archivoDocumento']['size'] > 0) {
        $archivo = $_FILES['archivoDocumento'];
        $documento_blob = file_get_contents($archivo['tmp_name']);
    }

    // Construir UPDATE dinámico según si hay documento
    if ($documento_blob !== null) {
        $sql = 'UPDATE empleado SET area_de_desempeno = :area, apellido_y_nombres = :apellido, sueldo_basico = :sueldo, fecha_de_ingreso = :fecha, dni = :dni, documento = :documento WHERE legajo = :legajo';
        $stmt = $pdo_local->prepare($sql);
        $stmt->bindParam(':documento', $documento_blob, PDO::PARAM_LOB);
    } else {
        $sql = 'UPDATE empleado SET area_de_desempeno = :area, apellido_y_nombres = :apellido, sueldo_basico = :sueldo, fecha_de_ingreso = :fecha, dni = :dni WHERE legajo = :legajo';
        $stmt = $pdo_local->prepare($sql);
    }

    $stmt->bindParam(':area', $cod_tipo);
    $stmt->bindParam(':apellido', $apellido_y_nombres);
    $stmt->bindValue(':sueldo', $sueldo_basico === '' ? null : floatval($sueldo_basico));
    $stmt->bindParam(':fecha', $fecha_de_ingreso);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':legajo', $legajo_original);

    $stmt->execute();
    
    $filasAfectadas = $stmt->rowCount();
    
    if ($filasAfectadas > 0) {
        echo "Registro actualizado correctamente (BD). Filas afectadas: " . $filasAfectadas;
    } else {
        echo "Advertencia: No se actualizó ningún registro. Verifique que el legajo existe: " . htmlspecialchars($legajo_original);
    }

} catch (Exception $e) {
    echo "Error al actualizar en BD: " . $e->getMessage();
}
?>
