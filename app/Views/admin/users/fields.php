<div class="card-body vstack gap-3">
    <div>
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" value="<?= e($record['name'] ?? '') ?>" required minlength="2">
    </div>
    <div>
        <label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" type="email" name="email" value="<?= e($record['email'] ?? '') ?>" required>
    </div>
    <div>
        <label class="form-label" for="role">Role</label>
        <select class="form-select" id="role" name="role_id" required>
            <?php foreach ($roles as $role): ?>
                <option value="<?= e($role['id']) ?>" <?= (int) ($record['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>>
                    <?= e($role['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label" for="password">Password</label>
        <?php if (!$passwordRequired): ?>
            <div class="form-text mb-2">Leave blank to keep the current password</div>
        <?php endif; ?>
        <input class="form-control" id="password" type="password" name="password" <?= $passwordRequired ? 'required' : '' ?> minlength="8">
    </div>
    <div class="d-flex gap-2 pt-2">
        <button class="btn btn-primary" type="submit"><?= $passwordRequired ? 'Create user' : 'Update user' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('admin/users')) ?>">Cancel</a>
    </div>
</div>
