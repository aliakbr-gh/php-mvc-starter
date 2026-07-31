<div>
    <div class="page-heading">
        <div>
            <h1>Users</h1>
            <p>Manage user accounts, roles, and access.</p>
        </div>
        <?php if (hasPermission('users.create')): ?>
            <a class="button" href="<?= url('users/create') ?>">Create user</a>
        <?php endif; ?>
    </div>

    <form class="filters" method="get" action="<?= url('users') ?>">
        <div class="filter-group filter-search">
            <label>
                Search
                <input type="search" name="search" value="<?= e($pagination['query']['search']) ?>" placeholder="Name or username">
            </label>
            <button type="submit">Search</button>
            <?php if ($pagination['query']['search'] !== ''): ?>
                <a class="button button-secondary" href="<?= url('users?per_page=' . $pagination['per_page']) ?>">Clear</a>
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
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pagination['rows'] === []): ?>
                    <tr>
                        <td class="table-empty" colspan="8">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['rows'] as $account): ?>
                        <?php
                        $isFixedAccount = in_array(
                            $account['role_slug'],
                            ['product-owner', 'admin'],
                            true
                        );
                        $isCurrentAccount = (int) $account['id'] === (int) user()['id'];
                        $canEditAccount = !$isFixedAccount && hasPermission('users.update');
                        $canDeleteAccount = !$isFixedAccount && !$isCurrentAccount && hasPermission('users.delete');
                        ?>
                        <tr>
                            <td><?= (int) $account['id'] ?></td>
                            <td><?= e($account['name']) ?></td>
                            <td><?= e($account['username']) ?></td>
                            <td><?= e($account['role_name']) ?></td>
                            <td>
                                <span class="status-badge <?= $account['is_active'] ? 'status-success' : 'status-danger' ?>">
                                    <?= $account['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= e($account['created_at']) ?></td>
                            <td><?= e($account['updated_at']) ?></td>
                            <td class="actions">
                                <?php if ($canEditAccount): ?>
                                    <a href="<?= url('users/edit?id=' . (int) $account['id']) ?>">Edit</a>
                                <?php endif; ?>
                                <?php if ($canDeleteAccount): ?>
                                    <a href="<?= url('users/delete?id=' . (int) $account['id']) ?>">Delete</a>
                                <?php elseif (!$isFixedAccount && $isCurrentAccount): ?>
                                    <span>Current user</span>
                                <?php elseif (!$canEditAccount): ?>
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
