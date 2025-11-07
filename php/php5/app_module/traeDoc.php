<?php
// Protección de sesión
include('../manejoSesion.inc.php');

// Ahora la implementación lee la BD y devuelve el PDF si está almacenado como BLOB
include('../datos_conexion.php');

// Validar parámetro (seguimos aceptando 'cod_deposito' por compatibilidad)
if (!isset($_GET['cod_deposito']) || trim($_GET['cod_deposito']) === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Parámetro cod_deposito requerido.';
    exit;
}

$cod_deposito = $_GET['cod_deposito'];

try {
    $pdo_local = obtenerConexion();
    if (!$pdo_local) throw new Exception('No se pudo conectar a la BD');

    $stmt = $pdo_local->prepare('SELECT documento FROM empleado WHERE legajo = :legajo');
    $stmt->bindParam(':legajo', $cod_deposito);
    $stmt->execute();
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fila || $fila['documento'] === null) {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>No PDF</title></head><body>';
        echo '<h2>No hay documento PDF asociado al legajo: ' . htmlspecialchars($cod_deposito) . '</h2>';
        echo '<p>Registro no encontrado o sin documento.</p>';
        echo '</body></html>';
        exit;
    }

    $pdf = $fila['documento'];
    if (is_resource($pdf)) {
        // PDO LOB may return stream
        $pdf = stream_get_contents($pdf);
    }

    header('Content-Type: application/pdf');
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $cod_deposito);
    header('Content-Disposition: inline; filename="empleado_' . $safeName . '.pdf"');
    header('Content-Length: ' . strlen($pdf));

    echo $pdf;
    exit;

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al recuperar documento: ' . $e->getMessage();
    exit;
}
?>
