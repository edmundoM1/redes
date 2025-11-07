<?php
include '../manejoSesion.inc.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABM Empleados</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div id="contenedor" class="app-container contenedorActivo">
        <header class="header">
            <div class="header-top">
                <h1>Gestión de Empleados - ABM</h1>
                <div class="header-actions">
                    <div class="orden-section">
                        <label for="selectOrden">Orden:</label>
                            <input type="text" id="selectOrden" placeholder="campo" value="legajo">
                         
                    </div>
                        <button type="button" id="btnMenu" class="btn-menu" aria-expanded="false" aria-controls="actionList">☰</button>
                        <div id="actionList" class="actions">
                        <button type="button" id="btnCargar">Cargar datos</button>
                        <button type="button" id="btnVaciar">Vaciar datos</button>
                        <button type="button" id="btnLimpiarFiltros">Limpiar filtros</button>
                        <button type="button" id="btnAlta">Alta registro</button>
                        <button type="button" id="btnCerrarSesion" style="background-color: #dc3545;">Cerrar Sesión</button>
                        </div>
                </div>
            </div>
            
            <div class="filtros-grid">
                <select id="filtro_cod_tipo">
                    <option value="">Todas</option>
                </select>
            </div>
            
            <div style="text-align: center; margin-top: 12px; color: #fff; font-size: 1.1em;">
                <strong>Total Registros: <span id="totalRegistros">0</span></strong>
            </div>
        </header>

        <main class="main-content">
            <div class="table-container">
                <table id="tablaEmpleados">
                    <thead>
                        <tr>
                            <th data-col="legajo">Legajo</th>
                            <th data-col="area_de_desempeno">Área</th>
                            <th data-col="apellido_y_nombres">Apellido y Nombres</th>
                            <th data-col="sueldo_basico">Sueldo</th>
                            <th data-col="fecha_de_ingreso" class="hidden-sm">Fecha Ingreso</th>
                            <th data-col="dni" class="hidden-sm">DNI</th>
                            <th data-col="tiene_documento">Documento</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyDepositos">
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="footer">
            <p> Pie</p>
        </footer>
    </div>

    <div id="ventanaModal" class="ventanaModalApagado">
        <div class="modal-content">
            <button id="cerrarModal">✖</button>
            <h2 id="tituloModal">Formulario</h2>
            <form id="formularioABM" method="POST" onsubmit="return false;">
                <input type="hidden" id="tipoOperacion" name="tipoOperacion" value="">
                <input type="hidden" id="legajo_original" name="legajo_original" value="">
                
                <label for="legajo">Legajo:</label>
                <input type="text" id="legajo" name="legajo" required>
                
                <label for="cod_tipo">Área:</label>
                <select id="cod_tipo" name="cod_tipo" required>
                    <option value="">-- Seleccione --</option>
                </select>
                
                <label for="apellido_y_nombres">Apellido y Nombres:</label>
                <input type="text" id="apellido_y_nombres" name="apellido_y_nombres" required>
                
                <label for="sueldo_basico">Sueldo Básico:</label>
                <input type="number" id="sueldo_basico" name="sueldo_basico" step="0.01" required>
                
                <label for="fecha_de_ingreso">Fecha de Ingreso:</label>
                <input type="date" id="fecha_de_ingreso" name="fecha_de_ingreso" required>
                
                <label for="dni">DNI:</label>
                <input type="number" id="dni" name="dni" required>
                
                <label for="archivoDocumento">Documento PDF (opcional):</label>
                <input type="file" id="archivoDocumento" name="archivoDocumento" accept=".pdf,.PDF">
                <small style="color: #666;">Archivo actual: <strong id="nombreArchivoActual">Ninguno</strong></small>
                
                <br><br>
                <button type="submit" id="btnEnviarForm" disabled>Enviar</button>
            </form>
        </div>
    </div>

    <div id="ventanaModalRespuesta" class="ventanaModalApagado">
        <div class="modal-content">
            <button id="cerrarModalRespuesta">✖</button>
            <h2>Respuesta del Servidor</h2>
            <div id="contenidoRespuesta" style="white-space: pre-line;">
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
