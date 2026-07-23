<header class="mb-4">
    <h1 class="h3 mb-1">Edit role</h1>
    <p class="text-body-secondary mb-0">Update this role and its granted permissions.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/roles/' . $record['id'] . '/update')) ?>">
    <?= csrf_field() ?>
    <div class="card-body vstack gap-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Name</label>
                <input class="form-control" id="name" name="name" value="<?= e($record['name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="slug">Slug</label>
                <input class="form-control" id="slug" name="slug" value="<?= e($record['slug']) ?>" placeholder="content-manager" required pattern="[a-z0-9.-]+">
            </div>
        </div>
        <fieldset>
            <legend class="h6">Permissions</legend>
            <div class="permission-grid">
                <?php foreach ($permissions as $permission): ?>
                    <label class="card bg-body-tertiary">
                        <span class="card-body d-flex gap-2 py-3">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= e($permission['id']) ?>" <?= in_array((int) $permission['id'], $selected, true) ? 'checked' : '' ?>>
                            <span>
                                <strong class="d-block"><?= e($permission['name']) ?></strong>
                                <small class="text-body-secondary"><?= e($permission['slug']) ?></small>
                            </span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Update role</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('admin/roles')) ?>">Cancel</a>
        </div>
    </div>
</form>
