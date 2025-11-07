<?php
if (isset($_POST['clave'])) {
    
    $clave_ingresada = $_POST['clave'] ?? ''; 

    sleep(5); 

    $clave_md5 = md5($clave_ingresada);
    $clave_sha1 = sha1($clave_ingresada); 
    
    $respuesta_html = '
        <p>Request_method: POST</p><br>
        <p>Clave: ' . $clave_ingresada . '</p><br>
        <br>
        Clave encriptada en md5 (128 bits o 16 pares hexadecimales):<br>
        ' . $clave_md5 . '<br>
        <br>
        Clave encriptada en sha1 (160 bits o 20 pares hexadecimales):<br>
        ' . $clave_sha1 . '
    ';

    echo $respuesta_html;
    
    exit; 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AJAX con Retardo y Encriptación (Archivo Único)</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1 class="titulo">AJAX con Retardo y Encriptación (Archivo Único)</h1>
    <div class="contenedor">
        <form id="form-encriptar" class="seccion" action="" onsubmit="return false;">
            <div class="bloque" id="bloque-entrada">
                <h3 class="titulo">Datos de entrada</h3>
                <input type="text" id="clave-input" value="" required class="input-text">
            </div>

            <div class="bloque" id="bloque-boton">
                <h3 class="titulo">Acción</h3>
                <button id="btn-encriptar" class="btn-encriptar">Encriptar</button>
            </div>

            <div class="bloque" id="bloque-estado">
                <h3 class="titulo">Estado del requerimiento</h3>
                <div id="estado-requerimiento" class="estado-text"></div>
            </div>

            <div class="bloque" id="bloque-resultado">
                <h3 class="titulo">Resultado</h3>
                <div id="resultado-contenido"></div>
            </div>

           
        </form>
        
        <button class="btn-volver"><a href="../" class="volver-link" >Volver atras</a></button
    </div>
    <script>
    const formEncriptar = document.getElementById('form-encriptar');
    const btnEncriptar = document.getElementById('btn-encriptar');
    const inputClave = document.getElementById('clave-input');
    const spanEstado = document.getElementById('estado-requerimiento');
    const divResultado = document.getElementById('resultado-contenido');
        
        const urlRespuesta = '<?php echo basename(__FILE__); ?>'; 
        formEncriptar.addEventListener('submit', (ev) => {
            ev.preventDefault();
        });

        btnEncriptar.addEventListener('click', () => {
            if (inputClave.value.trim() === '') {
                alert('Debe ingresar una clave para encriptar.');
                return;
            }

            spanEstado.innerHTML = 'ESPERANDO RESPUESTA ..'; 
            divResultado.innerHTML = 'Esperando respuesta ..'; 

            const claveAEncriptar = inputClave.value;
            const data = new URLSearchParams();
            data.append('clave', claveAEncriptar);

            alert(`localhost dice\nComo pienso usar el metodo POST en el req HTTP, esto viajara en el body del mismo con el formato clasico:\nclave=${claveAEncriptar}&variable=valor`);

            const options = {
                method: 'POST',
                body: data 
            };

            fetch(urlRespuesta, options)
                .then(respuesta => respuesta.text())
                .then(textoDeRespuesta => {
                    alert(textoDeRespuesta);
                    divResultado.innerHTML = textoDeRespuesta;
                    spanEstado.innerHTML = 'CUMPLIDO'; 
                })
                .catch(error => {
                    spanEstado.innerHTML = 'ERROR';
                    console.error('Error en fetch:', error);
                });
        });
    </script> 
</body>
</html>