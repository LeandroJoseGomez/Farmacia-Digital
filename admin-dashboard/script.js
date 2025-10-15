// =====================
// 🎨 MODO OSCURO
// =====================
const btnModo = document.getElementById("darkModeToggle");
if (btnModo) {
  btnModo.addEventListener("click", () => {
    document.body.classList.toggle("dark");
    btnModo.textContent = document.body.classList.contains("dark")
      ? "☀️ Modo Claro"
      : "🌙 Modo Oscuro";
  });
}

// =====================
// 📊 GRÁFICO DE BARRAS (Ventas Mensuales)
// =====================
const ctxVentas = document.getElementById("ventasChart");

// =====================
// 📈 DATOS DE MEDICAMENTOS ACTUALIZADOS (2025)
// =====================
let datosMedicamentos = {
  paracetamol: [95, 120, 135, 140, 150, 160, 170],
  omeprazol: [70, 85, 95, 100, 110, 115, 130],
  losartan: [60, 75, 80, 85, 95, 110, 120],
  metformina: [100, 105, 110, 120, 135, 140, 155],
  ibuprofeno: [65, 80, 90, 100, 95, 105, 110],
  amoxicilina: [50, 65, 70, 75, 80, 95, 100],
  enalapril: [55, 60, 65, 70, 80, 90, 100],
  salbutamol: [40, 50, 60, 70, 75, 80, 90],
  ranitidina: [30, 35, 40, 45, 50, 55, 60],
  atenolol: [45, 55, 60, 65, 70, 80, 85],
};


let chartVentas = new Chart(ctxVentas, {
  type: "bar",
  data: {
    labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul"],
    datasets: [
      {
        label: "Unidades Vendidas",
        data: datosMedicamentos.paracetamol,
        backgroundColor: "rgba(0,176,255,0.7)",
        borderRadius: 8,
      },
    ],
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    onClick: (evt, elements) => {
      if (elements.length > 0) {
        const index = elements[0].index;
        alert(`Mes seleccionado: ${chartVentas.data.labels[index]}`);
      }
    },
  },
});

// Cambiar datos dinámicamente
const selectProducto = document.getElementById("productoSelect");
if (selectProducto) {
  selectProducto.addEventListener("change", (e) => {
    const valor = e.target.value;
    chartVentas.data.datasets[0].data = datosMedicamentos[valor];
    chartVentas.update();
  });
}

// =====================
// 🥧 GRÁFICO DE PASTEL (Detalle de Ventas)
// =====================
const ctxPie = document.getElementById("ventasPieChart");
new Chart(ctxPie, {
  type: "doughnut",
  data: {
  labels: [
    "Paracetamol",
    "Omeprazol",
    "Losartán",
    "Metformina",
    "Ibuprofeno",
    "Amoxicilina",
    "Enalapril",
    "Salbutamol",
    "Ranitidina",
    "Atenolol",
  ],
  datasets: [
    {
      data: [125, 100, 90, 130, 85, 70, 95, 60, 40, 75],
      backgroundColor: [
        "#00B0FF",
        "#00E0C7",
        "#4DD0E1",
        "#26A69A",
        "#81C784",
        "#FFB74D",
        "#BA68C8",
        "#E57373",
        "#64B5F6",
        "#FFD54F",
      ],
      borderWidth: 2,
    },
  ],
},

  options: {
    cutout: "70%",
    plugins: {
      legend: { position: "bottom" },
    },
  },
});

// =====================================
// 🧠 PANEL EMERGENTE DE INFORMACIÓN DE MEDICAMENTOS
// =====================================
const infoPanel = document.getElementById("infoPanel");
const closePanel = document.getElementById("closePanel");
const medNombre = document.getElementById("medNombre");
const medUso = document.getElementById("medUso");
const medDemanda = document.getElementById("medDemanda");
const medPublico = document.getElementById("medPublico");
const medPrivado = document.getElementById("medPrivado");


// === DATOS DE PRODUCTOS PARA ANÁLISIS ===
const productos = [
  { nombre: "Paracetamol", categoria: "Analgésico", precio: 25, ventas: 84 },
  { nombre: "Omeprazol", categoria: "Gastrointestinal", precio: 20, ventas: 60 },
  { nombre: "Losartán", categoria: "Cardiovascular", precio: 15, ventas: 70 },
  { nombre: "Metformina", categoria: "Diabetes", precio: 10, ventas: 90 },
  { nombre: "Ibuprofeno", categoria: "Antiinflamatorio", precio: 25, ventas: 55 },
  { nombre: "Amoxicilina", categoria: "Antibiótico", precio: 30, ventas: 65 },
  { nombre: "Enalapril", categoria: "Cardiovascular", precio: 8, ventas: 75 },
  { nombre: "Salbutamol", categoria: "Respiratorio", precio: 40, ventas: 45 },
  { nombre: "Ranitidina", categoria: "Gastrointestinal", precio: 18, ventas: 25 },
  { nombre: "Atenolol", categoria: "Cardiovascular", precio: 12, ventas: 60 }
];

const tabla = document.getElementById("productosTabla");
let seleccionados = [];

// Generar tabla
productos.forEach((p, index) => {
  const row = document.createElement("tr");
  row.innerHTML = `
    <td>${p.nombre}</td>
    <td>${p.categoria}</td>
    <td>${p.precio}</td>
    <td><input type="checkbox" class="comparar-checkbox" data-index="${index}"></td>
  `;
  tabla.appendChild(row);
});

// Evento de selección
document.querySelectorAll(".comparar-checkbox").forEach(chk => {
  chk.addEventListener("change", (e) => {
    const index = parseInt(e.target.dataset.index);
    if (e.target.checked) {
      if (seleccionados.length < 2) {
        seleccionados.push(productos[index]);
      } else {
        e.target.checked = false;
        alert("Solo puedes comparar dos medicamentos a la vez.");
      }
    } else {
      seleccionados = seleccionados.filter(p => p !== productos[index]);
    }
    actualizarComparacion();
  });
});

const comparacionContenido = document.getElementById("comparacionContenido");
let comparacionChart;

// Actualizar información de comparación
function actualizarComparacion() {
  comparacionContenido.innerHTML = "";
  if (seleccionados.length === 0) {
    comparacionContenido.innerHTML = "<p>Selecciona dos medicamentos para comenzar la comparación.</p>";
    if (comparacionChart) comparacionChart.destroy();
    return;
  }

  seleccionados.forEach(p => {
    const div = document.createElement("div");
    div.innerHTML = `
      <strong>${p.nombre}</strong> — ${p.categoria}<br>
      💰 Precio: RD$${p.precio} | 📦 Ventas: ${p.ventas}M unidades
      <hr>
    `;
    comparacionContenido.appendChild(div);
  });

  if (seleccionados.length === 2) {
    generarGrafico(seleccionados);
  }
}

// Crear gráfico con Chart.js
function generarGrafico([p1, p2]) {
  const ctx = document.getElementById("comparacionChart").getContext("2d");
  if (comparacionChart) comparacionChart.destroy();

  comparacionChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Precio (RD$)", "Ventas (millones)"],
      datasets: [
        {
          label: p1.nombre,
          data: [p1.precio, p1.ventas],
          backgroundColor: "#00B0FF"
        },
        {
          label: p2.nombre,
          data: [p2.precio, p2.ventas],
          backgroundColor: "#00E0C7"
        }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: "bottom" } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

