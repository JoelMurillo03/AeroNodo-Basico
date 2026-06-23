<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Dashboard IoT</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <style>
        /* Estilos adicionales para la vista */
        .card-header { background-color: #f8f9fa; }
        .status-badge { font-size: 1rem; padding: 0.5rem 1rem; }
        .alert-item { border-left: 4px solid #ffc107; margin-bottom: 0.5rem; padding-left: 1rem; }
        .alert-item.critica { border-left-color: #dc3545; }
        .alert-item.moderada { border-left-color: #ffc107; }
        .alert-item.normal { border-left-color: #28a745; }
        .gauge { width: 100%; height: 220px; }
        .barras { width: 100%; max-width: 800px; height: 400px; margin: 0 auto; }
        .lineas { width: 100%; max-width: 1000px; height: 400px; margin: 0 auto; }
        .barras-recientes { width: 100%; max-width: 1200px; height: 450px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Encabezado -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold">🌤️ AeroNodo Básico</h1>
                <p class="lead">Monitoreo Ambiental IoT en tiempo real</p>
            </div>
        </div>

        <!-- Último registro -->
        <div class="alert alert-light border text-center my-3" role="alert">
            <strong>Último registro:</strong> <span id="fecha_registro">Cargando...</span>
        </div>

        <!-- Estado del dispositivo -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">📶 Estado del Dispositivo</div>
                    <div class="card-body">
                        <p><strong>WiFi:</strong> <span id="estado_wifi" class="badge bg-secondary">Desconocido</span></p>
                        <p><strong>Intensidad de señal:</strong> <span id="intensidad_wifi">--</span> dBm</p>
                        <p><strong>Última actualización:</strong> <span id="ultima_actualizacion">--</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">⚠️ Alertas Activas</div>
                    <div class="card-body" id="alertas_container">
                        <p class="text-muted">Cargando alertas...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gauges -->
        <div class="row g-2">
            <div class="col-12 col-md-6 col-lg-2">
                <div class="card text-center">
                    <div class="card-header"><strong>Temperatura</strong></div>
                    <div class="card-body">
                        <div id="gauge_temperatura" class="gauge"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <div class="card text-center">
                    <div class="card-header"><strong>Humedad</strong></div>
                    <div class="card-body">
                        <div id="gauge_humedad" class="gauge"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <div class="card text-center">
                    <div class="card-header"><strong>Presión</strong></div>
                    <div class="card-body">
                        <div id="gauge_presion" class="gauge"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <div class="card text-center">
                    <div class="card-header"><strong>Calidad del Aire</strong></div>
                    <div class="card-body">
                        <div id="gauge_calidad" class="gauge"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <div class="card text-center">
                    <div class="card-header"><strong>Índice</strong></div>
                    <div class="card-body">
                        <div id="gauge_indice" class="gauge"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="row g-2 mt-3">
            <div class="col-12">
                <div class="card text-center">
                    <div class="card-header">Último Registro (Barras)</div>
                    <div class="card-body">
                        <div id="barras_ultimo" class="barras"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-2 mt-3">
            <div class="col-12">
                <div class="card text-center">
                    <div class="card-header">Historial (Líneas) - Últimos 15 registros</div>
                    <div class="card-body">
                        <div id="lineas_historial" class="lineas"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-2 mt-3">
            <div class="col-12">
                <div class="card text-center">
                    <div class="card-header">Últimos 15 registros (Barras agrupadas)</div>
                    <div class="card-body">
                        <div id="barras_recientes" class="barras-recientes"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script type="module" src="<?= base_url('assets/app.js') ?>"></script>
    <script>
        // Aquí se escribe todo el código JavaScript para el dashboard
        document.addEventListener('DOMContentLoaded', () => {

            // 1. Inicialización de gráficas echarts
            const fechaRegistro = document.getElementById('fecha_registro');
            const estadoWifiSpan = document.getElementById('estado_wifi');
            const intensidadWifiSpan = document.getElementById('intensidad_wifi');
            const ultimaActualizacionSpan = document.getElementById('ultima_actualizacion');
            const alertasContainer = document.getElementById('alertas_container');

            // Gauges
            const tempGauge = window.echarts.init(document.getElementById('gauge_temperatura'));
            const humGauge = window.echarts.init(document.getElementById('gauge_humedad'));
            const presGauge = window.echarts.init(document.getElementById('gauge_presion'));
            const calGauge = window.echarts.init(document.getElementById('gauge_calidad'));
            const indGauge = window.echarts.init(document.getElementById('gauge_indice'));

            // Barras último
            const barUltimo = window.echarts.init(document.getElementById('barras_ultimo'));

            // Líneas historial
            const lineChart = window.echarts.init(document.getElementById('lineas_historial'));

            // Barras recientes
            const barRecientes = window.echarts.init(document.getElementById('barras_recientes'));

            // 2. Configuración inicial de los gauges (se actualizarán dinámicamente)
            const gaugeOptionTemplate = (title, unit, max, color) => ({
                series: [{
                    type: 'gauge',
                    center: ['50%', '65%'],
                    startAngle: 200,
                    endAngle: -20,
                    min: 0,
                    max: max,
                    splitNumber: 10,
                    itemStyle: { color: color },
                    progress: { show: true, width: 30 },
                    pointer: { show: false },
                    axisLine: { lineStyle: { width: 30 } },
                    axisTick: { distance: -45, splitNumber: 5, lineStyle: { width: 2, color: '#999' } },
                    splitLine: { distance: -52, length: 14, lineStyle: { width: 3, color: '#999' } },
                    axisLabel: { distance: -20, color: '#999', fontSize: 16 },
                    anchor: { show: false },
                    title: { show: false },
                    detail: {
                        valueAnimation: true,
                        width: '60%',
                        lineHeight: 40,
                        borderRadius: 8,
                        offsetCenter: [0, '50%'],
                        fontSize: 32,
                        fontWeight: 'bolder',
                        formatter: `{value} ${unit}`,
                        color: 'inherit'
                    },
                    data: [{ value: 0 }]
                }]
            });

            tempGauge.setOption(gaugeOptionTemplate('Temperatura', '°C', 50, '#FFAB91'));
            humGauge.setOption(gaugeOptionTemplate('Humedad', '%', 100, '#91E5FF'));
            presGauge.setOption(gaugeOptionTemplate('Presión', 'hPa', 1100, '#B39DDB'));
            calGauge.setOption(gaugeOptionTemplate('Calidad Aire', 'ppm', 1000, '#FFD54F'));
            indGauge.setOption(gaugeOptionTemplate('Índice', '', 100, '#A5D6A7'));

            // 3. Gráfica de barras del último registro (5 variables)
            barUltimo.setOption({
                title: { text: 'Último Registro' },
                xAxis: { data: ['Temp', 'Hum', 'Pres', 'Calidad', 'Índice'] },
                yAxis: {},
                animationDurationUpdate: 500,
                series: [{
                    name: 'Valores',
                    type: 'bar',
                    data: [0, 0, 0, 0, 0]
                }]
            });

            // 4. Gráfica de líneas del historial (15 registros)
            lineChart.setOption({
                title: { text: 'Historial de Registros' },
                tooltip: { trigger: 'axis' },
                legend: { data: ['Temperatura', 'Humedad', 'Presión', 'Calidad', 'Índice'] },
                xAxis: { type: 'category', data: [] },
                yAxis: { type: 'value' },
                series: [
                    { name: 'Temperatura', data: [], type: 'line' },
                    { name: 'Humedad', data: [], type: 'line' },
                    { name: 'Presión', data: [], type: 'line' },
                    { name: 'Calidad', data: [], type: 'line' },
                    { name: 'Índice', data: [], type: 'line' }
                ]
            });

            // 5. Gráfica de barras recientes (15 registros agrupados)
            barRecientes.setOption({
                title: { text: 'Últimos 15 registros' },
                tooltip: { trigger: 'axis' },
                legend: { data: ['Temperatura', 'Humedad', 'Presión', 'Calidad', 'Índice'] },
                xAxis: { type: 'category', data: [], axisLabel: { rotate: 45 } },
                yAxis: { type: 'value' },
                series: [
                    { name: 'Temperatura', type: 'bar', data: [] },
                    { name: 'Humedad', type: 'bar', data: [] },
                    { name: 'Presión', type: 'bar', data: [] },
                    { name: 'Calidad', type: 'bar', data: [] },
                    { name: 'Índice', type: 'bar', data: [] }
                ]
            });

            // 6. Función fetchUltimo
            async function fetchUltimo() {
                try {
                    const response = await fetch('/sensores/ultimo');
                    if (!response.ok) throw new Error('Error en el servidor');
                    const data = await response.json();

                    // Actualizar fecha
                    fechaRegistro.textContent = data.fecha_local || data.fecha;

                    // Actualizar gauges
                    const temp = parseFloat(data.temperatura) || 0;
                    const hum = parseFloat(data.humedad) || 0;
                    const pres = parseFloat(data.presion) || 0;
                    const cal = parseInt(data.calidad_aire) || 0;
                    const ind = parseFloat(data.indice) || 0;

                    tempGauge.setOption({ series: [{ data: [{ value: temp }] }] });
                    humGauge.setOption({ series: [{ data: [{ value: hum }] }] });
                    presGauge.setOption({ series: [{ data: [{ value: pres }] }] });
                    calGauge.setOption({ series: [{ data: [{ value: cal }] }] });
                    indGauge.setOption({ series: [{ data: [{ value: ind }] }] });

                    // Actualizar barras último
                    barUltimo.setOption({
                        series: [{ data: [temp, hum, pres, cal, ind] }]
                    });

                } catch (error) {
                    console.error('Error fetchUltimo:', error);
                }
            }

            // 7. Función fetchRecientes (15 últimos)
            async function fetchRecientes() {
                try {
                    const response = await fetch('/sensores/recientes/15');
                    if (!response.ok) throw new Error('Error en el servidor');
                    const data = await response.json();
                    if (!Array.isArray(data) || data.length === 0) {
                        console.warn('No hay datos recientes');
                        return;
                    }

                    const fechas = data.map(item => item.fecha_local ? item.fecha_local.substring(11, 19) : item.fecha.substring(11, 19));
                    const temps = data.map(item => parseFloat(item.temperatura) || 0);
                    const hums = data.map(item => parseFloat(item.humedad) || 0);
                    const pres = data.map(item => parseFloat(item.presion) || 0);
                    const cals = data.map(item => parseInt(item.calidad_aire) || 0);
                    const inds = data.map(item => parseFloat(item.indice) || 0);

                    // Líneas
                    lineChart.setOption({
                        xAxis: { data: fechas },
                        series: [
                            { name: 'Temperatura', data: temps },
                            { name: 'Humedad', data: hums },
                            { name: 'Presión', data: pres },
                            { name: 'Calidad', data: cals },
                            { name: 'Índice', data: inds }
                        ]
                    });

                    // Barras recientes
                    barRecientes.setOption({
                        xAxis: { data: fechas },
                        series: [
                            { name: 'Temperatura', data: temps },
                            { name: 'Humedad', data: hums },
                            { name: 'Presión', data: pres },
                            { name: 'Calidad', data: cals },
                            { name: 'Índice', data: inds }
                        ]
                    });

                } catch (error) {
                    console.error('Error fetchRecientes:', error);
                }
            }

            // 8. Función fetchEstado
            async function fetchEstado() {
                try {
                    const response = await fetch('/api/dispositivo');
                    if (!response.ok) throw new Error('Error al obtener estado');
                    const data = await response.json();

                    const conectado = data.estado_wifi ? 'Conectado' : 'Desconectado';
                    const badgeClass = data.estado_wifi ? 'bg-success' : 'bg-danger';
                    estadoWifiSpan.textContent = conectado;
                    estadoWifiSpan.className = `badge ${badgeClass}`;
                    intensidadWifiSpan.textContent = data.intensidad_wifi ?? '--';
                    ultimaActualizacionSpan.textContent = data.ultima_actualizacion || '--';
                } catch (error) {
                    console.error('Error fetchEstado:', error);
                }
            }

            // 9. Función fetchAlertas
            async function fetchAlertas() {
                try {
                    const response = await fetch('/api/alertas');
                    if (!response.ok) throw new Error('Error al obtener alertas');
                    const data = await response.json();
                    if (data.length === 0) {
                        alertasContainer.innerHTML = '<p class="text-muted">No hay alertas activas</p>';
                        return;
                    }
                    let html = '';
                    data.forEach(alerta => {
                        const nivel = alerta.nivel || 'normal';
                        const clase = nivel === 'critica' ? 'critica' : (nivel === 'moderada' ? 'moderada' : 'normal');
                        html += `<div class="alert-item ${clase}">
                                    <strong>${nivel.toUpperCase()}</strong> - ${alerta.valor_detectado} ppm
                                    <br><small>${alerta.fecha}</small>
                                </div>`;
                    });
                    alertasContainer.innerHTML = html;
                } catch (error) {
                    console.error('Error fetchAlertas:', error);
                }
            }

            // 10. Redimensionar gráficas al cambiar ventana
            window.addEventListener('resize', () => {
                tempGauge.resize();
                humGauge.resize();
                presGauge.resize();
                calGauge.resize();
                indGauge.resize();
                barUltimo.resize();
                lineChart.resize();
                barRecientes.resize();
            });

            // 11. Ejecutar funciones e intervalos
            fetchUltimo();
            fetchRecientes();
            fetchEstado();
            fetchAlertas();

            setInterval(() => {
                fetchUltimo();
                fetchRecientes();
                fetchEstado();
                fetchAlertas();
            }, 5000);
        });
    </script>
</body>
</html>