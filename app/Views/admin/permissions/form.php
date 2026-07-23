<header class="mb-4">
    <h1 class="h3 mb-1"><?= $record ? 'Edit permission' : 'Create permission' ?></h1>
    <p class="text-body-secondary mb-0">Use a module.action slug such as reports.view.</p>
</header>
<form class="card shadow-sm" method="post"
      action="<?= htmlspecialchars(url($record ? 'admin/permissions/' . $record['id'] . '/update' : 'admin/permissions'), ENT_QUOTES, 'UTF-8') ?>"><?= csrf_field() ?>
  <div class="card-body vstack gap-3">
    <div><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="<?= htmlspecialchars($record['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
    <div><label class="form-label" for="slug">Slug</label><input class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($record['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="reports.view" required pattern="[a-z0-9.-]+"></div>
    <div class="d-flex gap-2 pt-2">
        <button class="btn btn-primary" type="submit">Save permission</button>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('admin/permissions'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a></div>
  </div>
</form>
