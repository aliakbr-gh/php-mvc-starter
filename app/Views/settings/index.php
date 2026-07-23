<header class="settings-page-header d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
    <div>
        <span class="settings-eyebrow">Workspace preferences</span>
        <h1 class="h2 mb-2">App settings</h1>
        <p class="text-body-secondary mb-0">
            Manage your application identity and choose how navigation appears.
        </p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('dashboard')) ?>">Back to dashboard</a>
</header>

<form method="post" action="<?= e(url('settings')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="settings-layout">
        <div class="settings-content vstack gap-4">
            <section class="card settings-card shadow-sm">
                <div class="card-body p-4">
                    <div class="settings-section-heading">
                        <span class="settings-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 20h16M6 16l3-8 3 8M7.3 13h3.4M15 8v8M15 8h2.5a2.5 2.5 0 0 1 0 5H15" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="h5 mb-1">Application identity</h2>
                            <p class="text-body-secondary small mb-0">
                                This name appears in page titles, menus, and browser tabs.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-semibold" for="app-name">App name</label>
                        <input class="form-control form-control-lg" id="app-name" name="app_name"
                            value="<?= e($appName) ?>" required minlength="2" maxlength="80">
                        <div class="form-text">Use between 2 and 80 characters.</div>
                    </div>
                </div>
            </section>

            <section class="card settings-card shadow-sm">
                <div class="card-body p-4">
                    <div class="settings-section-heading">
                        <span class="settings-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                <circle cx="8.5" cy="9" r="1.5" />
                                <path d="m5 17 4-4 3 3 2-2 5 3" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="h5 mb-1">Brand assets</h2>
                            <p class="text-body-secondary small mb-0">
                                Upload optimized images to keep your brand consistent.
                            </p>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-12 col-xl-6">
                            <div class="upload-panel h-100">
                                <div class="upload-preview upload-preview-logo">
                                    <?php if ($logoUrl): ?>
                                        <img src="<?= e($logoUrl) ?>" alt="Current application logo">
                                    <?php else: ?>
                                        <span>No logo</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label fw-semibold" for="logo">Application logo</label>
                                    <input class="form-control" id="logo" name="logo" type="file"
                                        accept="image/png,image/jpeg,image/webp">
                                    <p class="form-text mb-0">
                                        PNG, JPG, or WebP. Maximum 2 MB. Recommended: square image.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="upload-panel h-100">
                                <div class="upload-preview upload-preview-favicon">
                                    <?php if ($faviconUrl): ?>
                                        <img src="<?= e($faviconUrl) ?>" alt="Current favicon">
                                    <?php else: ?>
                                        <span>32</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label fw-semibold" for="favicon">Favicon</label>
                                    <input class="form-control" id="favicon" name="favicon" type="file"
                                        accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/webp">
                                    <p class="form-text mb-0">
                                        PNG, ICO, or WebP. Maximum 1 MB. Recommended: 32 × 32 px.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card settings-card shadow-sm">
                <div class="card-body p-4">
                    <div class="settings-section-heading">
                        <span class="settings-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                <path d="M8 4v16M11 8h7M11 12h7" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="h5 mb-1">Navigation layout</h2>
                            <p class="text-body-secondary small mb-0">
                                Select the layout that best fits the way you work.
                            </p>
                        </div>
                    </div>

                    <fieldset class="mt-4">
                        <legend class="visually-hidden">Navigation style</legend>
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <label class="navigation-choice card h-100">
                                    <input class="form-check-input navigation-choice-input" type="radio"
                                        name="navigation" value="sidebar"
                                        <?= $navigationStyle === 'sidebar' ? 'checked' : '' ?>>
                                    <span class="navigation-mockup navigation-mockup-sidebar" aria-hidden="true">
                                        <span class="navigation-mockup-side"></span>
                                        <span class="navigation-mockup-body">
                                            <i></i><i></i><i></i>
                                        </span>
                                    </span>
                                    <span class="card-body d-flex align-items-start gap-3">
                                        <span class="navigation-radio"></span>
                                        <span>
                                            <strong class="d-block mb-1">Sidebar navigation</strong>
                                            <span class="text-body-secondary small">
                                                Keep primary links in a focused menu on the left.
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="navigation-choice card h-100">
                                    <input class="form-check-input navigation-choice-input" type="radio"
                                        name="navigation" value="header"
                                        <?= $navigationStyle === 'header' ? 'checked' : '' ?>>
                                    <span class="navigation-mockup navigation-mockup-header" aria-hidden="true">
                                        <span class="navigation-mockup-top"></span>
                                        <span class="navigation-mockup-body">
                                            <i></i><i></i><i></i>
                                        </span>
                                    </span>
                                    <span class="card-body d-flex align-items-start gap-3">
                                        <span class="navigation-radio"></span>
                                        <span>
                                            <strong class="d-block mb-1">Header navigation</strong>
                                            <span class="text-body-secondary small">
                                                Place primary links in a compact menu across the top.
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </section>
        </div>

        <aside class="settings-summary">
            <div class="card shadow-sm sticky-lg-top">
                <div class="card-body p-4">
                    <span class="settings-eyebrow">Live identity</span>
                    <div class="settings-brand-preview mt-3">
                        <div class="settings-brand-logo">
                            <?php if ($logoUrl): ?>
                                <img src="<?= e($logoUrl) ?>" alt="">
                            <?php else: ?>
                                <?= e(strtoupper(substr($appName, 0, 1))) ?>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                            <strong class="d-block text-truncate"><?= e($appName) ?></strong>
                            <span class="small text-body-secondary">Current application brand</span>
                        </div>
                    </div>
                    <hr>
                    <p class="small text-body-secondary mb-0">
                        New images are applied after you save. Existing assets remain unchanged when no file is selected.
                    </p>
                </div>
                <div class="card-footer bg-body p-3">
                    <button class="btn btn-primary w-100" type="submit">Save app settings</button>
                </div>
            </div>
        </aside>
    </div>
</form>
