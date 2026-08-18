<div>
    <div class="page-heading">
        <div>
            <h1>Send Email</h1>
            <p>Send an email using the active email service.</p>
        </div>
        <?php if (hasPermission('email-settings.view')): ?>
            <div class="actions">
                <a class="button secondary" href="<?= url('email-settings') ?>">Email settings</a>
            </div>
        <?php endif; ?>
    </div>

    <section class="card narrow">
        <h2>Email message</h2>
        <p>Active service: <strong><?= e($activeService) ?></strong> (<?= $emailReady ? 'enabled' : 'disabled' ?>)</p>
        <form method="post" action="<?= url('send-email') ?>">
            <?= csrf_field() ?>
            <label>
                Receiver email
                <input type="email" name="recipient" maxlength="254" required>
            </label>
            <label>
                Subject
                <input type="text" name="subject" maxlength="200" required>
            </label>
            <label>
                Message
                <textarea name="message" rows="7" maxlength="10000" required></textarea>
            </label>
            <?php if (hasPermission('email.send')): ?>
                <div class="actions">
                    <button type="submit" <?= !$emailReady ? 'disabled' : '' ?>>Send email</button>
                </div>
            <?php endif; ?>
        </form>
    </section>
</div>
