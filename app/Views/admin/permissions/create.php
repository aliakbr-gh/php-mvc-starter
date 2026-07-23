<header class="mb-4">
    <h1 class="h3 mb-1">Create permission</h1>
    <p class="text-body-secondary mb-0">Use a module.action slug such as reports.view.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/permissions')) ?>">
    <?= csrf_field() ?>
    <?php $record = null; require __DIR__ . '/fields.php'; ?>
</form>
