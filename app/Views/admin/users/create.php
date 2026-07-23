<header class="mb-4">
    <h1 class="h3 mb-1">Create user</h1>
    <p class="text-body-secondary mb-0">Add an account and assign its role.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('admin/users')) ?>">
    <?= csrf_field() ?>
    <div class="card-body vstack gap-3">
        <div>
            <label class="form-label" for="name">Name</label>
            <input class="form-control" id="name" name="name" required minlength="2">
        </div>
        <div>
            <label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" type="email" name="email" required>
        </div>
        <div>
            <label class="form-label" for="role">Role</label>
            <select class="form-select" id="role" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e($role['id']) ?>"><?= e($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" type="password" name="password" required minlength="8">
        </div>
        <div class="d-flex gap-2 pt-2">
            <button class="btn btn-primary" type="submit">Create user</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('admin/users')) ?>">Cancel</a>
        </div>
    </div>
</form>
