<section class="hero">
    <span class="eyebrow">DYNAMIC ROUTE</span>
    <h1>Hello, <?= $name ?>.</h1>
    <p>This page was matched by <code>/hello/{name}</code> without a <code>.php</code> extension.</p>
    <a class="button" href="<?= htmlspecialchars(url(), ENT_QUOTES, 'UTF-8') ?>">Go home</a>
</section>
