<form class="row g-2 align-items-center justify-content-between mb-3" method="get"
    action="<?= e(url($baseUrl)) ?>">
    <div class="col-12 col-md-auto">
        <div class="input-group">
            <input class="form-control" type="search" name="search"
                value="<?= e($search) ?>" placeholder="Search records…"
                aria-label="Search records">
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a class="btn btn-outline-secondary"
                    href="<?= e(url($baseUrl) . '?' . http_build_query(['per_page' => $perPage])) ?>">Clear</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-md-auto ms-md-auto">
        <div class="input-group">
            <label class="input-group-text" for="per-page">Show</label>
            <select class="form-select" id="per-page" name="per_page" onchange="this.form.submit()">
                <?php foreach ([5, 10, 25, 50] as $size): ?>
                    <option value="<?= $size ?>" <?= $perPage === $size ? 'selected' : '' ?>><?= $size ?></option>
                <?php endforeach; ?>
            </select>
            <span class="input-group-text">per page</span>
        </div>
    </div>
</form>