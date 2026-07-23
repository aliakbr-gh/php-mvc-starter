<header class="mb-4">
    <h1 class="h3 mb-1">Edit permission</h1>
    <p class="text-body-secondary mb-0">Update this permission name or slug.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/permissions/' . $record['id'] . '/update')) ?>">
    <?= csrf_field() ?>
    <?php require __DIR__ . '/fields.php'; ?>
</form>
