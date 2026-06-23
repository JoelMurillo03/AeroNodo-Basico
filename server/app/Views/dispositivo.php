<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Dispositivo</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?= view('components/sidebar') ?>

<div class="main">
    <div class="topbar">
        <div>
            <h3>Dispositivo</h3>
            <small>Estado y detalles del ESP32</small>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span id="topbar-status" class="status-online">● Conectado</span>
            <i class="bi bi-wifi fs-4"></i>
            <span id="topbar-datetime">--</span>
            <div><i class="bi bi-person-circle fs-3"></i> Usuario</div>
        </div>
    </div>

    <!-- Estado del dispositivo -->
    <div class="row">
        <div class="col-md-6">
            <div class="card-info">
                <h5><i class="bi bi-cpu"></i> ESP32 DevKit V1</h5>
                <hr>
                <p><strong>WiFi:</strong> 
                    <?php if ($dispositivo['estado_wifi']): ?>
                        <span class="badge bg-success">Conectado</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Desconectado</span>
                    <?php endif; ?>
                </p>
                <p><strong>Intensidad de señal:</strong> <?= $dispositivo['intensidad_wifi'] ?? '--' ?> dBm</p>
                <p><strong>Última actualización:</strong> <?= $dispositivo['ultima_actualizacion'] ?? '--' ?></p>
                <button class="btn btn-primary" id="btn-refresh-dispositivo">
                    <i class="bi bi-arrow-repeat"></i> Actualizar estado
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-info">
                <h5><i class="bi bi-info-circle"></i> Información del hardware</h5>
                <hr>
                <p><strong>Microcontrolador:</strong> ESP32</p>
                <p><strong>Sensores:</strong> BME280 (Temp, Hum, Pres), MQ-135 (Calidad Aire)</p>
                <p><strong>Actuadores:</strong> LED RGB</p>
                <p><strong>Firmware:</strong> v1.0.0</p>
            </div>
        </div>
    </div>

    <!-- Gráfica de evolución WiFi -->
    <div class="chart-card mt-3">
        <h5>Evolución de la conexión WiFi</h5>
        <canvas id="wifiChart"></canvas>
    </div>

    <footer>AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0</footer>
</div>

<script type="module" src="<?= base_url('assets/app.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechas = <?= json_encode($fechas) ?>;
    const estados = <?= json_encode($estados) ?>;

    // Gráfica de WiFi (estado 0/1)
    const ctx = document.getElementById('wifiChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: fechas.map(f => f.substring(11, 16)),
            datasets: [{
                label: 'Estado WiFi',
                data: estados,
                borderColor: '#2563eb',
                backgroundColor: '#2563eb33',
                fill: true,
                stepped: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: { min: 0, max: 1, ticks: { stepSize: 1, callback: v => v ? 'Conectado' : 'Desconectado' } }
            }
        }
    });

    // Botón refresh manual
    document.getElementById('btn-refresh-dispositivo').addEventListener('click', function() {
        this.innerHTML = '<i class="bi bi-arrow-repeat spinning"></i> Actualizando...';
        fetch('/api/dispositivo/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                estado_wifi: true,
                intensidad_wifi: Math.floor(Math.random() * 50) - 80
            })
        })
        .then(res => res.json())
        .then(() => location.reload())
        .catch(err => alert('Error al actualizar'))
        .finally(() => {
            this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Actualizar estado';
        });
    });

    // Reloj y estado
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