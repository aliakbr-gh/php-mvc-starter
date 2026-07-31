<div class="card narrow">
    <h1>Delete permission</h1>
    <p>
        Delete <strong><?= e($permission['name']) ?></strong>
        (<?= e($permission['slug']) ?>)?
    </p>

    <form method="post" action="<?= url('permissions/destroy') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $permission['id'] ?>">

        <div class="actions">
            <button class="danger" type="submit">Delete permission</button>
            <a class="button button-secondary" href="<?= url('permissions') ?>">Cancel</a>
        </div>
    </form>
</div>
