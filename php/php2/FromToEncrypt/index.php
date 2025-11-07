<?php
if (isset($_POST["varDeEntrada"])) {
    $varDeEntrada = $_POST["varDeEntrada"];
    $claveEncriptada = hash('sha256', $varDeEntrada); 
    $claveEncriptadamd5 = md5($varDeEntrada);

    echo "<h2>Datos Procesados</h2>";
    echo "Valor recibido: " . htmlspecialchars($varDeEntrada) . "<br>";
    echo "<h2>Clave encriptada (SHA256):</h2> " . $claveEncriptada . "<p> 256 bits o 32 octetos o 32 pares hexadecimales </p> <br>";
    echo "<h2>Clave encriptada (MD5):</h2> " . $claveEncriptadamd5 . "<p> 128 bits o 16 octetos o 16 pares hexadecimales </p> <br>";

    $objDatos = new stdClass();
    $objDatos->entrada = $varDeEntrada;
    $objDatos->encriptada = $claveEncriptada;
    $objDatos->encriptada = $claveEncriptadamd5;

    echo "<button class='volver-atras'><a class='volver-atras' href='./index.php'>Volver atras</a></button>";
} else {
?>
<html>
<head>
    <title>Formulario</title>
    <link rel="stylesheet" href="../../styles.css">
</head>
<body>
    <h1>Envío de Formulario</h1>
    <form method="post" action="">
        <label>Ingrese un valor:</label>
        <input type="text" name="varDeEntrada" required>
        <button class="submit" type="submit">Enviar</button>
    </form>
    <button class="volver-atras">
        <a class="volver-atras" href="../index.php">Volver atras</a>
    </button>
</body>
</html>
<?php
}
?>