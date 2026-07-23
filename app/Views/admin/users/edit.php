<header class="mb-4">
    <h1 class="h3 mb-1">Edit user</h1>
    <p class="text-body-secondary mb-0">Update account details and role assignment.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/users/' . $record['id'] . '/update')) ?>">
    <?= csrf_field() ?>
    <?php $passwordRequired = false; require __DIR__ . '/fields.php'; ?>
</form>
