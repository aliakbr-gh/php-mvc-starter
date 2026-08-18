<div>
    <div class="page-heading">
        <div>
            <h1>Email Settings</h1>
            <p>Configure Gmail and generic SMTP independently, then choose the active service.</p>
        </div>
        <?php if (hasPermission('email.view')): ?>
            <div class="actions">
                <a class="button secondary" href="<?= url('send-email') ?>">Send email</a>
            </div>
        <?php endif; ?>
    </div>

    <form method="post" action="<?= url('email-settings') ?>">
        <?= csrf_field() ?>

        <div class="card communication-service-picker">
            <label>
                Active email service
                <select name="active_transport" required>
                    <option value="gmail" <?= $settings['active_transport'] === 'gmail' ? 'selected' : '' ?>>Gmail</option>
                    <option value="smtp" <?= $settings['active_transport'] === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                </select>
                <small>Sending always uses this profile. It must also be enabled.</small>
            </label>
        </div>

        <div class="settings-grid communication-settings-grid">
            <section class="card">
                <div class="communication-card-heading">
                    <div>
                        <h2>Gmail</h2>
                        <p>Uses Gmail SMTP over an encrypted connection.</p>
                    </div>
                    <label class="checkbox-option compact-checkbox">
                        <input type="checkbox" name="gmail_enabled" value="1" <?= !empty($settings['gmail']['enabled']) ? 'checked' : '' ?>>
                        <span><strong>Enabled</strong></span>
                    </label>
                </div>

                <label>
                    Gmail address
                    <input type="email" name="gmail_email" value="<?= e($settings['gmail']['email']) ?>" maxlength="254" autocomplete="username">
                </label>
                <label>
                    Gmail app password
                    <input type="password" name="gmail_app_password" maxlength="100" autocomplete="new-password" placeholder="Leave blank to keep saved password">
                    <small><?= $settings['gmail']['app_password'] !== '' ? 'An app password is saved.' : 'Create an app password in your Google account; do not use your normal password.' ?></small>
                </label>
                <label>
                    Sender name
                    <input type="text" name="gmail_from_name" value="<?= e($settings['gmail']['from_name']) ?>" maxlength="100">
                </label>
            </section>

            <section class="card">
                <div class="communication-card-heading">
                    <div>
                        <h2>SMTP</h2>
                        <p>Works with any SMTP provider that supports login authentication.</p>
                    </div>
                    <label class="checkbox-option compact-checkbox">
                        <input type="checkbox" name="smtp_enabled" value="1" <?= !empty($settings['smtp']['enabled']) ? 'checked' : '' ?>>
                        <span><strong>Enabled</strong></span>
                    </label>
                </div>

                <div class="communication-inline-fields">
                    <label>
                        Host
                        <input type="text" name="smtp_host" value="<?= e($settings['smtp']['host']) ?>" maxlength="255" placeholder="smtp.example.com">
                    </label>
                    <label>
                        Port
                        <input type="number" name="smtp_port" value="<?= e($settings['smtp']['port']) ?>" min="1" max="65535" required>
                    </label>
                </div>
                <label>
                    Encryption
                    <select name="smtp_encryption" required>
                        <option value="tls" <?= $settings['smtp']['encryption'] === 'tls' ? 'selected' : '' ?>>STARTTLS</option>
                        <option value="ssl" <?= $settings['smtp']['encryption'] === 'ssl' ? 'selected' : '' ?>>SSL/TLS</option>
                        <option value="none" <?= $settings['smtp']['encryption'] === 'none' ? 'selected' : '' ?>>None</option>
                    </select>
                </label>
                <label>
                    Username
                    <input type="text" name="smtp_username" value="<?= e($settings['smtp']['username']) ?>" maxlength="255" autocomplete="username">
                </label>
                <label>
                    Password
                    <input type="password" name="smtp_password" maxlength="255" autocomplete="new-password" placeholder="Leave blank to keep saved password">
                    <small><?= $settings['smtp']['password'] !== '' ? 'A password is saved.' : 'No SMTP password is saved.' ?></small>
                </label>
                <label>
                    Sender email
                    <input type="email" name="smtp_from_email" value="<?= e($settings['smtp']['from_email']) ?>" maxlength="254">
                </label>
                <label>
                    Sender name
                    <input type="text" name="smtp_from_name" value="<?= e($settings['smtp']['from_name']) ?>" maxlength="100">
                </label>
            </section>
        </div>

        <?php if (hasPermission('email-settings.update')): ?>
            <div class="actions communication-save-actions">
                <button type="submit">Save email settings</button>
            </div>
        <?php endif; ?>
    </form>
</div>
