<?php
// Protección de sesión
include('../manejoSesion.inc.php');

// Alta usando BD: insertar fila en tabla `empleado`.
include('../datos_conexion.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$resultado = '';
// Simular pequeña demora para parity UX
sleep(1);

// Recibir y normalizar datos del formulario (campos adaptados a empleados)
$legajo = isset($_POST['legajo']) ? trim($_POST['legajo']) : '';
$area = isset($_POST['cod_tipo']) ? trim($_POST['cod_tipo']) : '';
$apellido_nombres = isset($_POST['apellido_y_nombres']) ? trim($_POST['apellido_y_nombres']) : '';
$sueldo = isset($_POST['sueldo_basico']) ? trim($_POST['sueldo_basico']) : '';
$fecha_ingreso = isset($_POST['fecha_de_ingreso']) ? trim($_POST['fecha_de_ingreso']) : '';
$dni = isset($_POST['dni']) ? trim($_POST['dni']) : '';

// Validaciones básicas
if ($legajo === '' || $area === '' || $apellido_nombres === '') {
    echo "Error: campos obligatorios faltantes (legajo, area o apellido_y_nombres).";
    exit();
}
if ($sueldo !== '' && !is_numeric($sueldo)) { echo "Error: sueldo_basico debe ser numérico."; exit(); }

try {
    $pdo_local = obtenerConexion();
    if (!$pdo_local) throw new Exception('No se pudo conectar a la BD');

    // Verificar duplicado por PK legajo
    $check = $pdo_local->prepare('SELECT COUNT(*) AS cnt FROM empleado WHERE legajo = :legajo');
    $check->bindParam(':legajo', $legajo, PDO::PARAM_STR);
    $check->execute();
    $row = $check->fetch(PDO::FETCH_ASSOC);
    if ($row && intval($row['cnt']) > 0) { echo "Error: ya existe un registro con legajo $legajo"; exit(); }

    $sql = 'INSERT INTO empleado (legajo, area_de_desempeno, apellido_y_nombres, sueldo_basico, fecha_de_ingreso, dni, foto_empleado, documento) 
            VALUES (:legajo, :area, :apellido_nombres, :sueldo, :fecha_ingreso, :dni, :foto, :documento)';

    $stmt = $pdo_local->prepare($sql);
    $stmt->bindParam(':legajo', $legajo);
    $stmt->bindParam(':area', $area);
    $stmt->bindParam(':apellido_nombres', $apellido_nombres);
    $stmt->bindValue(':sueldo', $sueldo === '' ? null : floatval($sueldo));
    $stmt->bindParam(':fecha_ingreso', $fecha_ingreso);
    $stmt->bindParam(':dni', $dni);

    // manejar foto/documento
    $foto_url = '';
    $documento_blob = null;
    if (isset($_FILES['archivoDocumento']) && $_FILES['archivoDocumento']['size'] > 0) {
        $archivo = $_FILES['archivoDocumento'];
        $contenido = file_get_contents($archivo['tmp_name']);
        $documento_blob = $contenido; // store as blob
    }

    $stmt->bindParam(':foto', $foto_url);
    if ($documento_blob !== null) {
        $stmt->bindParam(':documento', $documento_blob, PDO::PARAM_LOB);
    } else {
        $stmt->bindValue(':documento', null, PDO::PARAM_NULL);
    }

    $stmt->execute();

    echo "Registro agregado correctamente (BD).";

} catch (Exception $e) {
    echo "Error al insertar en BD: " . $e->getMessage();
}
?>
