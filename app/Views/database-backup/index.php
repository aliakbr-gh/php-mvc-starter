<div>
    <div class="page-heading">
        <div>
            <h1>Database Backup</h1>
            <p>Download timestamped backups or save them directly to Google Drive.</p>
        </div>
    </div>

    <div class="backup-grid">
        <div class="card backup-card">
            <h2>Database SQL</h2>
            <p>Schema and table data in an importable SQL file.</p>
            <?php if (hasPermission('database-backup.download')): ?>
                <div class="actions">
                    <a class="button" href="<?= url('database-backup/database') ?>" download>Download .sql</a>
                    <?php if ($googleDrive['connected']): ?>
                        <form method="post" action="<?= url('database-backup/google/upload') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="backup_type" value="database">
                            <button type="submit" class="button-secondary">Save to Drive</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="button button-secondary" aria-disabled="true">Download unavailable</span>
            <?php endif; ?>
        </div>

        <div class="card backup-card">
            <h2>Uploaded files</h2>
            <p>The complete public uploads directory in a ZIP archive.</p>
            <?php if (hasPermission('database-backup.download')): ?>
                <div class="actions">
                    <a class="button" href="<?= url('database-backup/uploads') ?>" download>Download uploads</a>
                    <?php if ($googleDrive['connected']): ?>
                        <form method="post" action="<?= url('database-backup/google/upload') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="backup_type" value="uploads">
                            <button type="submit" class="button-secondary">Save to Drive</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="button button-secondary" aria-disabled="true">Download unavailable</span>
            <?php endif; ?>
        </div>

        <div class="card backup-card">
            <h2>Full backup</h2>
            <p>A ZIP archive containing database.sql and the uploads directory.</p>
            <?php if (hasPermission('database-backup.download')): ?>
                <div class="actions">
                    <a class="button" href="<?= url('database-backup/full') ?>" download>Download full backup</a>
                    <?php if ($googleDrive['connected']): ?>
                        <form method="post" action="<?= url('database-backup/google/upload') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="backup_type" value="full">
                            <button type="submit" class="button-secondary">Save to Drive</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="button button-secondary" aria-disabled="true">Download unavailable</span>
            <?php endif; ?>
        </div>
    </div>

    <section class="card drive-settings-card">
        <div class="drive-settings-heading">
            <div>
                <span class="drive-eyebrow">Cloud backup</span>
                <h2>Google Drive</h2>
                <p>Store generated backups in My Drive or a specific Drive folder.</p>
            </div>
            <span class="status-badge <?= $googleDrive['connected'] ? 'status-success' : 'status-warning' ?>">
                <?= $googleDrive['connected'] ? 'Connected' : 'Not connected' ?>
            </span>
        </div>

        <?php if (hasPermission('sudo')): ?>
            <div class="drive-settings-layout">
                <aside class="drive-setup-guide">
                    <h3>Before you connect</h3>
                    <ol>
                        <li>Open <a href="https://console.cloud.google.com/auth/clients" target="_blank" rel="noopener noreferrer">Google Auth Platform</a>.</li>
                        <li>Create a client with <strong>Web application</strong> as its type.</li>
                        <li>Add the redirect URI shown here to <strong>Authorized redirect URIs</strong>.</li>
                        <li>Copy the generated client ID and client secret into this form.</li>
                    </ol>
                    <p class="drive-guide-note">Also enable the Google Drive API for the same Cloud project.</p>
                </aside>

                <form class="drive-settings-form" method="post" action="<?= url('database-backup/google/settings') ?>">
                    <?= csrf_field() ?>
                    <label>
                        OAuth client ID
                        <input type="text" name="client_id" value="<?= e($googleDrive['client_id']) ?>" maxlength="255" required autocomplete="off">
                        <small>Found in Google Auth Platform → Clients after creating a Web application client.</small>
                    </label>
                    <label>
                        OAuth client secret
                        <input type="password" name="client_secret" maxlength="255" autocomplete="new-password" placeholder="Leave blank to keep saved secret">
                        <small><?= $googleDrive['client_secret_saved'] ? 'A client secret is securely saved. Enter a value only to replace it.' : 'Copy the secret shown when Google creates the OAuth client.' ?></small>
                    </label>
                    <label>
                        Drive folder ID (optional)
                        <input type="text" name="folder_id" value="<?= e($googleDrive['folder_id']) ?>" maxlength="255" autocomplete="off" placeholder="1AbC...XyZ">
                        <small>Open the folder in Drive and copy the part after <code>/folders/</code>. Leave blank for My Drive.</small>
                    </label>

                    <div class="drive-redirect-uri">
                        <span>Authorized redirect URI</span>
                        <code><?= e($googleDrive['redirect_uri']) ?></code>
                        <small>Copy it exactly—protocol, domain, path, and trailing slash must match.</small>
                    </div>

                    <div class="actions drive-settings-actions">
                        <button type="submit">Save settings</button>
                        <?php if ($googleDrive['configured'] && !$googleDrive['connected']): ?>
                            <a class="button button-secondary" href="<?= url('database-backup/google/connect') ?>">Connect Google Drive</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if ($googleDrive['connected']): ?>
                <form class="drive-disconnect" method="post" action="<?= url('database-backup/google/disconnect') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="button-secondary">Disconnect Google Drive</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <p class="drive-viewer-message">
                <?= $googleDrive['connected']
                    ? 'Google Drive is ready. Use Save to Drive on any backup above.'
                    : 'The Product Owner must configure and connect Google Drive before cloud backups are available.' ?>
            </p>
        <?php endif; ?>
    </section>

    <div class="card backup-note">
        <strong>Timestamped filenames</strong>
        <p>Every download includes the current date and time, for example:</p>
        <code><?= e(appFilenameSlug()) ?>-full-backup-<?= date('Y-m-d_H-i-s') ?>.zip</code>
    </div>
</div>
