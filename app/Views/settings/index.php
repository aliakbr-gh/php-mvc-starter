<header class="mb-4">
    <h1 class="h3 mb-1">App settings</h1>
    <p class="text-body-secondary mb-0">Choose how you want to navigate the application.</p>
</header>

<form class="card shadow-sm" method="post" action="<?= e(url('settings')) ?>">
    <?= csrf_field() ?>
    <div class="card-body">
        <fieldset>
            <legend class="h5 mb-3">Navigation style</legend>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="navigation-choice card h-100">
                        <span class="card-body d-flex gap-3">
                            <input class="form-check-input mt-1" type="radio" name="navigation"
                                value="sidebar" <?= $navigationStyle === 'sidebar' ? 'checked' : '' ?>>
                            <span>
                                <strong class="d-block mb-1">Sidebar</strong>
                                <span class="text-body-secondary small">
                                    Display navigation in a collapsible menu on the left.
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
                <div class="col-12 col-md-6">
                    <label class="navigation-choice card h-100">
                        <span class="card-body d-flex gap-3">
                            <input class="form-check-input mt-1" type="radio" name="navigation"
                                value="header" <?= $navigationStyle === 'header' ? 'checked' : '' ?>>
                            <span>
                                <strong class="d-block mb-1">Header</strong>
                                <span class="text-body-secondary small">
                                    Display navigation across the top of every page.
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="card-footer bg-body d-flex gap-2">
        <button class="btn btn-primary" type="submit">Save settings</button>
        <a class="btn btn-outline-secondary" href="<?= e(url('dashboard')) ?>">Cancel</a>
    </div>
</form>
