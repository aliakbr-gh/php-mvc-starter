<?php $toasts = $_SESSION['_flash'] ?? [];
unset($_SESSION['_flash']); ?>
<?php if ($toasts): ?>
    <div class="toast-stack" aria-live="polite">
        <?php foreach ($toasts as $toast): ?>
            <div class="toast toast-<?= htmlspecialchars($toast['type'], ENT_QUOTES, 'UTF-8') ?>">
                <span><?= htmlspecialchars($toast['message'], ENT_QUOTES, 'UTF-8') ?></span>
                <button type="button" aria-label="Close">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
