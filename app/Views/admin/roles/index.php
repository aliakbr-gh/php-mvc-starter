<?php
$heading = 'Roles';
$description = 'Group permissions into reusable access levels.';
$createPermission = 'roles.create';
$createUrl = 'roles/create';
$singular = 'role';
require BASE_PATH . '/app/Views/partials/admin-header.php';
$baseUrl = 'roles';
require BASE_PATH . '/app/Views/partials/table-filters.php';
?>
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th class="text-end">Permissions</th>
                    <th class="text-end">Users</th>
                    <th>Created at</th>
                    <th>Updated at</th>
                    <th class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['items'] as $item): ?>
                    <tr>
                        <td><strong><?= e($item['name']) ?></strong><small><?= e($item['slug']) ?></small>
                        </td>
                        <td class="text-end"><?= (int) $item['permission_count'] ?></td>
                        <td class="text-end"><?= (int) $item['user_count'] ?></td>
                        <td data-order="<?= (int) strtotime($item['created_at']) ?>">
                            <?= e(format_timestamp($item['created_at'])) ?>
                        </td>
                        <td data-order="<?= (int) strtotime($item['updated_at']) ?>">
                            <?= e(format_timestamp($item['updated_at'])) ?>
                        </td>
                        <td class="actions-cell">
                            <?php if (\App\Core\Auth::can('roles.update')): ?><a class="btn btn-sm btn-outline-primary"
                                    href="<?= e(url('roles/' . $item['id'] . '/edit')) ?>">Edit</a><?php endif; ?>
                            <?php if (\App\Core\Auth::can('roles.delete')): ?>
                                <form method="post"
                                    action="<?= e(url('roles/' . $item['id'] . '/delete')) ?>"
                                    data-confirm="Delete this role?">
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
