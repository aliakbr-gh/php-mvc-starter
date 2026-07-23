<header class="mb-4">
    <h1 class="h3 mb-1">Edit user</h1>
    <p class="text-body-secondary mb-0">Update account details and role assignment.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('users/' . $record['id'] . '/update')) ?>">
    <?= csrf_field() ?>
    <div class="card-body vstack gap-3">
        <div>
            <label class="form-label" for="name">Name</label>
            <input class="form-control" id="name" name="name" value="<?= e($record['name']) ?>" required minlength="2">
        </div>
        <div>
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" value="<?= e($record['username']) ?>"
                required minlength="3" maxlength="50" pattern="[A-Za-z0-9._-]+">
        </div>
        <div>
            <label class="form-label" for="role">Role</label>
            <select class="form-select" id="role" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e($role['id']) ?>" <?= (int) $record['role_id'] === (int) $role['id'] ? 'selected' : '' ?>>
                        <?= e($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" for="password">Password</label>
            <div class="form-text mb-2">Leave blank to keep the current password</div>
            <input class="form-control" id="password" type="password" name="password" minlength="8">
        </div>
        <div class="d-flex gap-2 pt-2">
            <button class="btn btn-primary" type="submit">Update user</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('users')) ?>">Cancel</a>
        </div>
    </div>
</form>
