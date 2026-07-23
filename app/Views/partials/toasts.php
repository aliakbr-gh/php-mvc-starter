<?php $toasts = $_SESSION['_flash'] ?? [];
unset($_SESSION['_flash']); ?>
<?php if ($toasts): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        <?php foreach ($toasts as $toast): ?>
            <?php
            $type = in_array($toast['type'], ['success', 'danger', 'warning', 'info'], true)
                ? $toast['type']
                : ($toast['type'] === 'error' ? 'danger' : 'secondary');
            ?>
            <div class="toast border-0 text-bg-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" role="alert"
                aria-live="assertive" aria-atomic="true" data-bs-delay="4500">
                <div class="d-flex">
                    <div class="toast-body"><?= htmlspecialchars($toast['message'], ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>