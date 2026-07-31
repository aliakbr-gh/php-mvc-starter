<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e((string) $status) ?> | <?= e($title) ?></title>
    <link rel="stylesheet" href="<?= asset('theme.css') ?>">
    <link rel="stylesheet" href="<?= asset('style.css') ?>">
    <script src="<?= asset('theme.js') ?>"></script>
</head>
<body>
    <div class="error-page">
        <div class="card error-card">
            <p class="error-code"><?= (int) $status ?></p>
            <h1><?= e($title) ?></h1>
            <p><?= e($message !== '' ? $message : $defaultMessage) ?></p>
            <a class="button" href="<?= url(auth() ? 'dashboard' : 'login') ?>">Go back home</a>
        </div>
    </div>
</body>
</html>
