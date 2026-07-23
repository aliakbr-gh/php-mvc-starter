<header class="mb-4">
    <h1 class="h3 mb-1">Create role</h1>
    <p class="text-body-secondary mb-0">Choose the exact permissions granted to this role.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/roles')) ?>">
    <?= csrf_field() ?>
    <?php require __DIR__ . '/fields.php'; ?>
</form>
