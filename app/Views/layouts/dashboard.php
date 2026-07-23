<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(page_title($title ?? null)) ?></title>
    <?php if (favicon_url()): ?>
        <link rel="icon" href="<?= e(favicon_url()) ?>">
    <?php endif; ?>
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            document.documentElement.setAttribute('data-bs-theme', saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css') . '?v=' . filemtime(BASE_PATH . '/public/assets/css/app.css')) ?>">
</head>

<?php
$authenticated = \App\Core\Auth::check();
$user = $user ?? \App\Core\Auth::user();
$navigationStyle = $authenticated
    ? \App\Core\AppSettings::navigationStyle()
    : \App\Core\AppSettings::NAVIGATION_SIDEBAR;
?>
<body class="<?= $authenticated ? 'dashboard-body navigation-' . e($navigationStyle) : '' ?>">
    <div class="page-loader" role="status" aria-label="Loading page">
        <div class="spinner-border text-primary" aria-hidden="true"></div>
        <span class="visually-hidden">Loading…</span>
    </div>

    <?php if ($authenticated): ?>
        <?php if ($navigationStyle === \App\Core\AppSettings::NAVIGATION_SIDEBAR): ?>
        <header class="dashboard-topbar navbar bg-body sticky-top">
            <div class="container-fluid px-3 px-lg-4">
                <button class="sidebar-trigger btn btn-light d-inline-flex align-items-center justify-content-center"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#dashboardSidebar"
                    aria-controls="dashboardSidebar" aria-label="Open navigation">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
                <a class="navbar-brand brand d-flex align-items-center gap-2 ms-2 me-auto"
                    href="<?= e(url('dashboard')) ?>">
                    <?php if (logo_url()): ?>
                        <img src="<?= e(logo_url()) ?>" alt="">
                    <?php endif; ?>
                    <span><?= e(app_name()) ?></span>
                </a>
            </div>
        </header>

        <aside class="sidebar offcanvas offcanvas-start" tabindex="-1" id="dashboardSidebar"
            aria-labelledby="dashboardSidebarLabel">
            <div class="offcanvas-header sidebar-header">
                <a class="d-flex align-items-center gap-2 fw-semibold text-decoration-none text-body brand"
                    href="<?= e(url('dashboard')) ?>">
                    <?php if (logo_url()): ?>
                        <img src="<?= e(logo_url()) ?>"
                            alt="<?= e(app_name()) ?>">
                    <?php endif; ?>
                    <span id="dashboardSidebarLabel"><?= e(app_name()) ?></span>
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                    aria-label="Close navigation"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                <p class="nav-section-label">Main menu</p>
                <nav class="side-nav app-navigation nav flex-column">
                    <a class="nav-link" href="<?= e(url('dashboard')) ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <?php if (\App\Core\Auth::can('users.view') || \App\Core\Auth::can('roles.view') || \App\Core\Auth::can('permissions.view')): ?>
                        <button class="nav-link nav-dropdown-toggle collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#adminSubmenu"
                            aria-expanded="false" aria-controls="adminSubmenu">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 20h16M5 20V9l7-5 7 5v11M9 20v-6h6v6" />
                            </svg>
                            <span>Access Control</span>
                            <svg class="dropdown-chevron ms-auto" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m8 10 4 4 4-4" />
                            </svg>
                        </button>
                        <div class="collapse nav-submenu" id="adminSubmenu">
                            <?php if (\App\Core\Auth::can('users.view')): ?>
                                <a class="nav-link" href="<?= e(url('admin/users')) ?>">Users</a>
                            <?php endif; ?>
                            <?php if (\App\Core\Auth::can('roles.view')): ?>
                                <a class="nav-link" href="<?= e(url('admin/roles')) ?>">Roles</a>
                            <?php endif; ?>
                            <?php if (\App\Core\Auth::can('permissions.view')): ?>
                                <a class="nav-link" href="<?= e(url('admin/permissions')) ?>">Permissions</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <a class="nav-link" href="<?= e(url('settings')) ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 8.97 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.52-1H3v-4h.08A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 8.97 4.6 1.7 1.7 0 0 0 10 3.08V3h4v.08a1.7 1.7 0 0 0 1.03 1.52 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.52 1H21v4h-.08A1.7 1.7 0 0 0 19.4 15Z" />
                        </svg>
                        <span>App Settings</span>
                    </a>
                </nav>

                <div class="sidebar-footer mt-auto">
                    <div class="sidebar-user">
                        <div class="user-avatar" aria-hidden="true">
                            <?= e(strtoupper(substr($user['name'], 0, 1))) ?>
                        </div>
                        <div class="min-w-0">
                            <strong class="d-block text-truncate"><?= e($user['name']) ?></strong>
                            <span class="small"><?= e($user['role_name']) ?></span>
                        </div>
                    </div>
                    <div class="sidebar-actions">
                        <button class="theme-toggle sidebar-action" type="button"
                            aria-label="Switch color theme">
                            <?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?>
                            <span class="theme-label">Dark mode</span>
                        </button>
                        <form method="post" action="<?= e(url('logout')) ?>">
                            <?= csrf_field() ?>
                            <button class="sidebar-action sidebar-action-danger" type="submit">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M10 17l5-5-5-5M15 12H3M14 4h6v16h-6" />
                                </svg>
                                <span>Log out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
        <?php else: ?>
            <header class="header-navigation navbar navbar-expand-lg bg-body sticky-top border-bottom">
                <div class="container-fluid px-3 px-lg-4">
                    <a class="navbar-brand brand d-flex align-items-center gap-2" href="<?= e(url('dashboard')) ?>">
                        <?php if (logo_url()): ?>
                            <img src="<?= e(logo_url()) ?>"
                                alt="<?= e(app_name()) ?>">
                        <?php endif; ?>
                        <span><?= e(app_name()) ?></span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#headerNavigation" aria-controls="headerNavigation"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="headerNavigation">
                        <nav class="app-navigation navbar-nav me-auto mb-2 mb-lg-0">
                            <a class="nav-link" href="<?= e(url('dashboard')) ?>">Dashboard</a>
                            <?php if (\App\Core\Auth::can('users.view') || \App\Core\Auth::can('roles.view') || \App\Core\Auth::can('permissions.view')): ?>
                                <div class="nav-item dropdown">
                                    <button class="nav-link dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Access Control
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if (\App\Core\Auth::can('users.view')): ?>
                                            <li><a class="dropdown-item" href="<?= e(url('admin/users')) ?>">Users</a></li>
                                        <?php endif; ?>
                                        <?php if (\App\Core\Auth::can('roles.view')): ?>
                                            <li><a class="dropdown-item" href="<?= e(url('admin/roles')) ?>">Roles</a></li>
                                        <?php endif; ?>
                                        <?php if (\App\Core\Auth::can('permissions.view')): ?>
                                            <li><a class="dropdown-item" href="<?= e(url('admin/permissions')) ?>">Permissions</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <a class="nav-link" href="<?= e(url('settings')) ?>">App Settings</a>
                        </nav>
                        <div class="header-navigation-actions d-flex align-items-center gap-2">
                            <button class="theme-toggle btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                                type="button" aria-label="Switch color theme">
                                <?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?>
                                <span class="theme-label">Dark mode</span>
                            </button>
                            <span class="small text-body-secondary"><?= e($user['name']) ?></span>
                            <form method="post" action="<?= e(url('logout')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-danger btn-sm" type="submit">Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
        <?php endif; ?>

        <main class="dashboard-main"><?= $content ?></main>
        <?php require __DIR__ . '/../partials/confirm-modal.php'; ?>
    <?php else: ?>
        <button class="theme-toggle auth-theme-toggle btn btn-outline-secondary btn-sm d-inline-flex align-items-center"
            type="button" aria-label="Switch color theme">
            <?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?>
        </button>
        <main class="auth-page container d-flex align-items-center justify-content-center py-5">
            <?= $content ?>
        </main>
    <?php endif; ?>

    <?php require __DIR__ . '/../partials/toasts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= e(url('assets/js/app.js') . '?v=' . filemtime(BASE_PATH . '/public/assets/js/app.js')) ?>"></script>
</body>

</html>
