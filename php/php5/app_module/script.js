let tipos = [];
let empleadosActuales = [];
let datosOriginalesForm = {};
let ordenActual = { campo: 'legajo', direccion: 'ASC' };

window.addEventListener('DOMContentLoaded', () => {
    cargarTiposDeposito();
    configurarEventos();
});

function configurarEventos() {
    const selectOrden = document.getElementById('selectOrden');
    const btnToggle = document.getElementById('btnToggleDireccion');
    const btnMenu = document.getElementById('btnMenu');
    const headerActions = document.querySelector('.header-actions');

    // Agregar event listeners solo si los elementos existen
    const btnCargar = document.getElementById('btnCargar');
    if (btnCargar) btnCargar.addEventListener('click', cargarDepositos);
    
    const btnVaciar = document.getElementById('btnVaciar');
    if (btnVaciar) btnVaciar.addEventListener('click', vaciarTabla);
    
    const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    if (btnLimpiarFiltros) btnLimpiarFiltros.addEventListener('click', limpiarFiltros);
    
    const btnAlta = document.getElementById('btnAlta');
    if (btnAlta) btnAlta.addEventListener('click', abrirFormularioAlta);
    
    const btnCerrarSesion = document.getElementById('btnCerrarSesion');
    if (btnCerrarSesion) btnCerrarSesion.addEventListener('click', cerrarSesion);
    
    document.querySelectorAll('th[data-col]').forEach(th => {
        th.addEventListener('click', () => {
            const campo = th.dataset.col;
            
            if (ordenActual.campo === campo) {
                ordenActual.direccion = ordenActual.direccion === 'ASC' ? 'DESC' : 'ASC';
            } else {
                ordenActual.campo = campo;
                ordenActual.direccion = 'ASC';
            }
            
            if (selectOrden) selectOrden.value = campo;
            
            actualizarIndicadoresOrden();
            
            cargarDepositos();
        });
        
        th.style.cursor = 'pointer';
    });
    
    if (selectOrden) {
        selectOrden.addEventListener('input', (e) => {
            ordenActual.campo = e.target.value || 'legajo';
            actualizarIndicadoresOrden();
        });
        selectOrden.addEventListener('change', (e) => {
            ordenActual.campo = e.target.value || 'legajo';
            actualizarIndicadoresOrden();
        });
    }

    if (btnToggle) {
        btnToggle.textContent = ordenActual.direccion === 'ASC' ? '▲' : '▼';
        btnToggle.addEventListener('click', () => {
            ordenActual.direccion = ordenActual.direccion === 'ASC' ? 'DESC' : 'ASC';
            btnToggle.textContent = ordenActual.direccion === 'ASC' ? '▲' : '▼';
            actualizarIndicadoresOrden();
            cargarDepositos();
        });
    }

    // Configurar botón de menú mobile
    if (btnMenu && headerActions) {
        btnMenu.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!expanded));
            headerActions.classList.toggle('actions-open');
        });
    }
    
    const cerrarModal = document.getElementById('cerrarModal');
    if (cerrarModal) cerrarModal.addEventListener('click', cerrarVentanaModal);
    
    const cerrarModalRespuesta = document.getElementById('cerrarModalRespuesta');
    if (cerrarModalRespuesta) cerrarModalRespuesta.addEventListener('click', cerrarVentanaModalRespuesta);
    
    const formularioABM = document.getElementById('formularioABM');
    if (formularioABM) formularioABM.addEventListener('submit', enviarFormulario);
    
    const inputs = document.querySelectorAll('#formularioABM input, #formularioABM select');
    inputs.forEach(input => {
        input.addEventListener('input', detectarCambiosFormulario);
        input.addEventListener('change', detectarCambiosFormulario);
    });
}

function cargarTiposDeposito() {
    fetch('salidaJsonTipos.php')
        .then(response => response.json())
        .then(data => {
            tipos = data.tiposDeposito;
            llenarSelectTipos();
        })
        .catch(error => {
            console.error('Error al cargar tipos:', error);
            // keep UX quiet, log to console
        });
}

function llenarSelectTipos() {
    const selectFiltro = document.getElementById('filtro_cod_tipo');
    selectFiltro.innerHTML = '<option value="">Todas</option>';
    tipos.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = `${tipo.cod} - ${tipo.descripcion}`;
        selectFiltro.appendChild(option);
    });
    
    const selectForm = document.getElementById('cod_tipo');
    selectForm.innerHTML = '<option value="">-- Seleccione --</option>';
    tipos.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = `${tipo.cod} - ${tipo.descripcion}`;
        selectForm.appendChild(option);
    });
}

function cargarDepositos() {
    const selectOrden = document.getElementById('selectOrden');
    if (selectOrden && selectOrden.value) {
        ordenActual.campo = selectOrden.value;
    }
    
    const formData = new URLSearchParams();
    formData.append('orden', ordenActual.campo);
    formData.append('direccion', ordenActual.direccion);
    formData.append('filtro_cod_tipo', document.getElementById('filtro_cod_tipo').value);
    
    console.debug('Variables que se envían al servidor:', formData.toString());
    
    fetch('salidaJsonEmpleados.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.debug('Respuesta del servidor:', data);

        if (data.empleados) {
            empleadosActuales = data.empleados;
            renderizarTabla(data.empleados);
            document.getElementById('totalRegistros').textContent = data.cuenta;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // leave console error for diagnostics
    });
}

function actualizarIndicadoresOrden() {
    document.querySelectorAll('th[data-col]').forEach(th => {
        th.classList.remove('orden-asc', 'orden-desc');
        const span = th.querySelector('.indicador-orden');
        if (span) span.remove();
    });
    
    const thActivo = document.querySelector(`th[data-col="${ordenActual.campo}"]`);
    if (thActivo) {
        const indicador = document.createElement('span');
        indicador.className = 'indicador-orden';
        indicador.textContent = ordenActual.direccion === 'ASC' ? ' ▲' : ' ▼';
        thActivo.appendChild(indicador);
        thActivo.classList.add(ordenActual.direccion === 'ASC' ? 'orden-asc' : 'orden-desc');
    }
}

// Funciones helper para generar iconos SVG
function getEditIcon() {
    return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
    </svg>`;
}

function getDeleteIcon() {
    return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="3 6 5 6 21 6"></polyline>
        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        <line x1="10" y1="11" x2="10" y2="17"></line>
        <line x1="14" y1="11" x2="14" y2="17"></line>
    </svg>`;
}

function renderizarTabla(empleados) {
    const tbody = document.getElementById('tbodyDepositos');
    tbody.innerHTML = '';
    
    empleados.forEach(emp => {
        const tr = document.createElement('tr');
        
        const tienePDF = emp.tiene_documento === 'SI';
        const btnPDFClass = tienePDF ? 'btn-pdf' : 'btn-pdf btn-pdf-disabled';
        const btnPDFText = tienePDF ? 'PDF' : 'PDF';
        
        tr.innerHTML = `
            <td>${emp.legajo}</td>
            <td>${emp.area_de_desempeno}</td>
            <td>${emp.apellido_y_nombres}</td>
            <td>${emp.sueldo_basico}</td>
            <td class="hidden-sm">${emp.fecha_de_ingreso}</td>
            <td class="hidden-sm">${emp.dni}</td>
            <td>
                <button class="btn-accion ${btnPDFClass}" onclick="verPDF('${emp.legajo}')" ${!tienePDF ? 'disabled' : ''}>${btnPDFText}</button>
            </td>
            <td class="acciones-cell">
                <button class="btn-icon btn-edit" title="Editar">
                    ${getEditIcon()}
                </button>
                <button class="btn-icon btn-delete" onclick="eliminarDeposito('${emp.legajo}')" title="Eliminar">
                    ${getDeleteIcon()}
                </button>
            </td>
        `;
        
        // Agregar event listener al botón de editar usando el objeto emp del closure
        const editBtn = tr.querySelector('.btn-edit');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                abrirFormularioModificacion(emp);
            });
        }
        
        tbody.appendChild(tr);
    });
}

function vaciarTabla() {
    document.getElementById('tbodyDepositos').innerHTML = '';
    document.getElementById('totalRegistros').textContent = '0';
    empleadosActuales = [];
}

function limpiarFiltros() {
    document.getElementById('filtro_cod_tipo').value = '';
}

function verPDF(codDeposito) {
    fetch(`traeDoc.php?cod_deposito=${encodeURIComponent(codDeposito)}`)
        .then(response => {
            if (!response.ok) {
                return response.text().then(txt => { throw new Error(txt || 'Error al recuperar documento'); });
            }

            const contentType = response.headers.get('Content-Type') || '';
            if (!contentType.includes('application/pdf')) {
                return response.text().then(txt => { throw new Error(txt || 'Respuesta inesperada del servidor'); });
            }

            return response.blob();
        })
        .then(blob => {
            console.debug('PDF recibido del servidor, abriendo ventana modal...');

            const url = URL.createObjectURL(blob);

            const contenido = document.getElementById('contenidoRespuesta');

            contenido.innerHTML = `
                <iframe src="${url}" width="100%" height="500px" style="border: 1px solid #ccc;"></iframe>
            `;

            abrirVentanaModalRespuesta();
        })
        .catch(error => {
            console.error('Error al cargar PDF:', error);
            alert('Error al cargar el documento: ' + (error.message || error));
        });
}

function abrirFormularioAlta() {
    document.getElementById('tituloModal').textContent = 'Alta de Empleado';
    document.getElementById('tipoOperacion').value = 'alta';
    
    document.getElementById('formularioABM').reset();
    document.getElementById('legajo').disabled = false;
    document.getElementById('legajo').readOnly = false;
    document.getElementById('legajo_original').value = '';
    document.getElementById('nombreArchivoActual').textContent = 'Ninguno';
    document.getElementById('btnEnviarForm').disabled = false;
    document.getElementById('btnEnviarForm').textContent = 'Enviar Alta';
    
    datosOriginalesForm = {};
    
    abrirVentanaModal();
}

function abrirFormularioModificacion(deposito) {
    // deposito is now empleado object
    document.getElementById('tituloModal').textContent = 'Modificación de Empleado';
    document.getElementById('tipoOperacion').value = 'modi';
    
    document.getElementById('legajo').value = deposito.legajo;
    document.getElementById('legajo').readOnly = true; 
    document.getElementById('legajo').style.backgroundColor = '#f0f0f0';
    document.getElementById('legajo_original').value = deposito.legajo; 
    
    // Establecer valores en el formulario
    document.getElementById('cod_tipo').value = deposito.area_de_desempeno || '';
    document.getElementById('apellido_y_nombres').value = deposito.apellido_y_nombres || '';
    
    // Normalizar sueldo_basico antes de ponerlo en el input
    let sueldoNormalizado = '';
    if (deposito.sueldo_basico !== null && deposito.sueldo_basico !== undefined && deposito.sueldo_basico !== '') {
        const numSueldo = parseFloat(deposito.sueldo_basico);
        if (!isNaN(numSueldo)) {
            sueldoNormalizado = numSueldo.toString();
        } else {
            sueldoNormalizado = String(deposito.sueldo_basico).trim();
        }
    }
    document.getElementById('sueldo_basico').value = sueldoNormalizado;
    
    document.getElementById('fecha_de_ingreso').value = deposito.fecha_de_ingreso || '';
    document.getElementById('dni').value = deposito.dni || '';
    document.getElementById('nombreArchivoActual').textContent = deposito.tiene_documento === 'SI' ? 'Documento cargado' : 'Ninguno';
    
    document.getElementById('btnEnviarForm').disabled = true; 
    document.getElementById('btnEnviarForm').textContent = 'Enviar Modificación';
    
    // Normalizar valores al guardarlos para comparación consistente (usando los mismos valores que pusimos en el form)
    datosOriginalesForm = {
        legajo: String(deposito.legajo || '').trim(),
        cod_tipo: String(deposito.area_de_desempeno || '').trim(),
        apellido_y_nombres: String(deposito.apellido_y_nombres || '').trim(),
        sueldo_basico: sueldoNormalizado,
        fecha_de_ingreso: String(deposito.fecha_de_ingreso || '').trim(),
        dni: String(deposito.dni || '').trim()
    };
    
    abrirVentanaModal();
    
    // Disparar evento de cambio después de cargar los datos para verificar estado inicial
    // Usar múltiples timeouts para asegurar que el DOM esté completamente actualizado
    setTimeout(() => {
        detectarCambiosFormulario();
        // También forzar un evento change en el select por si acaso
        const selectArea = document.getElementById('cod_tipo');
        if (selectArea) {
            selectArea.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }, 150);
    
    // Asegurar que los event listeners estén activos después de abrir el modal
    setTimeout(() => {
        const inputs = document.querySelectorAll('#formularioABM input, #formularioABM select');
        inputs.forEach(input => {
            // Remover listeners anteriores si existen
            input.removeEventListener('input', detectarCambiosFormulario);
            input.removeEventListener('change', detectarCambiosFormulario);
            // Agregar nuevos listeners
            input.addEventListener('input', detectarCambiosFormulario);
            input.addEventListener('change', detectarCambiosFormulario);
        });
        // Verificar estado inicial después de agregar listeners
        detectarCambiosFormulario();
    }, 200);
}

function detectarCambiosFormulario() {
    const tipoOp = document.getElementById('tipoOperacion').value;
    
    if (tipoOp === 'alta') {
        document.getElementById('btnEnviarForm').disabled = false;
        return;
    }
    
    // Si no hay datos originales, no hacer nada
    if (!datosOriginalesForm || Object.keys(datosOriginalesForm).length === 0) {
        return;
    }
    
    // Construir objeto con los campos actuales del formulario (empleados)
    const campos = ['legajo','cod_tipo','apellido_y_nombres','sueldo_basico','fecha_de_ingreso','dni'];
    const datosActuales = {};
    campos.forEach(k => {
        const el = document.getElementById(k);
        let valor = el ? el.value : '';
        if (valor === null || valor === undefined) {
            valor = '';
        } else {
            valor = String(valor).trim();
        }
        // Normalizar sueldo_basico: convertir a número y luego a string para comparación consistente
        if (k === 'sueldo_basico' && valor !== '') {
            const numVal = parseFloat(valor);
            if (!isNaN(numVal)) {
                valor = numVal.toString();
            }
        }
        datosActuales[k] = valor;
    });

    // Normalizar datosOriginalesForm también
    const datosOriginalesNormalizados = {};
    campos.forEach(k => {
        let valor = datosOriginalesForm[k];
        if (valor === null || valor === undefined) {
            valor = '';
        } else {
            valor = String(valor).trim();
        }
        // Normalizar sueldo_basico de la misma manera
        if (k === 'sueldo_basico' && valor !== '') {
            const numVal = parseFloat(valor);
            if (!isNaN(numVal)) {
                valor = numVal.toString();
            }
        }
        datosOriginalesNormalizados[k] = valor;
    });

    const archivoEl = document.getElementById('archivoDocumento');
    const archivo = archivoEl ? archivoEl.files.length > 0 : false;

    // Comparar campo por campo en lugar de JSON stringify para más robustez
    let hayCambios = false;
    
    // Primero verificar si hay archivo
    if (archivo) {
        hayCambios = true;
    } else {
        // Comparar cada campo
        for (let k of campos) {
            const actual = (datosActuales[k] || '').toString();
            const original = (datosOriginalesNormalizados[k] || '').toString();
            if (actual !== original) {
                hayCambios = true;
                break;
            }
        }
    }

    const btn = document.getElementById('btnEnviarForm');
    if (btn) {
        btn.disabled = !hayCambios;
        // Debugging (puedes quitar esto después)
        if (hayCambios) {
            console.log('Cambios detectados - botón habilitado');
        } else {
            console.log('Sin cambios - botón deshabilitado');
        }
    }
}

function enviarFormulario(e) {
    // Prevenir el envío tradicional del formulario
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    const tipoOp = document.getElementById('tipoOperacion').value;
    const codigo = document.getElementById('legajo').value;
    
    if (!tipoOp || !codigo) {
        alert('Error: datos incompletos');
        return false;
    }
    
    const mensaje = tipoOp === 'alta' 
        ? `¿Está seguro que desea insertar el registro ${codigo}?`
        : `¿Está seguro que desea modificar el registro ${codigo}?`;
    
    if (!confirm(mensaje)) {
        return false;
    }
    
    const formulario = document.getElementById('formularioABM');
    const formData = new FormData(formulario);
    const endpoint = tipoOp === 'alta' ? 'alta.php' : 'modi.php';
    
    console.log('Enviando formulario a:', endpoint);
    console.log('Datos:', Object.fromEntries(formData));
    
    fetch(endpoint, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        return response.text();
    })
    .then(respuesta => {
        console.log('Respuesta del servidor:', respuesta);
        
        // Verificar si la respuesta indica éxito
        const esExitoso = respuesta.toLowerCase().includes('correctamente') || 
                         respuesta.toLowerCase().includes('agregado') ||
                         respuesta.toLowerCase().includes('actualizado');
        
        if (esExitoso) {
            // Si fue exitoso, recargar la tabla para mostrar los cambios
            cargarDepositos();
            alert('Operación exitosa:\n' + respuesta);
        } else {
            // Si hay error, mostrar mensaje de error
            alert('Error:\n' + respuesta);
        }
        mostrarRespuestaServidor(respuesta);
        cerrarVentanaModal();
        return false;
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        alert('Error al procesar la operación: ' + error.message);
        return false;
    });
    
    return false;
}

function eliminarDeposito(codDeposito) {
    if (!confirm(`¿Está seguro que desea eliminar el empleado ${codDeposito}?`)) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('legajo', codDeposito);
    
    fetch('baja.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(respuesta => {
        alert('Respuesta del servidor:\n' + respuesta);
        mostrarRespuestaServidor(respuesta);
        cargarDepositos(); 
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el depósito');
    });
}

function abrirVentanaModal() {
    document.getElementById('ventanaModal').className = 'ventanaModalPrendido';
    document.getElementById('contenedor').className = 'app-container contenedorPasivo';
}

function cerrarVentanaModal() {
    document.getElementById('ventanaModal').className = 'ventanaModalApagado';
    document.getElementById('contenedor').className = 'app-container contenedorActivo';
}

function abrirVentanaModalRespuesta() {
    document.getElementById('ventanaModalRespuesta').className = 'ventanaModalPrendido';
    document.getElementById('contenedor').className = 'app-container contenedorPasivo';
}

function cerrarVentanaModalRespuesta() {
    document.getElementById('ventanaModalRespuesta').className = 'ventanaModalApagado';
    document.getElementById('contenedor').className = 'app-container contenedorActivo';
}

function mostrarRespuestaServidor(texto) {
    document.getElementById('contenidoRespuesta').innerHTML = texto;
    abrirVentanaModalRespuesta();
}

function cerrarSesion() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = '../logout.php';
    }
}
