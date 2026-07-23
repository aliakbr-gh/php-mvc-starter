<?php
$heading = 'Permissions';
$description = 'Define individual abilities that can be assigned to roles.';
$createPermission = 'permissions.create';
$createUrl = 'admin/permissions/create';
$singular = 'permission';
require BASE_PATH . '/app/Views/partials/admin-header.php';
$baseUrl = 'admin/permissions';
require BASE_PATH . '/app/Views/partials/table-filters.php';
?>
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th>Permission</th>
                    <th>Assigned roles</th>
                    <th>Created</th>
                    <th class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['items'] as $item): ?>
                    <tr>
                        <td>
                            <strong><?= e($item['name']) ?></strong><small><?= e($item['slug']) ?></small>
                        </td>
                        <td><?= (int) $item['role_count'] ?></td>
                        <td data-order="<?= (int) strtotime($item['created_at']) ?>">
                            <?= e(date('M j, Y', strtotime($item['created_at']))) ?>
                        </td>
                        <td class="actions-cell">
                            <?php if (\App\Core\Auth::can('permissions.update')): ?><a
                                    class="btn btn-sm btn-outline-primary"
                                    href="<?= e(url('admin/permissions/' . $item['id'] . '/edit')) ?>">
                                    Edit</a><?php endif; ?>
                            <?php if (\App\Core\Auth::can('permissions.delete')): ?>
                                <form method="post"
                                    action="<?= e(url('admin/permissions/' . $item['id'] . '/delete')) ?>"
                                    data-confirm="Delete this permission?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
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
