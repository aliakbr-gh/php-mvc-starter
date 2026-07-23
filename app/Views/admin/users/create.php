<header class="mb-4">
    <h1 class="h3 mb-1">Create user</h1>
    <p class="text-body-secondary mb-0">Add an account and assign its role.</p>
</header>
<form class="card shadow-sm" method="post" action="<?= e(url('users')) ?>">
    <?= csrf_field() ?>
    <div class="card-body vstack gap-3">
        <div>
            <label class="form-label" for="name">Name</label>
            <input class="form-control" id="name" name="name" required minlength="2">
        </div>
        <div>
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" required minlength="3"
                maxlength="50" pattern="[A-Za-z0-9._-]+">
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
            <a class="btn btn-outline-secondary" href="<?= e(url('users')) ?>">Cancel</a>
        </div>
    </div>
</form>
