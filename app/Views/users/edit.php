<div class="card narrow">
    <h1>Edit user</h1>

    <form method="post" action="<?= url('users/update') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $editingUser['id'] ?>">

        <label>
            Name
            <input
                type="text"
                name="name"
                value="<?= e($editingUser['name']) ?>"
                minlength="2"
                maxlength="100"
                required
            >
        </label>

        <label>
            Username
            <input
                type="text"
                name="username"
                value="<?= e($editingUser['username']) ?>"
                minlength="3"
                maxlength="50"
                required
            >
        </label>

        <label>
            Role
            <select name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option
                        value="<?= (int) $role['id'] ?>"
                        <?= (int) $editingUser['role_id'] === (int) $role['id'] ? 'selected' : '' ?>
                    >
                        <?= e($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            New password
            <input
                type="password"
                name="password"
                minlength="5"
                placeholder="Leave blank to keep current"
            >
        </label>

        <label class="checkbox-option">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                <?= (bool) $editingUser['is_active'] ? 'checked' : '' ?>
            >
            <span>
                <strong>Active account</strong>
                <small>Inactive users are signed out and cannot sign in.</small>
            </span>
        </label>

        <div class="actions">
            <button type="submit">Save changes</button>
            <a class="button button-secondary" href="<?= url('users') ?>">Cancel</a>
        </div>
    </form>
</div>
