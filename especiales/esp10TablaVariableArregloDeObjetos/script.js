let sampleEmployees = [];


let employees = [];

const btnLoad = document.getElementById("btnLoad");
const btnClear = document.getElementById("btnClear");
const tableBody = document.getElementById("tableBody");
const tableFoot = document.getElementById("tableFoot");

async function loadEmployees() {
  try {
    const response = await fetch('../EjercicioJson/maestro_empleados.json');
    if (!response.ok) {
      throw new Error(`Error al cargar empleados: ${response.status}`);
    }
    const data = await response.json();
    
    sampleEmployees = data.empleado.map(emp => ({
      legajo: emp.legajo,
      apellido: emp.apellido_y_nombres,
      dni: emp.dni,
      fechaIngreso: emp.fecha_de_ingreso,
      area: emp.area_de_desempeno,
      sueldo: emp.sueldo_basico,
      foto: emp.foto_empleado
    }));
    
    console.log('Empleados cargados exitosamente:', sampleEmployees.length);
  } catch (error) {
    console.error('Error al cargar empleados:', error);
    alert('Error al cargar los datos de empleados. Verifique que el archivo JSON esté disponible.');
  }
}

function formatCurrency(value) {
  return value.toLocaleString("es-AR", {
    style: "currency",
    currency: "ARS",
    minimumFractionDigits: 2,
  });
}


function renderTable() {
  tableBody.innerHTML = "";
  let totalSueldos = 0;
  employees.forEach((emp) => {
    const tr = document.createElement("tr");
    totalSueldos += Number(emp.sueldo);
    tr.innerHTML = `
      <td>${emp.legajo}</td>
      <td>${emp.apellido}</td>
      <td>${emp.dni}</td>
      <td>${emp.fechaIngreso}</td>
      <td>${emp.area}</td>
      <td>${formatCurrency(emp.sueldo)}</td>
      <td><img src="${emp.foto}" alt="Foto de ${emp.apellido}"></td>
    `;
    tableBody.appendChild(tr);
  });
  if (employees.length === 0) {
    tableFoot.innerHTML = `
      <tr>
        <td colspan="7">No hay datos cargados</td>
      </tr>
    `;
  } else {
    tableFoot.innerHTML = "";
    const resumen = document.createElement("tr");
    
    const getColspan = () => {
      if (window.innerWidth <= 480) return 4;
      if (window.innerWidth <= 768) return 5;
      return 7; 
    };
    
    resumen.innerHTML = `
      <td colspan="${getColspan()}">Total de empleados: ${employees.length} &nbsp;–&nbsp; Suma de sueldos: ${formatCurrency(totalSueldos)}</td>
    `;
    tableFoot.appendChild(resumen);
  }
}


btnLoad.addEventListener("click", async () => {
  if (employees.length === 0) {

    if (sampleEmployees.length === 0) {
      await loadEmployees();
    }
    employees = sampleEmployees.map((e) => ({ ...e }));
    renderTable();
  }
});

btnClear.addEventListener("click", () => {
  employees = [];
  renderTable();
});


async function initializeApp() {

  renderTable();
  
  console.log('Aplicación inicializada correctamente');
}


document.addEventListener('DOMContentLoaded', initializeApp);

window.addEventListener('resize', () => {
  if (employees.length > 0) {
    renderTable();
  }
});