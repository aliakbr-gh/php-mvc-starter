<div class="card narrow">
    <h1>Create user</h1>

    <form method="post" action="<?= url('users/store') ?>">
        <?= csrf_field() ?>

        <label>
            Name
            <input type="text" name="name" value="<?= old('name') ?>" minlength="2" maxlength="100" required>
        </label>

        <label>
            Username
            <input type="text" name="username" value="<?= old('username') ?>" minlength="3" maxlength="50" required>
        </label>

        <label>
            Role
            <select name="role_id" required>
                <option value="">Select a role</option>
                <?php foreach ($roles as $role): ?>
                    <option
                        value="<?= (int) $role['id'] ?>"
                        <?= old('role_id') === (string) $role['id'] ? 'selected' : '' ?>
                    >
                        <?= e($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Password
            <input type="password" name="password" minlength="5" required>
        </label>

        <label>
            Confirm password
            <input type="password" name="confirm_password" minlength="5" required>
        </label>

        <label class="checkbox-option">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                <?= !isset($_SESSION['_old']) || old('is_active') === '1' ? 'checked' : '' ?>
            >
            <span>
                <strong>Active account</strong>
                <small>Allow this user to sign in and keep authenticated sessions.</small>
            </span>
        </label>

        <div class="actions">
            <button type="submit">Create user</button>
            <a class="button button-secondary" href="<?= url('users') ?>">Cancel</a>
        </div>
    </form>
</div>
