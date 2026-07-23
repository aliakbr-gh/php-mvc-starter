<section class="card shadow-sm auth-card">
  <div class="card-body p-4 p-md-5">
    <h1 class="h3 mb-2">Create account</h1>
    <p class="text-body-secondary mb-4">New registrations receive the standard user role.</p>
    <form class="vstack gap-3" method="post" action="<?= htmlspecialchars(url('register'), ENT_QUOTES, 'UTF-8') ?>">
        <?= csrf_field() ?>
        <div><label class="form-label" for="name">Name</label><input class="form-control" id="name" type="text" name="name" minlength="2" maxlength="100" autocomplete="name" required></div>
        <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email" name="email" maxlength="190" autocomplete="email" required></div>
        <div><label class="form-label" for="password">Password</label><input class="form-control" id="password" type="password" name="password" minlength="8" autocomplete="new-password" required></div>
        <button class="btn btn-primary" type="submit">Create account</button>
    </form>
    <p class="small text-body-secondary mt-4 mb-0">Already registered? <a href="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>">Log
            in</a></p>
  </div>
</section>
