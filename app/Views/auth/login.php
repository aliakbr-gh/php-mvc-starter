<section class="card shadow-sm auth-card">
  <div class="card-body p-4 p-md-5">
    <h1 class="h3 mb-2">Log in</h1>
    <p class="text-body-secondary mb-4">Enter your account details to open the dashboard.</p>
    <form class="vstack gap-3" method="post" action="<?= e(url('login')) ?>">
      <?= csrf_field() ?>
      <div><label class="form-label" for="username">Username</label><input class="form-control" id="username"
          name="username" autocomplete="username" required></div>
      <div><label class="form-label" for="password">Password</label><input class="form-control" id="password"
          type="password" name="password" autocomplete="current-password" required></div>
      <button class="btn btn-primary" type="submit">Log in</button>
    </form>
  </div>
</section>
