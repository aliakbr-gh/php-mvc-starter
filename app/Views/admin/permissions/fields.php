<div class="card-body vstack gap-3">
    <div>
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" value="<?= e($record['name'] ?? '') ?>" required>
    </div>
    <div>
        <label class="form-label" for="slug">Slug</label>
        <input class="form-control" id="slug" name="slug" value="<?= e($record['slug'] ?? '') ?>" placeholder="reports.view" required pattern="[a-z0-9.-]+">
    </div>
    <div class="d-flex gap-2 pt-2">
        <button class="btn btn-primary" type="submit"><?= $record ? 'Update permission' : 'Create permission' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('admin/permissions')) ?>">Cancel</a>
    </div>
</div>
