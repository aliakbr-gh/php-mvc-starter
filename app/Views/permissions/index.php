<div>
    <div class="page-heading">
        <div>
            <h1>Permissions</h1>
            <p>Manage the individual capabilities available to roles.</p>
        </div>

        <?php if (hasPermission('sudo')): ?>
            <a class="button" href="<?= url('permissions/create') ?>">Create permission</a>
        <?php endif; ?>
    </div>

    <form class="filters" method="get" action="<?= url('permissions') ?>">
        <div class="filter-group filter-search">
            <label>
                Search
                <input
                    type="search"
                    name="search"
                    value="<?= e($pagination['query']['search']) ?>"
                    placeholder="Name or slug"
                >
            </label>
            <button type="submit">Search</button>
            <?php if ($pagination['query']['search'] !== ''): ?>
                <a class="button button-secondary" href="<?= url('permissions?per_page=' . $pagination['per_page']) ?>">Clear</a>
            <?php endif; ?>
        </div>

        <div class="filter-group filter-page-size">
            <label>
                Per page
                <select name="per_page" data-native-select>
                    <?php foreach ($allowedPerPage as $option): ?>
                        <option value="<?= $option ?>" <?= $pagination['per_page'] === $option ? 'selected' : '' ?>>
                            <?= $option ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="button-secondary" type="submit">Apply</button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pagination['rows'] === []): ?>
                    <tr>
                        <td class="table-empty" colspan="4">No permissions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['rows'] as $permission): ?>
                        <tr>
                            <td><?= (int) $permission['id'] ?></td>
                            <td><?= e($permission['name']) ?></td>
                            <td><?= e($permission['slug']) ?></td>
                            <td class="actions">
                                <?php if (hasPermission('sudo')): ?>
                                    <a href="<?= url('permissions/edit?id=' . (int) $permission['id']) ?>">Edit</a>
                                <?php endif; ?>

                                <?php if (hasPermission('sudo')): ?>
                                    <a href="<?= url('permissions/delete?id=' . (int) $permission['id']) ?>">Delete</a>
                                <?php else: ?>
                                    <span aria-label="No actions available">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php require dirname(__DIR__) . '/partials/pagination.php'; ?>
</div>
