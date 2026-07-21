<section class="hero">
    <span class="eyebrow">PURE PHP 8.1+</span>
    <h1>A small MVC core that leaves room to grow.</h1>
    <p>Routes stay readable, controllers stay focused, and your application code stays outside the public web root.</p>
    <div class="grid">
        <?php foreach ($features as $feature): ?>
            <article><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></article>
        <?php endforeach; ?>
    </div>
    <a class="button" href="<?= htmlspecialchars(url('hello/developer'), ENT_QUOTES, 'UTF-8') ?>">Try a dynamic route</a>
</section>
