<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Core MVC', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="page-loading">
    <div class="top-loader" aria-hidden="true"><span></span></div>
    <header class="nav">
        <a href="<?= htmlspecialchars(url(), ENT_QUOTES, 'UTF-8') ?>">Core MVC</a>
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
