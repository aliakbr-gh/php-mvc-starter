<div class="card narrow">
    <h1>Edit permission</h1>

    <form method="post" action="<?= url('permissions/update') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $permission['id'] ?>">

        <label>
            Name
            <input name="name" value="<?= e($permission['name']) ?>" minlength="2" maxlength="120" required>
        </label>

        <label>
            Slug
            <input name="slug" value="<?= e($permission['slug']) ?>" required>
        </label>

        <div class="actions">
            <button type="submit">Save changes</button>
            <a class="button button-secondary" href="<?= url('permissions') ?>">Cancel</a>
        </div>
    </form>
</div>
