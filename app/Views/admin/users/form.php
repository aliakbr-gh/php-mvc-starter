<header class="mb-4">
    <h1 class="h3 mb-1"><?= $record ? 'Edit user' : 'Create user' ?></h1>
    <p class="text-body-secondary mb-0">Account details and role assignment.</p>
</header>
<form class="card shadow-sm" method="post"
      action="<?= htmlspecialchars(url($record ? 'admin/users/' . $record['id'] . '/update' : 'admin/users'), ENT_QUOTES, 'UTF-8') ?>"><?= csrf_field() ?>
  <div class="card-body vstack gap-3">
    <div><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="<?= htmlspecialchars($record['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required minlength="2"></div>
    <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email" name="email"
                       value="<?= htmlspecialchars($record['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
    <div><label class="form-label" for="role">Role</label><select class="form-select" id="role" name="role_id" required><?php foreach ($roles as $role): ?>
                <option
                value="<?= $role['id'] ?>" <?= (int)($record['role_id'] ?? 0) === (int)$role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select></div>
    <div><label class="form-label" for="password">Password</label><?php if ($record): ?><div class="form-text mb-2">Leave blank to keep the current password</div><?php endif; ?><input
                class="form-control" id="password" type="password" name="password" <?= $record ? '' : 'required' ?> minlength="8"></div>
    <div class="d-flex gap-2 pt-2">
        <button class="btn btn-primary" type="submit">Save user</button>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('admin/users'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a></div>
  </div>
</form>
