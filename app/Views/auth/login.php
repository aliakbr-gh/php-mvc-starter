<div class="card narrow">
    <div class="login-brand">
        <?php if (appSettings()['logo'] !== ''): ?>
            <img src="<?= url(appSettings()['logo']) ?>" width="140" alt="<?= e(appSettings()['app_name']) ?>">
        <?php else: ?>
            <span><?= e(strtoupper(substr(appSettings()['app_name'], 0, 2))) ?></span>
        <?php endif; ?>
    </div>

    <h1><?= appSettings()['app_name'] ?> Admin Portal</h1>
    <h1>Login</h1>

    <form method="post" action="<?= url('login') ?>">
        <?= csrf_field() ?>

        <label>
            Username
            <input type="text" name="username" value="<?= old('username') ?>" required autofocus>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="center">Log in</button>
    </form>
</div>
