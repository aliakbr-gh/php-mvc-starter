<div class="card">
    <div class="page-heading">
        <div>
            <h1>Assign permissions</h1>
            <p>Role: <?= e($role['name']) ?></p>
        </div>
        <a class="button button-secondary" href="<?= url('roles') ?>">Back to roles</a>
    </div>

    <form method="post" action="<?= url('roles/permissions') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $role['id'] ?>">

        <div class="checkbox-grid">
            <?php foreach ($permissions as $permission): ?>
                <label class="checkbox-option">
                    <input
                        type="checkbox"
                        name="permissions[]"
                        value="<?= (int) $permission['id'] ?>"
                        <?= in_array((int) $permission['id'], $assignedIds, true) ? 'checked' : '' ?>
                    >
                    <span>
                        <strong><?= e($permission['name']) ?></strong>
                        <small><?= e($permission['slug']) ?></small>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="actions">
            <button type="submit">Save permissions</button>
            <a class="button button-secondary" href="<?= url('roles') ?>">Cancel</a>
        </div>
    </form>
</div>
