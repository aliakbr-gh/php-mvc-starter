<header class="mb-4">
    <h1 class="h3 mb-1">Create user</h1>
    <p class="text-body-secondary mb-0">Add an account and assign its role.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/users')) ?>">
    <?= csrf_field() ?>
    <?php $record = null; $passwordRequired = true; require __DIR__ . '/fields.php'; ?>
</form>
