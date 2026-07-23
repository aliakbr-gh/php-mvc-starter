<?php
$heading = 'Users';
$description = 'Manage accounts and assign each user to a role.';
$createPermission = 'users.create';
$createUrl = 'users/create';
$singular = 'user';
require BASE_PATH . '/app/Views/partials/admin-header.php';
$baseUrl = 'users';
require BASE_PATH . '/app/Views/partials/table-filters.php';
?>
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Created at</th>
                    <th>Updated at</th>
                    <th class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['items'] as $item): ?>
                    <tr>
                        <td><strong><?= e($item['name']) ?></strong><small><?= e($item['username']) ?></small>
                        </td>
                        <td><span
                                class="badge text-bg-light border"><?= e($item['role_name']) ?></span>
                        </td>
                        <td data-order="<?= (int) strtotime($item['created_at']) ?>">
                            <?= e(format_timestamp($item['created_at'])) ?>
                        </td>
                        <td data-order="<?= (int) strtotime($item['updated_at']) ?>">
                            <?= e(format_timestamp($item['updated_at'])) ?>
                        </td>
                        <td class="actions-cell">
                            <?php if (\App\Core\Auth::can('users.update')): ?><a class="btn btn-sm btn-outline-primary"
                                    href="<?= e(url('users/' . $item['id'] . '/edit')) ?>">Edit</a><?php endif; ?>
                            <?php if (\App\Core\Auth::can('users.delete')): ?>
                                <form method="post"
                                    action="<?= e(url('users/' . $item['id'] . '/delete')) ?>"
                                    data-confirm="Delete this user?">
                                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"
                                        type="submit">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require BASE_PATH . '/app/Views/partials/pagination.php'; ?>
