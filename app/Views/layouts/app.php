<!DOCTYPE html>
<html lang="en" data-app-slug="<?= e($GLOBALS['config']['slug']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? $GLOBALS['config']['name']) ?> | <?= e($GLOBALS['config']['name']) ?></title>
    <?php if (appSettings()['favicon'] !== ''): ?>
        <link rel="icon" href="<?= url(appSettings()['favicon']) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= asset('theme.css') ?>">
    <link rel="stylesheet" href="<?= asset('style.css') ?>">
    <script src="<?= asset('theme.js') ?>"></script>
    <script src="<?= asset('searchable-select.js') ?>" defer></script>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/loader.php'; ?>
    <?php require dirname(__DIR__) . '/partials/toaster.php'; ?>

    <?php if (auth()): ?>
        <?php require dirname(__DIR__) . '/partials/header.php'; ?>
        <div class="container"><?php require $viewFile; ?></div>
    <?php else: ?>
        <div class="login-page">
            <div class="<?= str_contains($viewFile, '/health/') ? 'health-container' : 'login-container' ?>">
                <?php require $viewFile; ?>
            </div>
        </div>
    <?php endif; ?>

    <script src="<?= asset('toast.js') ?>" defer></script>
</body>
</html>
