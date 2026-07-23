<header class="module-head">
    <div><span class="eyebrow">PERMISSIONS</span>
        <h1><?= $record ? 'Edit permission' : 'Create permission' ?></h1>
        <p>Use a module.action slug such as reports.view.</p></div>
</header>
<form class="admin-form" method="post"
      action="<?= htmlspecialchars(url($record ? 'admin/permissions/' . $record['id'] . '/update' : 'admin/permissions'), ENT_QUOTES, 'UTF-8') ?>"><?= csrf_field() ?>
    <label>Name<input name="name" value="<?= htmlspecialchars($record['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label><label>Slug<input
                name="slug" value="<?= htmlspecialchars($record['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="reports.view" required pattern="[a-z0-9.-]+"></label>
    <div class="form-actions">
        <button class="button" type="submit">Save permission</button>
        <a href="<?= htmlspecialchars(url('admin/permissions'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a></div>
</form>
