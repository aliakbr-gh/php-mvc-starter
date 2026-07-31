<div class="health-page">
    <div class="health-heading">
        <div>
            <p class="health-eyebrow">System status</p>
            <h1><?= $healthy ? 'All systems operational' : 'Service requires attention' ?></h1>
            <p>Live server and database health information.</p>
        </div>
        <span class="status-badge <?= $healthy ? 'status-success' : 'status-danger' ?>">
            <?= $healthy ? 'Healthy' : 'Degraded' ?>
        </span>
    </div>

    <div class="health-grid">
        <div class="health-panel">
            <div class="health-panel-heading">
                <h2>Server</h2>
                <span class="status-badge <?= $server['status'] === 'healthy' ? 'status-success' : 'status-danger' ?>">
                    <?= e(ucfirst($server['status'])) ?>
                </span>
            </div>
            <dl class="health-details">
                <div><dt>PHP version</dt><dd><?= e($server['php_version']) ?></dd></div>
                <div><dt>Server</dt><dd><?= e($server['server_software']) ?></dd></div>
                <div><dt>Timezone</dt><dd><?= e($server['timezone']) ?></dd></div>
                <div><dt>Server time</dt><dd><?= e($server['server_time']) ?></dd></div>
                <div>
                    <dt>Storage writable</dt>
                    <dd><?= $server['storage_writable'] ? 'Yes' : 'No' ?></dd>
                </div>
            </dl>
        </div>

        <div class="health-panel">
            <div class="health-panel-heading">
                <h2>Database</h2>
                <span class="status-badge <?= $database['status'] === 'healthy' ? 'status-success' : 'status-danger' ?>">
                    <?= e(ucfirst($database['status'])) ?>
                </span>
            </div>
            <dl class="health-details">
                <div>
                    <dt>Connection</dt>
                    <dd><?= $database['status'] === 'healthy' ? 'Connected' : 'Failed' ?></dd>
                </div>
                <div>
                    <dt>Version</dt>
                    <dd><?= e($database['version'] ?? 'Unavailable') ?></dd>
                </div>
                <div>
                    <dt>Latency</dt>
                    <dd>
                        <?= $database['latency_ms'] === null
                            ? 'Unavailable'
                            : e(number_format((float) $database['latency_ms'], 2)) . ' ms' ?>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
