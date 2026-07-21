<section class="auth-card">
    <span class="eyebrow">WELCOME BACK</span>
    <h1>Log in</h1>
    <p>Enter your account details to open the dashboard.</p>
    <form method="post" action="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>">
        <?= csrf_field() ?>
        <label>Email<input type="email" name="email" autocomplete="email" required></label>
        <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="button" type="submit">Log in</button>
    </form>
    <p class="form-foot">No account? <a href="<?= htmlspecialchars(url('register'), ENT_QUOTES, 'UTF-8') ?>">Register</a></p>
</section>
