<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(page_title($title ?? null), ENT_QUOTES, 'UTF-8') ?></title>
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            document.documentElement.setAttribute('data-bs-theme', saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php $hideHeader = in_array(($title ?? null), ['Login', 'Register'], true); ?>
    <div class="page-loader" role="status" aria-label="Loading page"><div class="spinner-border text-primary" aria-hidden="true"></div><span class="visually-hidden">Loading…</span></div>
    <?php if (!$hideHeader): ?>
    <header class="navbar navbar-expand border-bottom bg-body sticky-top">
      <div class="container py-2">
          <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold brand" href="<?= htmlspecialchars(url(), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (logo_url()): ?>
                <img src="<?= htmlspecialchars(logo_url(), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars((string) config('branding.logo_alt', app_name()), ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <span><?= htmlspecialchars(app_name(), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <nav class="d-flex align-items-center gap-2">
            <button class="theme-toggle btn btn-outline-secondary btn-sm d-inline-flex align-items-center" type="button" aria-label="Switch color theme"><?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?></button>
            <?php if (\App\Core\Auth::check()): ?>
                <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
            <?php else: ?>
                <a class="btn btn-link btn-sm text-decoration-none" href="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
                <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(url('register'), ENT_QUOTES, 'UTF-8') ?>">Register</a>
            <?php endif; ?>
        </nav>
      </div>
    </header>
    <?php else: ?>
        <button class="theme-toggle auth-theme-toggle btn btn-outline-secondary btn-sm d-inline-flex align-items-center" type="button" aria-label="Switch color theme"><?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?></button>
    <?php endif; ?>
    <main class="<?= $hideHeader ? 'auth-page container d-flex align-items-center justify-content-center py-5' : 'container py-5' ?>"><?= $content ?></main>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars(url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
