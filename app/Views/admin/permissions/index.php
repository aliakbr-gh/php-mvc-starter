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
<div class="table-card table-responsive">
    <table class="table table-striped table-hover table-bordered align-middle mb-0 data-table">
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
                    <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                </td>
                <td><?= (int)$item['role_count'] ?></td>
                <td data-order="<?= (int)strtotime($item['created_at']) ?>"><?= htmlspecialchars(date('M j, Y', strtotime($item['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="actions-cell">
                    <?php if (\App\Core\Auth::can('permissions.update')): ?><a class="btn btn-sm btn-outline-primary"
                                                                               href="<?= htmlspecialchars(url('admin/permissions/' . $item['id'] . '/edit'), ENT_QUOTES, 'UTF-8') ?>">
                            Edit</a><?php endif; ?>
                    <?php if (\App\Core\Auth::can('permissions.delete')): ?>
                        <form method="post"
                              action="<?= htmlspecialchars(url('admin/permissions/' . $item['id'] . '/delete'), ENT_QUOTES, 'UTF-8') ?>"
                              onsubmit="return confirm('Delete this permission?')">
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
<?php require BASE_PATH . '/app/Views/partials/pagination.php'; ?>
