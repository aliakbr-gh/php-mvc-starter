<header class="mb-4">
  <h1 class="h3 mb-1"><?= $record ? 'Edit role' : 'Create role' ?></h1>
  <p class="text-body-secondary mb-0">Choose the exact permissions granted to this role.</p>
</header>
<form class="card shadow-sm" method="post"
  action="<?= htmlspecialchars(url($record ? 'admin/roles/' . $record['id'] . '/update' : 'admin/roles'), ENT_QUOTES, 'UTF-8') ?>">
  <?= csrf_field() ?>
  <div class="card-body vstack gap-4">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label" for="name">Name</label><input class="form-control" id="name"
          name="name" value="<?= htmlspecialchars($record['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
      <div class="col-md-6"><label class="form-label" for="slug">Slug</label><input class="form-control" id="slug"
          name="slug" value="<?= htmlspecialchars($record['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          placeholder="content-manager" required pattern="[a-z0-9.-]+"></div>
    </div>
    <fieldset>
      <legend class="h6">Permissions</legend>
      <div class="permission-grid"><?php foreach ($permissions as $permission): ?>
          <label class="card bg-body-tertiary">
            <span class="card-body d-flex gap-2 py-3">
              <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $permission['id'] ?>"
                <?= in_array((int) $permission['id'], $selected, true) ? 'checked' : '' ?>>
              <span><strong
                  class="d-block"><?= htmlspecialchars($permission['name'], ENT_QUOTES, 'UTF-8') ?></strong><small
                  class="text-body-secondary"><?= htmlspecialchars($permission['slug'], ENT_QUOTES, 'UTF-8') ?></small></span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">Save role</button>
      <a class="btn btn-outline-secondary"
        href="<?= htmlspecialchars(url('admin/roles'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
    </div>
  </div>
</form>