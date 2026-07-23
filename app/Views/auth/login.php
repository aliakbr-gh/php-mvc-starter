<section class="card shadow-sm auth-card">
  <div class="card-body p-4 p-md-5">
    <h1 class="h3 mb-2">Log in</h1>
    <p class="text-body-secondary mb-4">Enter your account details to open the dashboard.</p>
    <form class="vstack gap-3" method="post" action="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>">
      <?= csrf_field() ?>
      <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email"
          name="email" autocomplete="email" required></div>
      <div><label class="form-label" for="password">Password</label><input class="form-control" id="password"
          type="password" name="password" autocomplete="current-password" required></div>
      <button class="btn btn-primary" type="submit">Log in</button>
    </form>
    <p class="small text-body-secondary mt-4 mb-0">No account? <a
        href="<?= htmlspecialchars(url('register'), ENT_QUOTES, 'UTF-8') ?>">Register</a></p>
  </div>
</section>