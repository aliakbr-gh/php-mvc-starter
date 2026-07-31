<div class="page-heading">
    <div>
        <h1>Dashboard</h1>
        <p>Monitor and manage your <?= e($GLOBALS['config']['name']) ?> workspace.</p>
    </div>
</div>

<div class="dashboard-grid">
    <?php if (hasPermission('users.view')): ?>
        <a class="card module-card" href="<?= url('users') ?>">
            <div>
                <h2>Users</h2>
                <p>Create accounts and manage team members.</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if (hasPermission('roles.view')): ?>
        <a class="card module-card" href="<?= url('roles') ?>">
            <div>
                <h2>Roles</h2>
                <p>Organize access levels for your team.</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if (hasRole('product-owner', 'admin')): ?>
        <a class="card module-card" href="<?= url('permissions') ?>">
            <div>
                <h2>Permissions</h2>
                <p>Control individual application capabilities.</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if (hasPermission('settings.view')): ?>
        <a class="card module-card" href="<?= url('app-settings') ?>">
            <div>
                <h2>Application Settings</h2>
                <p>Manage branding, logo, and favicon.</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if (hasPermission('logs.view')): ?>
        <a class="card module-card" href="<?= url('logs') ?>">
            <div>
                <h2>Logs</h2>
                <p>Review application requests by date.</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if (hasPermission('rate-limits.view')): ?>
        <a class="card module-card" href="<?= url('rate-limits') ?>">
            <div>
                <h2>Rate Limits</h2>
                <p>Control request limits and blocked IPs.</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if (hasPermission('database-backup.view')): ?>
        <a class="card module-card" href="<?= url('database-backup') ?>">
            <div>
                <h2>Database Backup</h2>
                <p>Download SQL, uploads, or a complete backup.</p>
            </div>
        </a>
    <?php endif; ?>
</div>
