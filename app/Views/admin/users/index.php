<?php $heading='Users';$description='Manage accounts and assign each user to a role.';$createPermission='users.create';$createUrl='admin/users/create';$singular='user';require BASE_PATH.'/app/Views/partials/admin-header.php'; ?>
<?php $baseUrl='admin/users';require BASE_PATH.'/app/Views/partials/table-filters.php'; ?>
<div class="table-card"><table><thead><tr><th>User</th><th>Role</th><th>Created</th><th class="actions-cell">Actions</th></tr></thead><tbody>
<?php foreach($result['items'] as $item): ?><tr><td><strong><?= htmlspecialchars($item['name'],ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars($item['email'],ENT_QUOTES,'UTF-8') ?></small></td><td><span class="table-badge"><?= htmlspecialchars($item['role_name'],ENT_QUOTES,'UTF-8') ?></span></td><td><?= htmlspecialchars(date('M j, Y',strtotime($item['created_at'])),ENT_QUOTES,'UTF-8') ?></td><td class="actions-cell">
<?php if(\App\Core\Auth::can('users.update')): ?><a href="<?= htmlspecialchars(url('admin/users/'.$item['id'].'/edit'),ENT_QUOTES,'UTF-8') ?>">Edit</a><?php endif; ?>
<?php if(\App\Core\Auth::can('users.delete')): ?><form method="post" action="<?= htmlspecialchars(url('admin/users/'.$item['id'].'/delete'),ENT_QUOTES,'UTF-8') ?>" onsubmit="return confirm('Delete this user?')"><?= csrf_field() ?><button class="danger-link" type="submit">Delete</button></form><?php endif; ?>
</td></tr><?php endforeach; ?>
<?php if(!$result['items']): ?><tr><td colspan="4" class="empty-table">No users found.</td></tr><?php endif; ?></tbody></table></div>
<?php require BASE_PATH.'/app/Views/partials/pagination.php'; ?>
