<header class="module-head">
    <div><span class="eyebrow">ROLES</span>
        <h1><?= $record ? 'Edit role' : 'Create role' ?></h1>
        <p>Choose the exact permissions granted to this role.</p></div>
</header>
<form class="admin-form wide-form" method="post"
      action="<?= htmlspecialchars(url($record ? 'admin/roles/' . $record['id'] . '/update' : 'admin/roles'), ENT_QUOTES, 'UTF-8') ?>"><?= csrf_field() ?>
    <div class="form-grid"><label>Name<input name="name"
                                             value="<?= htmlspecialchars($record['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                             required></label><label>Slug<input name="slug"
                                                                                value="<?= htmlspecialchars($record['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                                placeholder="content-manager" required
                                                                                pattern="[a-z0-9.-]+"></label></div>
    <fieldset>
        <legend>Permissions</legend>
        <div class="permission-grid"><?php foreach ($permissions as $permission): ?><label class="check-card"><input
                        type="checkbox" name="permissions[]"
                        value="<?= $permission['id'] ?>" <?= in_array((int)$permission['id'], $selected, true) ? 'checked' : '' ?>><span><strong><?= htmlspecialchars($permission['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($permission['slug'], ENT_QUOTES, 'UTF-8') ?></small></span>
                </label><?php endforeach; ?></div>
    </fieldset>
    <div class="form-actions">
        <button class="button" type="submit">Save role</button>
        <a href="<?= htmlspecialchars(url('admin/roles'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a></div>
</form>
