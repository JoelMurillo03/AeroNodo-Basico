<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Gráficas</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?= view('components/sidebar') ?>

<div class="main">
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <h3>Gráficas</h3>
            <small>Visualización avanzada de datos</small>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span id="topbar-status" class="status-online">● Conectado</span>
            <i class="bi bi-wifi fs-4"></i>
            <span id="topbar-datetime">--</span>
            <div><i class="bi bi-person-circle fs-3"></i> Usuario</div>
        </div>
    </div>

    <!-- Selector de variables -->
    <div class="card p-3 mb-3">
        <h5>Mostrar variables</h5>
        <div class="d-flex flex-wrap gap-3">
            <label class="form-check-label">
                <input type="checkbox" class="form-check-input var-toggle" data-var="temperatura" checked> Temperatura
            </label>
            <label class="form-check-label">
                <input type="checkbox" class="form-check-input var-toggle" data-var="humedad" checked> Humedad
            </label>
            <label class="form-check-label">
                <input type="checkbox" class="form-check-input var-toggle" data-var="presion" checked> Presión
            </label>
            <label class="form-check-label">
                <input type="checkbox" class="form-check-input var-toggle" data-var="calidad" checked> Calidad Aire
            </label>
            <label class="form-check-label">
                <input type="checkbox" class="form-check-input var-toggle" data-var="indice" checked> Índice
            </label>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="row">
        <div class="col-12">
            <div class="chart-card">
                <h5>Evolución de variables (últimos <?= count($temperaturas) ?> registros)</h5>
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="chart-card">
                <h5>Promedios diarios</h5>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    <footer>AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0</footer>
</div>

<script type="module" src="<?= base_url('assets/app.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechas = <?= json_encode($fechas) ?>;
    const temps = <?= json_encode($temperaturas) ?>;
    const hums = <?= json_encode($humedades) ?>;
    const pres = <?= json_encode($presiones) ?>;
    const cals = <?= json_encode($calidades) ?>;
    const inds = <?= json_encode($indices) ?>;

    const colors = {
        temperatura: '#ef4444',
        humedad: '#2563eb',
        presion: '#9333ea',
        calidad: '#16a34a',
        indice: '#f59e0b'
    };

    // Gráfica de líneas
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    const lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: fechas.map(f => f.substring(11, 16)),
            datasets: [
                { label: 'Temperatura', data: temps, borderColor: colors.temperatura, tension: 0.1, fill: false },
                { label: 'Humedad', data: hums, borderColor: colors.humedad, tension: 0.1, fill: false },
                { label: 'Presión', data: pres, borderColor: colors.presion, tension: 0.1, fill: false },
                { label: 'Calidad Aire', data: cals, borderColor: colors.calidad, tension: 0.1, fill: false },
                { label: 'Índice', data: inds, borderColor: colors.indice, tension: 0.1, fill: false }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: false }
            }
        }
    });

    // Gráfica de barras (promedios diarios)
    const diarios = <?= json_encode($promedios_diarios) ?>;
    const dias = Object.keys(diarios);
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: dias,
            datasets: [
                { label: 'Temp prom', data: dias.map(d => diarios[d].temp), backgroundColor: colors.temperatura + '99' },
                { label: 'Hum prom', data: dias.map(d => diarios[d].hum), backgroundColor: colors.humedad + '99' },
                { label: 'Pres prom', data: dias.map(d => diarios[d].pres), backgroundColor: colors.presion + '99' },
                { label: 'Cal prom', data: dias.map(d => diarios[d].cal), backgroundColor: colors.calidad + '99' },
                { label: 'Índice prom', data: dias.map(d => diarios[d].ind), backgroundColor: colors.indice + '99' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Toggle de variables
    document.querySelectorAll('.var-toggle').forEach(cb => {
        cb.addEventListener('change', function() {
            const varName = this.dataset.var;
            const index = ['temperatura', 'humedad', 'presion', 'calidad', 'indice'].indexOf(varName);
            lineChart.data.datasets[index].hidden = !this.checked;
            lineChart.update();
        });
    });

    // Reloj y estado (igual que en otras vistas)
    function updateClock() {
        const now = new Date();
        document.getElementById('topbar-datetime').textContent =
            now.toLocaleDateString('es-MX') + ' ' + now.toLocaleTimeString('es-MX');
    }
    setInterval(updateClock, 1000);
    updateClock();

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
});
</script>
</body>
</html>