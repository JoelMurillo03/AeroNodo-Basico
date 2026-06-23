<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Historial</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<?= view('components/sidebar') ?>

<div class="main">
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <h3>Historial</h3>
            <small>Registros completos del sistema</small>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span id="topbar-status" class="status-online">● Conectado</span>
            <i class="bi bi-wifi fs-4"></i>
            <span id="topbar-datetime">--</span>
            <div><i class="bi bi-person-circle fs-3"></i> Usuario</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card p-3 mb-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label>Desde</label>
                <input type="date" name="desde" class="form-control" value="<?= $desde ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label>Hasta</label>
                <input type="date" name="hasta" class="form-control" value="<?= $hasta ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label>Buscar (ID)</label>
                <input type="text" name="buscar" class="form-control" placeholder="ID..." value="<?= $buscar ?? '' ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="<?= base_url('historial') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i>
                </a>
                <a href="<?= base_url('historial/exportar') . (!empty($desde) || !empty($hasta) ? '?' . http_build_query(['desde' => $desde, 'hasta' => $hasta]) : '') ?>" class="btn btn-success">
                    <i class="bi bi-download"></i> CSV
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Temp (°C)</th>
                        <th>Hum (%)</th>
                        <th>Presión (hPa)</th>
                        <th>Calidad Aire (ppm)</th>
                        <th>Índice</th>
                        <th>WiFi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No hay registros</td></tr>
                    <?php else: ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= fecha_local($row['fecha']) ?></td>
                                <td><?= $row['temperatura'] ?></td>
                                <td><?= $row['humedad'] ?></td>
                                <td><?= $row['presion'] ?? '-' ?></td>
                                <td><?= $row['calidad_aire'] ?? '-' ?></td>
                                <td><?= $row['indice'] ?? '-' ?></td>
                                <td>
                                    <?php if ($row['estado_wifi']): ?>
                                        <span class="badge bg-success">Conectado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Desconectado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
        <div class="text-muted small">Total: <?= $total ?? 0 ?> registros</div>
    </div>

    <footer>AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0</footer>
</div>

<script type="module" src="<?= base_url('assets/app.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar estado y reloj (igual que en dashboard)
    function updateClock() {
        const now = new Date();
        document.getElementById('topbar-datetime').textContent =
            now.toLocaleDateString('es-MX') + ' ' + now.toLocaleTimeString('es-MX');
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Estado del dispositivo
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