<div>
    <div class="page-heading">
        <div>
            <h1>Database Backup</h1>
            <p>Download timestamped backups of the database and uploaded files.</p>
        </div>
    </div>

    <div class="backup-grid">
        <div class="card backup-card">
            <h2>Database SQL</h2>
            <p>Schema and table data in an importable SQL file.</p>
            <?php if (hasPermission('database-backup.download')): ?>
                <a class="button" href="<?= url('database-backup/database') ?>" download>Download .sql</a>
            <?php else: ?>
                <span class="button button-secondary" aria-disabled="true">Download unavailable</span>
            <?php endif; ?>
        </div>

        <div class="card backup-card">
            <h2>Uploaded files</h2>
            <p>The complete public uploads directory in a ZIP archive.</p>
            <?php if (hasPermission('database-backup.download')): ?>
                <a class="button" href="<?= url('database-backup/uploads') ?>" download>Download uploads</a>
            <?php else: ?>
                <span class="button button-secondary" aria-disabled="true">Download unavailable</span>
            <?php endif; ?>
        </div>

        <div class="card backup-card">
            <h2>Full backup</h2>
            <p>A ZIP archive containing database.sql and the uploads directory.</p>
            <?php if (hasPermission('database-backup.download')): ?>
                <a class="button" href="<?= url('database-backup/full') ?>" download>Download full backup</a>
            <?php else: ?>
                <span class="button button-secondary" aria-disabled="true">Download unavailable</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card backup-note">
        <strong>Timestamped filenames</strong>
        <p>Every download includes the current date and time, for example:</p>
        <code><?= e(appFilenameSlug()) ?>-full-backup-<?= date('Y-m-d_H-i-s') ?>.zip</code>
    </div>
</div>
