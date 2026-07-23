<form class="table-filters" method="get" action="<?= htmlspecialchars(url($baseUrl), ENT_QUOTES, 'UTF-8') ?>">
    <input class="form-control" type="search" name="search"
           value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search…">
    <select class="form-select" name="per_page" onchange="this.form.submit()" aria-label="Items per page">
        <?php foreach ([10, 25, 50] as $size): ?>
            <option value="<?= $size ?>" <?= $perPage === $size ? 'selected' : '' ?>><?= $size ?> per
            page</option><?php endforeach; ?>
    </select>
    <button class="btn btn-secondary" type="submit">Search</button>
</form>
