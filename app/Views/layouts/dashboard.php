<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(page_title($title ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></title>
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            document.documentElement.setAttribute('data-bs-theme', saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard-body">
<div class="page-loader" role="status" aria-label="Loading page">
    <div class="spinner-border text-primary" aria-hidden="true"></div>
    <span class="visually-hidden">Loading…</span></div>
<header class="dashboard-topbar navbar border-bottom bg-body sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashboardSidebar"
                aria-controls="dashboardSidebar" aria-label="Open navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand brand ms-2 me-auto"
           href="<?= htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(app_name(), ENT_QUOTES, 'UTF-8') ?></a>
        <button class="theme-toggle btn btn-outline-secondary d-inline-flex align-items-center" type="button"
                aria-label="Switch color theme"><?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?></button>
    </div>
</header>
<aside class="sidebar offcanvas offcanvas-start" tabindex="-1" id="dashboardSidebar"
       aria-labelledby="dashboardSidebarLabel">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5" id="dashboardSidebarLabel">Navigation</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body sidebar-content">
        <a class="brand" href="<?= htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (logo_url()): ?>
                <img src="<?= htmlspecialchars(logo_url(), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars((string)config('branding.logo_alt', app_name()), ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <span><?= htmlspecialchars(app_name(), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <div class="user-card">
            <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars($user['role_name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <nav class="side-nav nav nav-pills flex-column">
            <a class="nav-link" href="<?= htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Overview</a>
            <?php if (\App\Core\Auth::can('users.view')): ?>
                <a class="nav-link" href="<?= htmlspecialchars(url('admin'), ENT_QUOTES, 'UTF-8') ?>">Admin area</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('users.view')): ?>
                <a class="nav-link" href="<?= htmlspecialchars(url('admin/users'), ENT_QUOTES, 'UTF-8') ?>">Users</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('roles.view')): ?>
                <a class="nav-link" href="<?= htmlspecialchars(url('admin/roles'), ENT_QUOTES, 'UTF-8') ?>">Roles</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('permissions.view')): ?>
                <a class="nav-link" href="<?= htmlspecialchars(url('admin/permissions'), ENT_QUOTES, 'UTF-8') ?>">Permissions</a>
            <?php endif; ?>
            <a class="nav-link" href="<?= htmlspecialchars(url(), ENT_QUOTES, 'UTF-8') ?>">View website</a>
        </nav>
        <form class="logout" method="post" action="<?= htmlspecialchars(url('logout'), ENT_QUOTES, 'UTF-8') ?>">
            <?= csrf_field() ?>
            <button type="submit">Log out</button>
        </form>
        <button class="theme-toggle btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2"
                type="button"
                aria-label="Switch color theme"><?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?><span
                    class="theme-label">Dark mode</span></button>
    </div>
</aside>
<main class="dashboard-main"><?= $content ?></main>
<?php require __DIR__ . '/../partials/toasts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= htmlspecialchars(url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
