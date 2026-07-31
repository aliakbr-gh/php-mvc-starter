<div class="card narrow">
    <h1>Create role</h1>

    <form method="post" action="<?= url('roles/store') ?>">
        <?= csrf_field() ?>

        <label>
            Name
            <input name="name" value="<?= old('name') ?>" minlength="2" maxlength="100" required>
        </label>

        <label>
            Slug
            <input name="slug" value="<?= old('slug') ?>" placeholder="content-manager" required>
        </label>

        <div class="actions">
            <button type="submit">Create role</button>
            <a class="button button-secondary" href="<?= url('roles') ?>">Cancel</a>
        </div>
    </form>
</div>
