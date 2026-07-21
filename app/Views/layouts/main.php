<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(page_title($title ?? null), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="page-loading">
    <div class="top-loader" aria-hidden="true"><span></span></div>
    <header class="nav">
        <a class="brand" href="<?= htmlspecialchars(url(), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (logo_url()): ?>
                <img src="<?= htmlspecialchars(logo_url(), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars((string) config('branding.logo_alt', app_name()), ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <span><?= htmlspecialchars(app_name(), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <nav class="nav-links">
            <?php if (\App\Core\Auth::check()): ?>
                <a href="<?= htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
                <a href="<?= htmlspecialchars(url('register'), ENT_QUOTES, 'UTF-8') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="container"><?= $content ?></main>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>
    <script src="<?= htmlspecialchars(url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
