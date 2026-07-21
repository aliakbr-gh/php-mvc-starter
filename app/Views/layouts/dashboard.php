<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(page_title($title ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard-body page-loading">
    <div class="top-loader" aria-hidden="true"><span></span></div>
    <aside class="sidebar">
        <a class="brand" href="<?= htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (logo_url()): ?>
                <img src="<?= htmlspecialchars(logo_url(), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars((string) config('branding.logo_alt', app_name()), ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <span><?= htmlspecialchars(app_name(), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <div class="user-card">
            <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars($user['role_name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <nav class="side-nav">
            <a href="<?= htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Overview</a>
            <?php if (\App\Core\Auth::can('users.view')): ?>
                <a href="<?= htmlspecialchars(url('admin'), ENT_QUOTES, 'UTF-8') ?>">Admin area</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('users.view')): ?>
                <a href="<?= htmlspecialchars(url('admin/users'), ENT_QUOTES, 'UTF-8') ?>">Users</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('roles.view')): ?>
                <a href="<?= htmlspecialchars(url('admin/roles'), ENT_QUOTES, 'UTF-8') ?>">Roles</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('permissions.view')): ?>
                <a href="<?= htmlspecialchars(url('admin/permissions'), ENT_QUOTES, 'UTF-8') ?>">Permissions</a>
            <?php endif; ?>
            <a href="<?= htmlspecialchars(url(), ENT_QUOTES, 'UTF-8') ?>">View website</a>
        </nav>
        <form class="logout" method="post" action="<?= htmlspecialchars(url('logout'), ENT_QUOTES, 'UTF-8') ?>">
            <?= csrf_field() ?>
            <button type="submit">Log out</button>
        </form>
    </aside>
    <main class="dashboard-main"><?= $content ?></main>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>
    <script src="<?= htmlspecialchars(url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
