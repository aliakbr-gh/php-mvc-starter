<section class="py-5">
    <span class="badge text-bg-primary mb-3">DYNAMIC ROUTE</span>
    <h1 class="display-4 fw-semibold">Hello, <?= e($name) ?>.</h1>
    <p class="lead text-body-secondary">This page was matched by <code>/hello/{name}</code> without a <code>.php</code>
        extension.</p>
    <a class="btn btn-primary" href="<?= e(url()) ?>">Go home</a>
</section>
