<?php
// Protección de sesión
include('../manejoSesion.inc.php');

// Intentar leer los datos desde un JSON local (adaptado a los archivos adjuntos).
// Rutas consideradas (orden de preferencia):
// 1) ../area.json (dentro del workspace)
// 2) Ruta absoluta donde se colocó el JSON en las attachments
$possiblePaths = [
    __DIR__ . '/../area.json',
    'c:\\xampp\\htdocs\\edmundo\\especiales\\EjercicioJson\\area.json'
];

$jsonPath = null;
foreach ($possiblePaths as $p) {
    if (file_exists($p) && is_readable($p)) {
        $jsonPath = $p;
        break;
    }
}

// Obtener áreas desde la BD (tabla `area`). No usar JSON como fuente primaria.
include '../datos_conexion.php';

try {
    $pdo_local = obtenerConexion();
    if (!$pdo_local) throw new Exception('No se pudo conectar a BD');

    $sql = "SELECT cod_area AS cod, descripcion FROM area ORDER BY cod_area";
    $stmt = $pdo_local->prepare($sql);
    $stmt->execute();

    $tipos = [];
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipos[] = $fila;
    }

    echo json_encode(['tiposDeposito' => $tipos], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
