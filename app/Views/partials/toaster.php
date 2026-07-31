<div class="toast-container" id="toast-container" aria-label="Notifications" aria-live="polite">
    <?php if ($message = flash('success')): ?>
        <div class="toast toast--success" role="status" data-toast data-duration="1000">
            <span class="toast__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="m7 12.5 3 3 7-7" /></svg>
            </span>
            <span class="toast__message"><?= e($message) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($message = flash('error')): ?>
        <div class="toast toast--error" role="alert" data-toast data-duration="1000">
            <span class="toast__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 7v6m0 4h.01" /></svg>
            </span>
            <span class="toast__message"><?= e($message) ?></span>
        </div>
    <?php endif; ?>
</div>
