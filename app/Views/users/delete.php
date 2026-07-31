<div class="card narrow">
    <h1>Delete user</h1>
    <p>Delete <strong><?= e($deletingUser['name']) ?></strong> (<?= e($deletingUser['username']) ?>)?</p>
    <p>This action cannot be undone.</p>

    <form method="post" action="<?= url('users/destroy') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $deletingUser['id'] ?>">
        <div class="actions">
            <button class="danger" type="submit">Delete user</button>
            <a class="button button-secondary" href="<?= url('users') ?>">Cancel</a>
        </div>
    </form>
</div>
