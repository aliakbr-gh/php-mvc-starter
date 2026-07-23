<section class="py-4 py-lg-5">
    <span class="badge text-bg-primary mb-3">PURE PHP 8.1+</span>
    <h1 class="display-4 fw-semibold">A small MVC core that leaves room to grow.</h1>
    <p class="lead text-body-secondary">Routes stay readable, controllers stay focused, and your application code stays
        outside the public web root.</p>
    <div class="row g-3 my-4">
        <?php foreach ($features as $feature): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="card h-100">
                    <div class="card-body"><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
    <a class="btn btn-primary" href="<?= htmlspecialchars(url('hello/developer'), ENT_QUOTES, 'UTF-8') ?>">Try a dynamic
        route</a>
</section>