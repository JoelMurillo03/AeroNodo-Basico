<div class="sidebar">
    <div class="logo">
        <h3><i class="bi bi-cloud"></i> AeroNodo</h3>
        <p>Monitoreo Ambiental IoT</p>
    </div>

    <div class="menu">
        <?php
        $menuItems = [
            'dashboard' => ['/', 'bi-house', 'Dashboard'],
            'historial' => ['/historial', 'bi-clock-history', 'Historial'],
            'graficas' => ['/graficas', 'bi-graph-up', 'Gráficas'],
            'alertas' => ['/alertas', 'bi-bell', 'Alertas'],
            'dispositivo' => ['/dispositivo', 'bi-cpu', 'Dispositivo'],
            'configuracion' => ['/configuracion', 'bi-gear', 'Configuración'],
            'acerca-de' => ['/acerca-de', 'bi-info-circle', 'Acerca de']
        ];

        $currentUri = service('uri')->getPath();

        // Contar alertas activas para el badge
        $db = \Config\Database::connect();
        $alertasActivas = $db->table('alertas')->where('nivel !=', 'normal')->countAllResults(false);
        ?>

        <?php foreach ($menuItems as $key => $item): ?>
            <?php
            $url = $item[0];
            $icon = $item[1];
            $label = $item[2];
            $active = ($currentUri === $url || strpos($currentUri, $url) === 0) ? 'active' : '';
            ?>
            <a href="<?= base_url($url) ?>" class="<?= $active ?>">
                <i class="bi <?= $icon ?>"></i> <?= $label ?>
                <?php if ($key === 'alertas' && $alertasActivas > 0): ?>
                    <span class="badge bg-danger rounded-pill float-end"><?= $alertasActivas ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="p-3">
        <div class="system-box">
            <h5>Estado del Sistema</h5>
            <p id="sidebar-wifi">🟡 WiFi: Cargando...</p>
            <p id="sidebar-api">🟡 API: Cargando...</p>
            <p>Última actualización:</p>
            <small id="sidebar-fecha">--</small>
        </div>
    </div>
</div>