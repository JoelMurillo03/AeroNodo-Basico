<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico</title>

    <!-- CSS generado por Vite (incluye Bootstrap y estilos personalizados) -->
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">

    <!-- Bootstrap Icons (se mantiene desde CDN para asegurar disponibilidad) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<?= view('components/sidebar') ?>

<!-- ===== MAIN ===== -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <h3>Dashboard</h3>
            <small>Resumen general del entorno</small>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span id="topbar-status" class="status-online">● Conectado</span>
            <i class="bi bi-wifi fs-4"></i>
            <span id="topbar-datetime">--</span>
            <div>
                <i class="bi bi-person-circle fs-3"></i> Usuario
            </div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN (orden: Temp, Hum, Pres, Calidad, Sistema) -->
    <div class="row g-4">
        <div class="col-md-2">
            <div class="card-info">
                <i class="bi bi-thermometer-half temp"></i>
                <h6 class="mt-3">Temperatura</h6>
                <h2 id="card-temp">-- °C</h2>
                <span id="card-temp-status">--</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card-info">
                <i class="bi bi-droplet-fill hum"></i>
                <h6 class="mt-3">Humedad</h6>
                <h2 id="card-hum">-- %</h2>
                <span id="card-hum-status">--</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card-info">
                <i class="bi bi-speedometer2 pres"></i>
                <h6 class="mt-3">Presión</h6>
                <h2 id="card-pres">--</h2>
                <span>hPa</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-info border border-warning">
                <i class="bi bi-wind air"></i>
                <h6 class="mt-3">Calidad del Aire</h6>
                <h2 id="card-air">-- ppm</h2>
                <span id="card-air-status" class="text-warning">--</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-info border border-success">
                <i class="bi bi-shield-check ok"></i>
                <h6 class="mt-3">Estado del Sistema</h6>
                <h2 id="card-sistema" class="text-success">--</h2>
                <span id="card-sistema-sub">--</span>
            </div>
        </div>
    </div>

    <!-- GRÁFICAS (orden: Temp → Hum → Pres → Calidad) -->
    <div class="chart-card">
        <div class="row">
            <!-- Temperatura -->
            <div class="col-md-3">
                <div class="text-center mb-1">
                    <span class="badge bg-danger">🌡️ Temperatura</span>
                </div>
                <canvas id="tempChart"></canvas>
            </div>
            <!-- Humedad -->
            <div class="col-md-3">
                <div class="text-center mb-1">
                    <span class="badge bg-primary">💧 Humedad</span>
                </div>
                <canvas id="humChart"></canvas>
            </div>
            <!-- Presión -->
            <div class="col-md-3">
                <div class="text-center mb-1">
                    <span class="badge bg-purple">📊 Presión</span>
                </div>
                <canvas id="presChart"></canvas>
            </div>
            <!-- Calidad del Aire -->
            <div class="col-md-3">
                <div class="text-center mb-1">
                    <span class="badge bg-success">🌬️ Calidad Aire</span>
                </div>
                <canvas id="airChart"></canvas>
            </div>
        </div>
    </div>

    <!-- TABLA Y ALERTAS -->
    <div class="row">
        <div class="col-md-8">
            <div class="table-card">
                <h5>Últimos Registros</h5>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Temp</th>
                                <th>Hum</th>
                                <th>Presión</th>
                                <th>Aire</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-registros">
                            <!-- Se llenará con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert-card">
                <h5>Alertas Activas</h5>
                <div id="alertas-container">
                    <!-- Se llenará con JavaScript -->
                </div>
            </div>

            <div class="action-card">
                <h5>Acciones</h5>
                <div class="d-grid gap-3 mt-4">
                    <button class="btn btn-primary" id="btn-exportar">
                        <i class="bi bi-download"></i> Exportar Datos
                    </button>
                    <button class="btn btn-success" id="btn-actualizar">
                        <i class="bi bi-arrow-repeat"></i> Actualizar Datos
                    </button>
                    <button class="btn btn-secondary" id="btn-filtrar">
                        <i class="bi bi-calendar"></i> Filtrar por Fechas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0
    </footer>
</div>

<!-- ===== SCRIPTS ===== -->
<!-- Script principal generado por Vite -->
<script type="module" src="<?= base_url('assets/app.js') ?>"></script>

<!-- Script específico del dashboard -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ---- Variables para las gráficas ----
        const chartColors = {
            temp: '#ef4444',
            hum: '#2563eb',
            air: '#16a34a',
            pres: '#9333ea'
        };

        // Inicializar gráficas en el nuevo orden: Temp, Hum, Pres, Air
        const ctxTemp = document.getElementById('tempChart').getContext('2d');
        const ctxHum = document.getElementById('humChart').getContext('2d');
        const ctxPres = document.getElementById('presChart').getContext('2d');
        const ctxAir = document.getElementById('airChart').getContext('2d');

        // Etiquetas iniciales (se actualizarán)
        const labels = ['--', '--', '--', '--', '--', '--'];

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true }
            },
            elements: {
                point: { radius: 2 }
            }
        };

        const tempChart = new Chart(ctxTemp, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Temperatura',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: chartColors.temp,
                    backgroundColor: chartColors.temp + '33',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: commonOptions
        });

        const humChart = new Chart(ctxHum, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Humedad',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: chartColors.hum,
                    backgroundColor: chartColors.hum + '33',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: commonOptions
        });

        const presChart = new Chart(ctxPres, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Presión',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: chartColors.pres,
                    backgroundColor: chartColors.pres + '33',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: commonOptions
        });

        const airChart = new Chart(ctxAir, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Calidad Aire',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: chartColors.air,
                    backgroundColor: chartColors.air + '33',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: commonOptions
        });

        // ---- Funciones de actualización ----

        // 1. Obtener último registro y actualizar tarjetas
        async function fetchUltimo() {
            try {
                const resp = await fetch('/sensores/ultimo');
                if (!resp.ok) throw new Error('Error al obtener último registro');
                const data = await resp.json();

                const temp = parseFloat(data.temperatura) || 0;
                const hum = parseFloat(data.humedad) || 0;
                const pres = parseFloat(data.presion) || 0;
                const air = parseInt(data.calidad_aire) || 0;
                const indice = parseFloat(data.indice) || 0;

                // Tarjetas
                document.getElementById('card-temp').textContent = temp.toFixed(1) + ' °C';
                document.getElementById('card-hum').textContent = hum.toFixed(1) + ' %';
                document.getElementById('card-pres').textContent = pres.toFixed(1);
                document.getElementById('card-air').textContent = air + ' ppm';

                // Estados (clasificación simple)
                const tempStatus = temp < 15 ? 'Baja' : (temp > 30 ? 'Alta' : 'Normal');
                const humStatus = hum < 40 ? 'Baja' : (hum > 70 ? 'Alta' : 'Normal');
                const airStatus = air <= 500 ? 'Normal' : (air <= 800 ? 'Moderado' : 'Crítico');

                document.getElementById('card-temp-status').textContent = tempStatus;
                document.getElementById('card-temp-status').className = tempStatus === 'Normal' ? 'text-success' : (tempStatus === 'Alta' ? 'text-danger' : 'text-warning');
                document.getElementById('card-hum-status').textContent = humStatus;
                document.getElementById('card-hum-status').className = humStatus === 'Normal' ? 'text-success' : (humStatus === 'Alta' ? 'text-danger' : 'text-warning');
                document.getElementById('card-air-status').textContent = airStatus;
                document.getElementById('card-air-status').className = airStatus === 'Normal' ? 'text-success' : (airStatus === 'Moderado' ? 'text-warning' : 'text-danger');

                // Estado del sistema (basado en conectividad)
                const wifiOk = data.estado_wifi === true || data.estado_wifi === 1;
                document.getElementById('card-sistema').textContent = wifiOk ? 'Operativo' : 'Desconectado';
                document.getElementById('card-sistema').className = wifiOk ? 'text-success' : 'text-danger';
                document.getElementById('card-sistema-sub').textContent = wifiOk ? 'Todo en orden' : 'Revisar conexión';

            } catch (error) {
                console.error('Error en fetchUltimo:', error);
            }
        }

        // 2. Obtener recientes (15) para tabla y gráficas
        async function fetchRecientes() {
            try {
                const resp = await fetch('/sensores/recientes/15');
                if (!resp.ok) throw new Error('Error al obtener recientes');
                const data = await resp.json();

                // Actualizar tabla (mostrar últimos 5)
                const tbody = document.getElementById('tabla-registros');
                tbody.innerHTML = '';
                const mostrar = data.slice(-5).reverse(); // los más recientes primero
                mostrar.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    const fecha = row.fecha_local ? row.fecha_local : row.fecha;
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${fecha}</td>
                        <td>${parseFloat(row.temperatura).toFixed(1)}</td>
                        <td>${parseFloat(row.humedad).toFixed(1)}</td>
                        <td>${parseFloat(row.presion).toFixed(1)}</td>
                        <td>${parseInt(row.calidad_aire)}</td>
                    `;
                    tbody.appendChild(tr);
                });

                // Actualizar gráficas: usar los últimos 6 puntos (o todos si son menos)
                const puntos = data.length >= 6 ? data.slice(-6) : data;
                const labels = puntos.map(item => {
                    const fecha = item.fecha_local ? item.fecha_local : item.fecha;
                    return fecha.substring(11, 16); // HH:MM
                });

                const temps = puntos.map(item => parseFloat(item.temperatura) || 0);
                const hums = puntos.map(item => parseFloat(item.humedad) || 0);
                const pres = puntos.map(item => parseFloat(item.presion) || 0);
                const airs = puntos.map(item => parseInt(item.calidad_aire) || 0);

                // Actualizar datasets en el nuevo orden: temp, hum, pres, air
                tempChart.data.labels = labels;
                tempChart.data.datasets[0].data = temps;
                tempChart.update();

                humChart.data.labels = labels;
                humChart.data.datasets[0].data = hums;
                humChart.update();

                presChart.data.labels = labels;
                presChart.data.datasets[0].data = pres;
                presChart.update();

                airChart.data.labels = labels;
                airChart.data.datasets[0].data = airs;
                airChart.update();

            } catch (error) {
                console.error('Error en fetchRecientes:', error);
            }
        }

        // 3. Obtener estado del dispositivo (sidebar y topbar)
        async function fetchEstado() {
            try {
                const resp = await fetch('/api/dispositivo');
                if (!resp.ok) throw new Error('Error al obtener estado');
                const data = await resp.json();

                const wifi = data.estado_wifi === true || data.estado_wifi === 1;
                const intensidad = data.intensidad_wifi || 0;
                const ultima = data.ultima_actualizacion || '--';

                // Sidebar
                document.getElementById('sidebar-wifi').innerHTML = wifi ? '🟢 WiFi: Conectado' : '🔴 WiFi: Desconectado';
                document.getElementById('sidebar-api').innerHTML = wifi ? '🟢 API: OK' : '🔴 API: Sin conexión';
                document.getElementById('sidebar-fecha').textContent = ultima;

                // Topbar
                const statusSpan = document.getElementById('topbar-status');
                if (wifi) {
                    statusSpan.textContent = '● Conectado';
                    statusSpan.className = 'status-online';
                } else {
                    statusSpan.textContent = '● Desconectado';
                    statusSpan.className = 'status-offline';
                }

                // También actualizar el estado del sistema en tarjetas (aunque fetchUltimo ya lo hace)
                const sistema = document.getElementById('card-sistema');
                const sub = document.getElementById('card-sistema-sub');
                if (wifi) {
                    sistema.textContent = 'Operativo';
                    sistema.className = 'text-success';
                    sub.textContent = 'Todo en orden';
                } else {
                    sistema.textContent = 'Desconectado';
                    sistema.className = 'text-danger';
                    sub.textContent = 'Revisar conexión';
                }

            } catch (error) {
                console.error('Error en fetchEstado:', error);
            }
        }

        // 4. Obtener alertas activas
        async function fetchAlertas() {
            try {
                const resp = await fetch('/api/alertas');
                if (!resp.ok) throw new Error('Error al obtener alertas');
                const data = await resp.json();

                const container = document.getElementById('alertas-container');
                container.innerHTML = '';

                if (data.length === 0) {
                    container.innerHTML = '<p class="text-muted">No hay alertas activas</p>';
                    return;
                }

                data.forEach(alerta => {
                    const nivel = alerta.nivel || 'normal';
                    const clase = nivel === 'critica' ? 'alert-danger-custom' : (nivel === 'moderada' ? 'alert-warning-custom' : 'alert-success-custom');
                    const div = document.createElement('div');
                    div.className = `alert-item ${clase}`;
                    div.innerHTML = `
                        <strong>${nivel.charAt(0).toUpperCase() + nivel.slice(1)}</strong>
                        <br>
                        Valor: ${alerta.valor_detectado} ${alerta.unidad || ''}
                        <br><small>${alerta.fecha}</small>
                    `;
                    container.appendChild(div);
                });

            } catch (error) {
                console.error('Error en fetchAlertas:', error);
            }
        }

        // 5. Actualizar reloj en la topbar
        function updateClock() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('es-MX', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            });
            const timeStr = now.toLocaleTimeString('es-MX', {
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
            document.getElementById('topbar-datetime').textContent = `${dateStr} ${timeStr}`;
        }

        // 6. Función de actualización completa
        function refreshAll() {
            fetchUltimo();
            fetchRecientes();
            fetchEstado();
            fetchAlertas();
        }

        // ---- Configuración inicial y temporizadores ----

        // Reloj (actualiza cada segundo)
        setInterval(updateClock, 1000);
        updateClock();

        // Carga inicial de datos
        refreshAll();

        // Actualización automática cada 5 segundos
        setInterval(refreshAll, 5000);

        // Botón "Actualizar Datos"
        document.getElementById('btn-actualizar').addEventListener('click', function () {
            refreshAll();
            // Pequeño feedback visual
            this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Actualizando...';
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Actualizar Datos';
            }, 1000);
        });

        // ===== Botón Exportar Datos (actualizado) =====
        document.getElementById('btn-exportar').addEventListener('click', function() {
            window.location.href = '<?= base_url('dashboard/exportar') ?>';
        });

        // ===== Botón Filtrar por Fechas (actualizado) =====
        document.getElementById('btn-filtrar').addEventListener('click', function() {
            // Crear modal dinámicamente
            const modalHtml = `
                <div class="modal fade" id="modalFiltrar" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-calendar"></i> Filtrar por Fechas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Desde</label>
                                    <input type="date" id="filtro-desde" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Hasta</label>
                                    <input type="date" id="filtro-hasta" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-primary" id="btn-aplicar-filtro">Aplicar filtro</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Eliminar modal existente si hay
            const oldModal = document.getElementById('modalFiltrar');
            if (oldModal) oldModal.remove();

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('modalFiltrar'));
            modal.show();

            document.getElementById('btn-aplicar-filtro').addEventListener('click', function() {
                const desde = document.getElementById('filtro-desde').value;
                const hasta = document.getElementById('filtro-hasta').value;
                if (!desde && !hasta) {
                    alert('Selecciona al menos una fecha');
                    return;
                }
                // Redirigir a historial con filtros
                let url = '<?= base_url('historial') ?>?';
                if (desde) url += 'desde=' + desde + '&';
                if (hasta) url += 'hasta=' + hasta;
                window.location.href = url;
            });
        });

        // ---- Estilos dinámicos ----
        const style = document.createElement('style');
        style.textContent = `
            .status-offline {
                color: #dc3545;
                font-weight: bold;
            }
            .alert-danger-custom {
                background: #fef2f2;
                border-left: 5px solid #ef4444;
            }
            .bg-purple {
                background-color: #9333ea;
                color: white;
            }
        `;
        document.head.appendChild(style);

    });
</script>

</body>
</html>