<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$initials = implode('', array_map(static fn (string $part): string => strtoupper($part[0] ?? ''), array_filter(explode(' ', user()['name']))));
$initials = substr($initials, 0, 2) ?: 'U';
$userManagementActive = str_contains($currentPath, '/users')
    || str_contains($currentPath, '/roles')
    || str_contains($currentPath, '/permissions');
$systemSettingsActive = str_contains($currentPath, '/app-settings')
    || str_contains($currentPath, '/logs')
    || str_contains($currentPath, '/rate-limits')
    || str_contains($currentPath, '/database-backup');
$userManagementVisible = hasPermission('users.view')
    || hasPermission('roles.view')
    || hasRole('product-owner', 'admin');
$systemSettingsVisible = hasPermission('settings.view')
    || hasPermission('logs.view')
    || hasPermission('rate-limits.view')
    || hasPermission('database-backup.view');
$activityLogsActive = str_contains($currentPath, '/activity-logs');
?>
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-brand">
            <button class="icon-button" type="button" data-menu-toggle aria-label="Open main menu">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            <a class="header-brand" href="<?= url('dashboard') ?>">
                <span class="brand-mark">
                    <?php if (appSettings()['logo'] !== ''): ?>
                        <img src="<?= url(appSettings()['logo']) ?>" alt="">
                    <?php else: ?>
                        <?= e(strtoupper(substr(appSettings()['app_name'], 0, 2))) ?>
                    <?php endif; ?>
                </span>
                <span><?= e(appSettings()['app_name']) ?></span>
            </a>
        </div>

        <div class="user-menu-wrap">
            <button class="user-menu-trigger" type="button" data-user-menu-toggle aria-label="Open user menu" aria-expanded="false">
                <span class="avatar">
                    <?= e($initials) ?>
                    <i></i>
                </span>
                <span class="user-menu-copy">
                    <strong><?= e(user()['name']) ?></strong>
                    <small>@<?= e(user()['username']) ?></small>
                </span>
                <svg class="chevron" viewBox="0 0 20 20" width="16" height="16" fill="none" aria-hidden="true">
                    <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="user-dropdown" data-user-menu>
                <div class="user-dropdown-heading">
                    <strong><?= e(user()['name']) ?></strong>
                    <small>@<?= e(user()['username']) ?></small>
                </div>
                <div class="user-dropdown-links">
                    <a class="dropdown-action" href="<?= url('profile') ?>">My profile</a>
                    <a class="dropdown-action" href="<?= url('profile/password') ?>">Change password</a>
                </div>
                <div class="user-dropdown-row">
                    <span>Color theme</span>
                    <button class="icon-button" type="button" data-theme-toggle aria-label="Toggle color theme">
                        <span class="theme-icon-light">☾</span>
                        <span class="theme-icon-dark">☀</span>
                    </button>
                </div>
                <form class="logout-form" method="post" action="<?= url('logout') ?>">
                    <?= csrf_field() ?>
                    <button class="dropdown-action danger-text" type="submit">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay" data-sidebar-overlay aria-hidden="true">
    <div class="sidebar" data-sidebar aria-label="Main navigation">
        <div class="sidebar-heading">
            <div>
                <strong><?= e(appSettings()['app_name']) ?></strong>
            </div>
            <button class="icon-button" type="button" data-sidebar-close aria-label="Close menu">×</button>
        </div>
        <p class="nav-label">Main menu</p>
        <div class="sidebar-nav">
            <?php if (hasPermission('dashboard.view')): ?>
                <a
                    class="nav-link <?= str_ends_with($currentPath, '/dashboard') ? 'is-active' : '' ?>"
                    href="<?= url('dashboard') ?>"
                >
                    Dashboard
                </a>
            <?php endif; ?>

            <?php if ($userManagementVisible): ?>
                <div class="nav-group">
                    <button
                        class="nav-group-toggle <?= $userManagementActive ? 'is-active' : '' ?>"
                        type="button"
                        data-nav-group-toggle
                        aria-expanded="<?= $userManagementActive ? 'true' : 'false' ?>"
                    >
                        <span>User Management</span>
                        <svg viewBox="0 0 20 20" width="16" height="16" fill="none" aria-hidden="true">
                            <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="nav-group-items <?= $userManagementActive ? 'is-open' : '' ?>">
                        <?php if (hasPermission('users.view')): ?>
                            <a class="nav-link <?= str_contains($currentPath, '/users') ? 'is-active' : '' ?>" href="<?= url('users') ?>">Users</a>
                        <?php endif; ?>
                        <?php if (hasPermission('roles.view')): ?>
                            <a class="nav-link <?= str_contains($currentPath, '/roles') ? 'is-active' : '' ?>" href="<?= url('roles') ?>">Roles</a>
                        <?php endif; ?>
                        <?php if (hasRole('product-owner', 'admin')): ?>
                            <a class="nav-link <?= str_contains($currentPath, '/permissions') ? 'is-active' : '' ?>" href="<?= url('permissions') ?>">Permissions</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (hasPermission('activity-logs.view')): ?>
                <a
                    class="nav-link <?= $activityLogsActive ? 'is-active' : '' ?>"
                    href="<?= url('activity-logs') ?>"
                >
                    Activity Logs
                </a>
            <?php endif; ?>

            <?php if ($systemSettingsVisible): ?>
                <div class="nav-group">
                    <button
                        class="nav-group-toggle <?= $systemSettingsActive ? 'is-active' : '' ?>"
                        type="button"
                        data-nav-group-toggle
                        aria-expanded="<?= $systemSettingsActive ? 'true' : 'false' ?>"
                    >
                        <span>System Settings</span>
                        <svg viewBox="0 0 20 20" width="16" height="16" fill="none" aria-hidden="true">
                            <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="nav-group-items <?= $systemSettingsActive ? 'is-open' : '' ?>">
                        <?php if (hasPermission('settings.view')): ?>
                            <a class="nav-link <?= str_contains($currentPath, '/app-settings') ? 'is-active' : '' ?>" href="<?= url('app-settings') ?>">Application Settings</a>
                        <?php endif; ?>
                        <?php if (hasPermission('logs.view')): ?>
                            <a class="nav-link <?= str_contains($currentPath, '/logs') ? 'is-active' : '' ?>" href="<?= url('logs') ?>">Logs</a>
                        <?php endif; ?>
                        <?php if (hasPermission('rate-limits.view')): ?>
                            <a class="nav-link <?= str_contains($currentPath, '/rate-limits') ? 'is-active' : '' ?>" href="<?= url('rate-limits') ?>">Rate Limits</a>
                        <?php endif; ?>
                        <?php if (hasPermission('database-backup.view')): ?>
                            <a class="nav-link <?= str_contains($currentPath, '/database-backup') ? 'is-active' : '' ?>" href="<?= url('database-backup') ?>">Database Backup</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
