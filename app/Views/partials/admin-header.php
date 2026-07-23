<header class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-body-secondary mb-0"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <?php if ($createPermission && \App\Core\Auth::can($createPermission)): ?>
        <a class="btn btn-primary" href="<?= htmlspecialchars(url($createUrl), ENT_QUOTES, 'UTF-8') ?>">
            Add <?= htmlspecialchars($singular, ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endif; ?>
</header>
