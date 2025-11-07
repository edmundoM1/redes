<?php
// datos_conexion.php - ejemplo para XAMPP local sin variables de entorno
// Este archivo exporta tanto un PDO ($pdo) como funciones utilitarias para
// autenticación e incremento de contador de sesiones, compatibles con la
// implementación de la carpeta php/php5.

$host = '127.0.0.1';
$port = 3306;
$dbname = 'galvis_demo';
$user = 'root';
$pass = ''; // si tu XAMPP utiliza contraseña para root, ponla aquí

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Crear conexión PDO global (para compatibilidad con código existente)
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // En desarrollo mostrar el mensaje; en producción se debería loguear
    // y devolver un mensaje genérico.
    $pdo = null;
}

// --------------------------------------------------
// Funciones compatibles con php5/datos_conexion.php
// --------------------------------------------------
function obtenerConexion() {
    global $host, $port, $dbname, $user, $pass, $options;

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log('Error obtenerConexion: ' . $e->getMessage());
        return null;
    }
}

/**
 * autenticar_usuario: intenta autenticar contra la tabla `usuarios`.
 * Retorna array con campos: id_usuario, login, apellido, nombres, contador_sesiones
 * o false si no existe/contraseña incorrecta.
 */
function autenticar_usuario($login, $passwordUsuario) {
    try {
        $pdo_local = obtenerConexion();
        if (!$pdo_local) return false;

        $passwordHash = hash('sha256', $passwordUsuario);

        $sql = "SELECT id_usuario, login, apellido, nombres, contador_sesiones, password
                FROM usuarios
                WHERE login = :login AND password = :password";

        $stmt = $pdo_local->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            // No retornar la columna password
            unset($usuario['password']);
            return $usuario;
        }
        return false;
    } catch (PDOException $e) {
        error_log('ERROR autenticación: ' . $e->getMessage());
        return false;
    }
}

function incrementar_contador_sesiones($id_usuario) {
    try {
        $pdo_local = obtenerConexion();
        if (!$pdo_local) return false;

        $sql = "UPDATE usuarios SET contador_sesiones = contador_sesiones + 1 WHERE id_usuario = :id_usuario";
        $stmt = $pdo_local->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        error_log('Error incrementar_contador_sesiones: ' . $e->getMessage());
        return false;
    }
}

function obtener_contador_sesiones($id_usuario) {
    try {
        $pdo_local = obtenerConexion();
        if (!$pdo_local) return 0;

        $sql = "SELECT contador_sesiones FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $pdo_local->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? intval($row['contador_sesiones']) : 0;
    } catch (PDOException $e) {
        error_log('Error obtener_contador_sesiones: ' . $e->getMessage());
        return 0;
    }
}

?>