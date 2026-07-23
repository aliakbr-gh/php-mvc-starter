<header class="mb-4">
    <h1 class="h3 mb-1">Create permission</h1>
    <p class="text-body-secondary mb-0">Use a module.action slug such as reports.view.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/permissions')) ?>">
    <?= csrf_field() ?>
    <div class="card-body vstack gap-3">
        <div>
            <label class="form-label" for="name">Name</label>
            <input class="form-control" id="name" name="name" required>
        </div>
        <div>
            <label class="form-label" for="slug">Slug</label>
            <input class="form-control" id="slug" name="slug" placeholder="reports.view" required pattern="[a-z0-9.-]+">
        </div>
        <div class="d-flex gap-2 pt-2">
            <button class="btn btn-primary" type="submit">Create permission</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('admin/permissions')) ?>">Cancel</a>
        </div>
    </div>
</form>
