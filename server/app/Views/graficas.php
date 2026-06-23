<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Gráficas Avanzadas</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Plugin para líneas de regresión -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.1/dist/chartjs-plugin-annotation.min.js"></script>

    <style>
        /* ===== CONTENEDORES DE GRÁFICAS CON ALTURA FIJA ===== */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .chart-container canvas {
            display: block;
            width: 100% !important;
            height: 100% !important;
        }

        /* Ajuste para pantallas pequeñas */
        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }

        /* Ajuste para la gráfica de tendencia (ocupa todo el ancho) */
        .chart-container-full {
            height: 350px;
        }
        @media (max-width: 768px) {
            .chart-container-full {
                height: 280px;
            }
        }
    </style>
</head>
<body>

<?= view('components/sidebar') ?>

<div class="main">
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <h3>📊 Gráficas Avanzadas</h3>
            <small>Análisis estadístico y probabilístico de datos ambientales</small>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span id="topbar-status" class="status-online">● Conectado</span>
            <i class="bi bi-wifi fs-4"></i>
            <span id="topbar-datetime">--</span>
            <div><i class="bi bi-person-circle fs-3"></i> Usuario</div>
        </div>
    </div>

    <!-- Botón de actualización -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" id="btn-actualizar-graficas">
            <i class="bi bi-arrow-repeat"></i> Actualizar Datos
        </button>
    </div>

    <!-- Contenedor de carga -->
    <div id="cargando" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2">Cargando gráficas, por favor espere...</p>
    </div>

    <!-- Grid de gráficas -->
    <div id="graficas-container" style="display: none;">
        <div class="row g-4">

            <!-- Gráfica 1: Tendencia Histórica (ocupa 12 columnas) -->
            <div class="col-12">
                <div class="card p-3">
                    <h5 class="card-title">📈 Tendencia Histórica de Variables</h5>
                    <div class="chart-container chart-container-full">
                        <canvas id="chartTendencia"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfica 2: Resumen Estadístico -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">📊 Comparativa de Métricas por Variable</h5>
                    <div class="chart-container">
                        <canvas id="chartResumen"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfica 3: Distribución por Cuartiles -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">📦 Distribución por Cuartiles</h5>
                    <div class="chart-container">
                        <canvas id="chartCuartiles"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfica 4: Correlación y Regresión Lineal -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title" id="tituloDispersion">🔗 Relación y Correlación</h5>
                    <div class="chart-container">
                        <canvas id="chartDispersion"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfica 5: Frecuencia de Rangos -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">📊 Frecuencia de Rangos de Temperatura</h5>
                    <div class="chart-container">
                        <canvas id="chartFrecuencia"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfica 6: Probabilidad de Estados -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">🎯 Probabilidad de Estados de Temperatura</h5>
                    <div class="chart-container">
                        <canvas id="chartProbabilidad"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfica 7: Variabilidad (Desviación Estándar) -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">📉 Comparativa de Variabilidad (Desv. Estándar)</h5>
                    <div class="chart-container">
                        <canvas id="chartVariabilidad"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer>AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0</footer>
</div>

<!-- Script principal -->
<script type="module" src="<?= base_url('assets/app.js') ?>"></script>

<!-- Script específico de gráficas -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Variables globales para almacenar datos
        let estadisticas = null;
        let historial = null;
        let configs = <?= json_encode($configs ?? []) ?>; // Pasado desde el controlador
        let charts = {}; // Para almacenar instancias de Chart.js

        // Elementos del DOM
        const container = document.getElementById('graficas-container');
        const cargando = document.getElementById('cargando');

        // Colores del proyecto
        const colores = {
            temperatura: '#ef4444',
            humedad: '#2563eb',
            presion: '#9333ea',
            calidad: '#16a34a',
            indice: '#f59e0b'
        };

        // ------ Funciones de fetch ------

        async function fetchEstadisticas() {
            try {
                const resp = await fetch('/estadisticas/resumen');
                if (!resp.ok) throw new Error('Error al obtener estadísticas');
                estadisticas = await resp.json();
                return estadisticas;
            } catch (error) {
                console.error('Error fetchEstadisticas:', error);
                throw error;
            }
        }

        async function fetchHistorial() {
            try {
                const resp = await fetch('/sensores/recientes/100');
                if (!resp.ok) throw new Error('Error al obtener historial');
                historial = await resp.json();
                return historial;
            } catch (error) {
                console.error('Error fetchHistorial:', error);
                throw error;
            }
        }

        // ------ Funciones de renderizado de gráficas ------

        function renderGraficas() {
            if (!estadisticas || !historial) {
                console.warn('Faltan datos para renderizar');
                return;
            }

            // Ocultar spinner y mostrar contenedor
            cargando.style.display = 'none';
            container.style.display = 'block';

            // 1. Tendencia Histórica (líneas)
            renderTendencia();

            // 2. Resumen Estadístico (barras agrupadas)
            renderResumen();

            // 3. Distribución por Cuartiles (barras)
            renderCuartiles();

            // 4. Correlación y Regresión (dispersión)
            renderDispersion();

            // 5. Frecuencia de Rangos (barras)
            renderFrecuencia();

            // 6. Probabilidad de Estados (dona)
            renderProbabilidad();

            // 7. Variabilidad (desviación estándar)
            renderVariabilidad();
        }

        // ------ Gráfica 1: Tendencia Histórica ------
        function renderTendencia() {
            const ctx = document.getElementById('chartTendencia').getContext('2d');
            const data = historial;
            const fechas = data.map(item => {
                const f = item.fecha_local ? item.fecha_local : item.fecha;
                return f.substring(11, 16); // HH:MM
            });

            if (charts.tendencia) charts.tendencia.destroy();
            charts.tendencia = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: fechas,
                    datasets: [
                        { label: 'Temperatura', data: data.map(d => parseFloat(d.temperatura)), borderColor: colores.temperatura, backgroundColor: colores.temperatura + '33', fill: true, tension: 0.1, yAxisID: 'y1' },
                        { label: 'Humedad', data: data.map(d => parseFloat(d.humedad)), borderColor: colores.humedad, backgroundColor: colores.humedad + '33', fill: true, tension: 0.1, yAxisID: 'y1' },
                        { label: 'Presión', data: data.map(d => parseFloat(d.presion)), borderColor: colores.presion, backgroundColor: colores.presion + '33', fill: true, tension: 0.1, yAxisID: 'y2' },
                        { label: 'Calidad Aire', data: data.map(d => parseInt(d.calidad_aire)), borderColor: colores.calidad, backgroundColor: colores.calidad + '33', fill: true, tension: 0.1, yAxisID: 'y1' },
                        { label: 'Índice', data: data.map(d => parseFloat(d.indice)), borderColor: colores.indice, backgroundColor: colores.indice + '33', fill: true, tension: 0.1, yAxisID: 'y1' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y1: { type: 'linear', position: 'left', title: { display: true, text: 'Temp/Hum/Cal/Índice' } },
                        y2: { type: 'linear', position: 'right', title: { display: true, text: 'Presión (hPa)' }, grid: { drawOnChartArea: false } }
                    }
                }
            });
        }

        // ------ Gráfica 2: Resumen Estadístico ------
        function renderResumen() {
            const ctx = document.getElementById('chartResumen').getContext('2d');
            const vars = ['temperatura', 'humedad', 'presion', 'calidad_aire', 'indice'];
            const etiquetas = ['Temperatura', 'Humedad', 'Presión', 'Calidad', 'Índice'];
            const coloresVar = ['#ef4444', '#2563eb', '#9333ea', '#16a34a', '#f59e0b'];

            const dataPromedio = vars.map(v => parseFloat(estadisticas[v]?.promedio || 0));
            const dataMediana = vars.map(v => parseFloat(estadisticas[v]?.mediana || 0));
            const dataMin = vars.map(v => parseFloat(estadisticas[v]?.minimo || 0));
            const dataMax = vars.map(v => parseFloat(estadisticas[v]?.maximo || 0));

            if (charts.resumen) charts.resumen.destroy();
            charts.resumen = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [
                        { label: 'Promedio', data: dataPromedio, backgroundColor: '#4f46e5' },
                        { label: 'Mediana', data: dataMediana, backgroundColor: '#f59e0b' },
                        { label: 'Mínimo', data: dataMin, backgroundColor: '#22c55e' },
                        { label: 'Máximo', data: dataMax, backgroundColor: '#ef4444' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // ------ Gráfica 3: Distribución por Cuartiles ------
        function renderCuartiles() {
            const ctx = document.getElementById('chartCuartiles').getContext('2d');
            const vars = ['temperatura', 'humedad', 'presion', 'calidad_aire', 'indice'];
            const etiquetas = ['Temperatura', 'Humedad', 'Presión', 'Calidad', 'Índice'];

            const dataMin = vars.map(v => parseFloat(estadisticas[v]?.minimo || 0));
            const dataQ1 = vars.map(v => parseFloat(estadisticas[v]?.primer_cuartil || 0));
            const dataMediana = vars.map(v => parseFloat(estadisticas[v]?.mediana || 0));
            const dataQ3 = vars.map(v => parseFloat(estadisticas[v]?.tercer_cuartil || 0));
            const dataMax = vars.map(v => parseFloat(estadisticas[v]?.maximo || 0));

            if (charts.cuartiles) charts.cuartiles.destroy();
            charts.cuartiles = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [
                        { label: 'Mínimo', data: dataMin, backgroundColor: '#22c55e' },
                        { label: 'Q1', data: dataQ1, backgroundColor: '#3b82f6' },
                        { label: 'Mediana', data: dataMediana, backgroundColor: '#f59e0b' },
                        { label: 'Q3', data: dataQ3, backgroundColor: '#8b5cf6' },
                        { label: 'Máximo', data: dataMax, backgroundColor: '#ef4444' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // ------ Gráfica 4: Dispersión y Regresión ------
        function renderDispersion() {
            const ctx = document.getElementById('chartDispersion').getContext('2d');
            // Elegir par de variables: Temperatura vs Humedad
            const xData = historial.map(d => parseFloat(d.temperatura));
            const yData = historial.map(d => parseFloat(d.humedad));

            // Obtener coeficientes de regresión del resumen
            const reg = estadisticas.correlaciones?.temperatura_humedad?.regresion_lineal || [];
            const pendiente = reg[0] || 0;
            const intercepto = reg[1] || 0;
            const correlacion = estadisticas.correlaciones?.temperatura_humedad?.correlacion || 0;

            // Actualizar título con el valor de correlación
            document.getElementById('tituloDispersion').textContent =
                `🔗 Relación y Correlación: Temperatura vs Humedad (r = ${correlacion.toFixed(4)})`;

            // Preparar puntos de dispersión
            const scatterData = xData.map((x, i) => ({ x: x, y: yData[i] }));

            // Línea de regresión: calcular dos puntos extremos
            const xMin = Math.min(...xData);
            const xMax = Math.max(...xData);
            const regLine = [
                { x: xMin, y: pendiente * xMin + intercepto },
                { x: xMax, y: pendiente * xMax + intercepto }
            ];

            if (charts.dispersion) charts.dispersion.destroy();
            charts.dispersion = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [
                        {
                            label: 'Datos',
                            data: scatterData,
                            backgroundColor: '#6366f1',
                            pointRadius: 4
                        },
                        {
                            label: 'Regresión lineal',
                            data: regLine,
                            type: 'line',
                            borderColor: '#ef4444',
                            borderWidth: 3,
                            pointRadius: 0,
                            fill: false,
                            tension: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return `Temp: ${context.raw.x}°C, Hum: ${context.raw.y}%`;
                                    } else {
                                        return `Regresión: y = ${pendiente.toFixed(2)}x + ${intercepto.toFixed(2)}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Temperatura (°C)' } },
                        y: { title: { display: true, text: 'Humedad (%)' }, beginAtZero: true }
                    }
                }
            });
        }

        // ------ Gráfica 5: Frecuencia de Rangos ------
        function renderFrecuencia() {
            const ctx = document.getElementById('chartFrecuencia').getContext('2d');
            const rangos = estadisticas.frecuencias?.temperatura?.frecuencia_rangos || {};
            const etiquetas = Object.keys(rangos).map(k => `${k}-${parseInt(k)+10}`);
            const valores = Object.values(rangos);

            if (charts.frecuencia) charts.frecuencia.destroy();
            charts.frecuencia = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Frecuencia',
                        data: valores,
                        backgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Nº de registros' } } }
                }
            });
        }

        // ------ Gráfica 6: Probabilidad de Estados (Dona) ------
        function renderProbabilidad() {
            const ctx = document.getElementById('chartProbabilidad').getContext('2d');

            // Umbrales desde configuración
            const tempMin = parseFloat(configs.umbral_temp_minima) || 15;
            const tempMax = parseFloat(configs.umbral_temp_maxima) || 30;

            // Clasificar las temperaturas
            const temps = historial.map(d => parseFloat(d.temperatura)).filter(v => !isNaN(v));
            let baja = 0, normal = 0, alta = 0;

            temps.forEach(t => {
                if (t < tempMin) baja++;
                else if (t > tempMax) alta++;
                else normal++;
            });

            const total = temps.length || 1;
            const data = [baja, normal, alta];
            const labels = [`Baja (< ${tempMin}°C)`, `Normal (${tempMin}-${tempMax}°C)`, `Alta (> ${tempMax}°C)`];
            const colores = ['#f59e0b', '#22c55e', '#ef4444'];
            const porcentajes = data.map(v => ((v / total) * 100).toFixed(1));

            if (charts.probabilidad) charts.probabilidad.destroy();
            charts.probabilidad = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels.map((l, i) => `${l} (${porcentajes[i]}%)`),
                    datasets: [{
                        data: data,
                        backgroundColor: colores,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const porcentaje = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed} registros (${porcentaje}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // ------ Gráfica 7: Variabilidad (Desviación Estándar) ------
        function renderVariabilidad() {
            const ctx = document.getElementById('chartVariabilidad').getContext('2d');
            const vars = ['temperatura', 'humedad', 'presion', 'calidad_aire', 'indice'];
            const etiquetas = ['Temperatura', 'Humedad', 'Presión', 'Calidad', 'Índice'];
            const coloresVar = ['#ef4444', '#2563eb', '#9333ea', '#16a34a', '#f59e0b'];
            const desviaciones = vars.map(v => parseFloat(estadisticas[v]?.desviacion_estandar || 0));

            if (charts.variabilidad) charts.variabilidad.destroy();
            charts.variabilidad = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Desviación Estándar',
                        data: desviaciones,
                        backgroundColor: coloresVar
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Desviación Estándar' } } }
                }
            });
        }

        // ------ Función principal de carga ------
        async function cargarDatos() {
            try {
                // Mostrar spinner
                cargando.style.display = 'block';
                container.style.display = 'none';

                // Obtener datos
                await fetchEstadisticas();
                await fetchHistorial();

                // Renderizar
                renderGraficas();
            } catch (error) {
                console.error('Error al cargar datos:', error);
                cargando.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Error al cargar los datos. Intenta nuevamente.
                        <button class="btn btn-outline-danger ms-3" onclick="location.reload()">
                            Recargar página
                        </button>
                    </div>
                `;
            }
        }

        // ------ Eventos y temporizadores ------
        // Botón de actualización manual
        document.getElementById('btn-actualizar-graficas').addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Actualizando...';
            this.disabled = true;

            cargarDatos().finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });

        // Reloj en la topbar
        function updateClock() {
            const now = new Date();
            document.getElementById('topbar-datetime').textContent =
                now.toLocaleDateString('es-MX') + ' ' + now.toLocaleTimeString('es-MX');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Estado del dispositivo (sidebar y topbar)
        async function fetchEstado() {
            try {
                const resp = await fetch('/api/dispositivo');
                if (!resp.ok) return;
                const data = await resp.json();
                const wifi = data.estado_wifi === true || data.estado_wifi === 1;
                document.getElementById('topbar-status').textContent = wifi ? '● Conectado' : '● Desconectado';
                document.getElementById('topbar-status').className = wifi ? 'status-online' : 'status-offline';
                document.getElementById('sidebar-wifi').innerHTML = wifi ? '🟢 WiFi: Conectado' : '🔴 WiFi: Desconectado';
                document.getElementById('sidebar-api').innerHTML = wifi ? '🟢 API: OK' : '🔴 API: Sin conexión';
                document.getElementById('sidebar-fecha').textContent = data.ultima_actualizacion || '--';
            } catch (e) { console.error(e); }
        }
        fetchEstado();
        setInterval(fetchEstado, 5000);

        // Carga inicial de gráficas
        cargarDatos();
    });
</script>

</body>
</html>