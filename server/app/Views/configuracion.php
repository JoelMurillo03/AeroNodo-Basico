<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Configuración</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<?= view('components/sidebar') ?>

<div class="main">
    <div class="topbar">
        <div>
            <h3>Configuración</h3>
            <small>Ajustes del sistema</small>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span id="topbar-status" class="status-online">● Conectado</span>
            <i class="bi bi-wifi fs-4"></i>
            <span id="topbar-datetime">--</span>
            <div><i class="bi bi-person-circle fs-3"></i> Usuario</div>
        </div>
    </div>

    <?php if (session('mensaje')): ?>
        <div class="alert alert-success"><?= session('mensaje') ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('configuracion/guardar') ?>" class="row">
        <?= csrf_field() ?>

        <div class="col-md-6">
            <div class="card p-3">
                <h5><i class="bi bi-sliders"></i> Umbrales de alerta</h5>
                <hr>

                <div class="mb-3">
                    <label>Calidad del Aire - Moderado (ppm)</label>
                    <input type="number" name="umbral_calidad_moderado" class="form-control"
                           value="<?= $configs['umbral_calidad_moderado']['valor'] ?? '500' ?>">
                </div>
                <div class="mb-3">
                    <label>Calidad del Aire - Crítico (ppm)</label>
                    <input type="number" name="umbral_calidad_critico" class="form-control"
                           value="<?= $configs['umbral_calidad_critico']['valor'] ?? '800' ?>">
                </div>
                <div class="mb-3">
                    <label>Temperatura mínima (°C)</label>
                    <input type="number" step="0.1" name="umbral_temp_minima" class="form-control"
                           value="<?= $configs['umbral_temp_minima']['valor'] ?? '15' ?>">
                </div>
                <div class="mb-3">
                    <label>Temperatura máxima (°C)</label>
                    <input type="number" step="0.1" name="umbral_temp_maxima" class="form-control"
                           value="<?= $configs['umbral_temp_maxima']['valor'] ?? '30' ?>">
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3">
                <h5><i class="bi bi-sliders"></i> Configuración adicional</h5>
                <hr>

                <div class="mb-3">
                    <label>Humedad mínima (%)</label>
                    <input type="number" step="0.1" name="umbral_hum_minima" class="form-control"
                           value="<?= $configs['umbral_hum_minima']['valor'] ?? '40' ?>">
                </div>
                <div class="mb-3">
                    <label>Humedad máxima (%)</label>
                    <input type="number" step="0.1" name="umbral_hum_maxima" class="form-control"
                           value="<?= $configs['umbral_hum_maxima']['valor'] ?? '70' ?>">
                </div>
                <div class="mb-3">
                    <label>Intervalo de actualización (segundos)</label>
                    <input type="number" name="intervalo_actualizacion" class="form-control"
                           value="<?= $configs['intervalo_actualizacion']['valor'] ?? '5' ?>">
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar configuración</button>
        </div>
    </form>

    <footer>AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0</footer>
</div>

<script type="module" src="<?= base_url('assets/app.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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