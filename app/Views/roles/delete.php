<div class="card narrow">
    <h1>Delete role</h1>
    <p>Delete <strong><?= e($role['name']) ?></strong>?</p>
    <p>A role assigned to users cannot be deleted.</p>

    <form method="post" action="<?= url('roles/destroy') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $role['id'] ?>">

        <div class="actions">
            <button class="danger" type="submit">Delete role</button>
            <a class="button button-secondary" href="<?= url('roles') ?>">Cancel</a>
        </div>
    </form>
</div>
