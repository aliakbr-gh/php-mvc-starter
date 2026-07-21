<header class="module-head">
    <div><span class="eyebrow">ACCESS CONTROL</span><h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1><p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p></div>
    <?php if ($createPermission && \App\Core\Auth::can($createPermission)): ?><a class="button" href="<?= htmlspecialchars(url($createUrl), ENT_QUOTES, 'UTF-8') ?>">Add <?= htmlspecialchars($singular, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
</header>
