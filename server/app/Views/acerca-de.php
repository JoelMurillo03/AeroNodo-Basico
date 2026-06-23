<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Acerca de</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<?= view('components/sidebar') ?>

<div class="main">
    <div class="topbar">
        <div>
            <h3>Acerca de</h3>
            <small>Información del proyecto</small>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span id="topbar-status" class="status-online">● Conectado</span>
            <i class="bi bi-wifi fs-4"></i>
            <span id="topbar-datetime">--</span>
            <div><i class="bi bi-person-circle fs-3"></i> Usuario</div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4">
                <h1 class="display-6"><?= $nombre ?></h1>
                <p class="lead"><?= $descripcion ?></p>
                <hr>
                <h5>📚 Tecnologías utilizadas</h5>
                <ul class="list-unstyled row">
                    <?php foreach ($tecnologias as $tech => $desc): ?>
                        <li class="col-md-6"><strong><?= $tech ?>:</strong> <?= $desc ?></li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <p><strong>Autor:</strong> <?= $autor ?></p>
                <p><strong>Año:</strong> <?= $anio ?></p>
                <p><strong>Versión:</strong> <?= $version ?></p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3">
                <h5>📊 Diagramas</h5>
                <ul class="list-unstyled">
                    <?php foreach ($diagramas as $nombre => $archivo): ?>
                        <li><a href="<?= base_url('diagrams/' . $archivo) ?>" target="_blank"><i class="bi bi-file-image"></i> <?= $nombre ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <p class="text-muted small mt-3">Los diagramas se encuentran en la carpeta <code>diagrams/</code></p>
            </div>
        </div>
    </div>

    <footer>AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0</footer>
</div>

<script type="module" src="<?= base_url('assets/app.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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