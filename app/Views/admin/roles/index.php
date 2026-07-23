<?php
$heading = 'Roles';
$description = 'Group permissions into reusable access levels.';
$createPermission = 'roles.create';
$createUrl = 'admin/roles/create';
$singular = 'role';
require BASE_PATH . '/app/Views/partials/admin-header.php';
$baseUrl = 'admin/roles';
require BASE_PATH . '/app/Views/partials/table-filters.php';
?>
<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 data-table">
        <thead><tr><th>Role</th><th>Permissions</th><th>Users</th><th class="actions-cell">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($result['items'] as $item): ?>
            <tr>
                <td><strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?></small></td>
                <td><?= (int) $item['permission_count'] ?></td>
                <td><?= (int) $item['user_count'] ?></td>
                <td class="actions-cell">
                    <?php if (\App\Core\Auth::can('roles.update')): ?><a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(url('admin/roles/' . $item['id'] . '/edit'), ENT_QUOTES, 'UTF-8') ?>">Edit</a><?php endif; ?>
                    <?php if (\App\Core\Auth::can('roles.delete')): ?>
                        <form method="post" action="<?= htmlspecialchars(url('admin/roles/' . $item['id'] . '/delete'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this role?')">
                            <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
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
