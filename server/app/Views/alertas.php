<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroNodo Básico - Alertas</title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<?= view('components/sidebar') ?>

<div class="main">
    <div class="topbar">
        <div>
            <h3>Alertas</h3>
            <small>Historial completo de alertas generadas</small>
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
            <div class="col-md-4">
                <label>Nivel</label>
                <select name="nivel" class="form-select">
                    <option value="todas" <?= ($nivel ?? '') === 'todas' ? 'selected' : '' ?>>Todas</option>
                    <option value="critica" <?= ($nivel ?? '') === 'critica' ? 'selected' : '' ?>>Crítica</option>
                    <option value="moderada" <?= ($nivel ?? '') === 'moderada' ? 'selected' : '' ?>>Moderada</option>
                    <option value="normal" <?= ($nivel ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Buscar</label>
                <input type="text" name="buscar" class="form-control" placeholder="ID, nivel, valor..." value="<?= $buscar ?? '' ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Filtrar</button>
                <a href="<?= base_url('alertas') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i></a>
            </div>
        </form>
    </div>

    <!-- Listado -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lectura ID</th>
                        <th>Nivel</th>
                        <th>Valor Detectado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alertas)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No hay alertas</td></tr>
                    <?php else: ?>
                        <?php foreach ($alertas as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['lectura_id'] ?></td>
                                <td>
                                    <?php
                                    $badgeClass = $row['nivel'] === 'critica' ? 'bg-danger' : ($row['nivel'] === 'moderada' ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['nivel']) ?></span>
                                </td>
                                <td><?= $row['valor_detectado'] ?></td>
                                <td><?= $row['fecha'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger eliminar-alerta" data-id="<?= $row['id'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
    </div>

    <footer>AeroNodo Básico - Monitoreo Ambiental IoT | Versión 1.0</footer>
</div>

<script type="module" src="<?= base_url('assets/app.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Eliminar alerta con confirmación
    document.querySelectorAll('.eliminar-alerta').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (!confirm('¿Eliminar esta alerta?')) return;
            fetch('/alertas/' + id, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Error al eliminar'));
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