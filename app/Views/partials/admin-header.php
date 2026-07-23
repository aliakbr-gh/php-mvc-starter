<header class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= e($heading) ?></h1>
        <p class="text-body-secondary mb-0"><?= e($description) ?></p>
    </div>
    <?php if ($createPermission && \App\Core\Auth::can($createPermission)): ?>
        <a class="btn btn-primary" href="<?= e(url($createUrl)) ?>">
            Add <?= e($singular) ?>
        </a>
    <?php endif; ?>
</header>