<div>
    <div class="page-heading">
        <div>
            <h1>Application Settings</h1>
            <p>Update the application name, logo, and browser favicon.</p>
        </div>
    </div>

    <div class="card narrow">
        <form method="post" action="<?= url('app-settings') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <label>
                App name
                <input
                    type="text"
                    name="app_name"
                    value="<?= e($settings['app_name']) ?>"
                    minlength="2"
                    maxlength="100"
                    required
                >
            </label>

            <label>
                Logo
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp">
                <small>JPG, PNG, or WebP. Maximum 2 MB.</small>
            </label>

            <?php if ($settings['logo'] !== ''): ?>
                <div class="file-preview">
                    <img src="<?= url($settings['logo']) ?>" alt="Current application logo">
                    <span>Current logo</span>
                </div>
            <?php endif; ?>

            <label>
                Favicon
                <input type="file" name="favicon" accept=".ico,.png">
                <small>ICO or PNG. Maximum 512 KB.</small>
            </label>

            <?php if ($settings['favicon'] !== ''): ?>
                <div class="file-preview file-preview-small">
                    <img src="<?= url($settings['favicon']) ?>" alt="Current favicon">
                    <span>Current favicon</span>
                </div>
            <?php endif; ?>

            <?php if (hasPermission('settings.update')): ?>
                <div class="actions">
                    <button type="submit">Save settings</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
