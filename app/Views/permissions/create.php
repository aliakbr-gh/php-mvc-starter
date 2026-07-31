<div class="card narrow">
    <h1>Create permission</h1>

    <form method="post" action="<?= url('permissions/store') ?>">
        <?= csrf_field() ?>

        <label>
            Name
            <input name="name" value="<?= old('name') ?>" minlength="2" maxlength="120" required>
        </label>

        <label>
            Slug
            <input name="slug" value="<?= old('slug') ?>" placeholder="projects.create" required>
        </label>

        <div class="actions">
            <button type="submit">Create permission</button>
            <a class="button button-secondary" href="<?= url('permissions') ?>">Cancel</a>
        </div>
    </form>
</div>
