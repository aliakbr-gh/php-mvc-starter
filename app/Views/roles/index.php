<div>
    <div class="page-heading">
        <div>
            <h1>Roles</h1>
            <p>Manage access roles and their assigned permissions.</p>
        </div>

        <?php if (hasPermission('roles.create')): ?>
            <a class="button" href="<?= url('roles/create') ?>">Create role</a>
        <?php endif; ?>
    </div>

    <form class="filters" method="get" action="<?= url('roles') ?>">
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
                <a class="button button-secondary" href="<?= url('roles?per_page=' . $pagination['per_page']) ?>">Clear</a>
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
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pagination['rows'] === []): ?>
                    <tr>
                        <td class="table-empty" colspan="5">No roles found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['rows'] as $role): ?>
                        <?php
                        $isFixedRole = in_array(
                            $role['slug'],
                            ['product-owner', 'admin'],
                            true
                        );
                        $canUpdateRole = !$isFixedRole && hasPermission('roles.update');
                        $canDeleteRole = !$isFixedRole && hasPermission('roles.delete');
                        ?>
                        <tr>
                            <td><?= (int) $role['id'] ?></td>
                            <td><?= e($role['name']) ?></td>
                            <td><?= e($role['slug']) ?></td>
                            <td><?= (int) $role['permission_count'] ?></td>
                            <td class="actions">
                                <?php if ($canUpdateRole): ?>
                                    <a href="<?= url('roles/permissions?id=' . (int) $role['id']) ?>">Assign permissions</a>
                                    <a href="<?= url('roles/edit?id=' . (int) $role['id']) ?>">Edit</a>
                                <?php endif; ?>
                                <?php if ($canDeleteRole): ?>
                                    <a href="<?= url('roles/delete?id=' . (int) $role['id']) ?>">Delete</a>
                                <?php endif; ?>
                                <?php if (!$canUpdateRole && !$canDeleteRole): ?>
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
