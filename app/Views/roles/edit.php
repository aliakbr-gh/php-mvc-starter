<div class="card narrow">
    <h1>Edit role</h1>

    <form method="post" action="<?= url('roles/update') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $role['id'] ?>">

        <label>
            Name
            <input name="name" value="<?= e($role['name']) ?>" minlength="2" maxlength="100" required>
        </label>

        <label>
            Slug
            <input name="slug" value="<?= e($role['slug']) ?>" required>
        </label>

        <div class="actions">
            <button type="submit">Save changes</button>
            <a class="button button-secondary" href="<?= url('roles') ?>">Cancel</a>
        </div>
    </form>
</div>
