<section class="auth-card">
    <span class="eyebrow">GET STARTED</span>
    <h1>Create account</h1>
    <p>New registrations receive the standard user role.</p>
    <form method="post" action="<?= htmlspecialchars(url('register'), ENT_QUOTES, 'UTF-8') ?>">
        <?= csrf_field() ?>
        <label>Name<input type="text" name="name" minlength="2" maxlength="100" autocomplete="name" required></label>
        <label>Email<input type="email" name="email" maxlength="190" autocomplete="email" required></label>
        <label>Password<input type="password" name="password" minlength="8" autocomplete="new-password" required></label>
        <button class="button" type="submit">Create account</button>
    </form>
    <p class="form-foot">Already registered? <a href="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>">Log in</a></p>
</section>
