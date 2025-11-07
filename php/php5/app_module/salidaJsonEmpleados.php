<?php
// Protección de sesión
include('../manejoSesion.inc.php');

// Trabajar exclusivamente con la BD: devolver empleados desde la tabla `empleado`.
include('../datos_conexion.php');

$resultado = '';
$debug_info = [];

try {
    $pdo_local = obtenerConexion();
    if (!$pdo_local) throw new Exception('No se pudo obtener conexión a la BD');

    // Recibir parámetros de ordenamiento y filtros (adaptados a empleados)
    $orden = isset($_POST['orden']) ? $_POST['orden'] : 'legajo';
    $direccion = isset($_POST['direccion']) ? strtoupper($_POST['direccion']) : 'ASC';
    $filtro_legajo = isset($_POST['filtro_legajo']) ? trim($_POST['filtro_legajo']) : '';
    $filtro_area = isset($_POST['filtro_cod_tipo']) ? trim($_POST['filtro_cod_tipo']) : '';
    $filtro_apellido = isset($_POST['filtro_apellido']) ? trim($_POST['filtro_apellido']) : '';

    // Campos válidos para ordenar (seguridad)
    $campos_validos = ['legajo','area_de_desempeno','apellido_y_nombres','sueldo_basico','fecha_de_ingreso','dni'];
    if (!in_array($orden, $campos_validos)) $orden = 'legajo';
    if ($direccion !== 'ASC' && $direccion !== 'DESC') $direccion = 'ASC';

    $sql = "SELECT legajo, area_de_desempeno, apellido_y_nombres, sueldo_basico, fecha_de_ingreso, dni, 
            CASE WHEN documento IS NOT NULL THEN 'SI' ELSE NULL END AS tiene_documento
            FROM empleado WHERE 1=1";

    if ($filtro_legajo !== '') $sql .= " AND legajo LIKE CONCAT('%', :filtro_legajo, '%')";
    if ($filtro_area !== '') $sql .= " AND area_de_desempeno = :filtro_area";
    if ($filtro_apellido !== '') $sql .= " AND apellido_y_nombres LIKE CONCAT('%', :filtro_apellido, '%')";

    $sql .= " ORDER BY " . $orden . " " . $direccion;

    $debug_info['sql_generado'] = $sql;

    $stmt = $pdo_local->prepare($sql);
    if ($filtro_legajo !== '') $stmt->bindParam(':filtro_legajo', $filtro_legajo, PDO::PARAM_STR);
    if ($filtro_area !== '') $stmt->bindParam(':filtro_area', $filtro_area, PDO::PARAM_STR);
    if ($filtro_apellido !== '') $stmt->bindParam(':filtro_apellido', $filtro_apellido, PDO::PARAM_STR);

    $stmt->execute();

    $empleados = [];
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $empleados[] = $fila;
    }

    $cuenta = count($empleados);

    echo json_encode([
        'empleados' => $empleados,
        'cuenta' => $cuenta,
        'resultado' => 'Datos cargados desde BD',
        'debug' => $debug_info
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'debug' => $debug_info], JSON_UNESCAPED_UNICODE);
}
?>
