<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(page_title($title ?? null)) ?></title>
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            document.documentElement.setAttribute('data-bs-theme', saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css') . '?v=' . filemtime(BASE_PATH . '/public/assets/css/app.css')) ?>">
</head>

<body>
    <?php $hideHeader = in_array(($title ?? null), ['Login', 'Register'], true); ?>
    <div class="page-loader" role="status" aria-label="Loading page">
        <div class="spinner-border text-primary" aria-hidden="true"></div><span class="visually-hidden">Loading…</span>
    </div>
    <?php if (!$hideHeader): ?>
        <header class="navbar navbar-expand border-bottom bg-body sticky-top">
            <div class="container-fluid app-shell py-2">
                <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold brand"
                    href="<?= e(url()) ?>">
                    <?php if (logo_url()): ?>
                        <img src="<?= e(logo_url()) ?>"
                            alt="<?= e((string) config('branding.logo_alt', app_name())) ?>">
                    <?php endif; ?>
                    <span><?= e(app_name()) ?></span>
                </a>
                <nav class="d-flex align-items-center gap-2">
                    <button class="theme-toggle btn btn-outline-secondary btn-sm d-inline-flex align-items-center"
                        type="button"
                        aria-label="Switch color theme"><?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?></button>
                    <?php if (\App\Core\Auth::check()): ?>
                        <a class="btn btn-primary btn-sm"
                            href="<?= e(url('dashboard')) ?>">Dashboard</a>
                    <?php else: ?>
                        <a class="btn btn-link btn-sm text-decoration-none"
                            href="<?= e(url('login')) ?>">Login</a>
                        <a class="btn btn-primary btn-sm"
                            href="<?= e(url('register')) ?>">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>
    <?php else: ?>
        <button class="theme-toggle auth-theme-toggle btn btn-outline-secondary btn-sm d-inline-flex align-items-center"
            type="button"
            aria-label="Switch color theme"><?php require BASE_PATH . '/app/Views/partials/theme-icons.php'; ?></button>
    <?php endif; ?>
    <main
        class="<?= $hideHeader ? 'auth-page container d-flex align-items-center justify-content-center py-5' : 'container-fluid app-shell py-4 py-lg-5' ?>">
        <?= $content ?></main>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(url('assets/js/app.js') . '?v=' . filemtime(BASE_PATH . '/public/assets/js/app.js')) ?>"></script>
</body>

</html>
