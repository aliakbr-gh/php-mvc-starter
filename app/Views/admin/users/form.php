<header class="module-head"><div><span class="eyebrow">USERS</span><h1><?= $record?'Edit user':'Create user' ?></h1><p>Account details and role assignment.</p></div></header>
<form class="admin-form" method="post" action="<?= htmlspecialchars(url($record?'admin/users/'.$record['id'].'/update':'admin/users'),ENT_QUOTES,'UTF-8') ?>"><?= csrf_field() ?>
<label>Name<input name="name" value="<?= htmlspecialchars($record['name']??'',ENT_QUOTES,'UTF-8') ?>" required minlength="2"></label>
<label>Email<input type="email" name="email" value="<?= htmlspecialchars($record['email']??'',ENT_QUOTES,'UTF-8') ?>" required></label>
<label>Role<select name="role_id" required><?php foreach($roles as $role): ?><option value="<?= $role['id'] ?>" <?= (int)($record['role_id']??0)===(int)$role['id']?'selected':'' ?>><?= htmlspecialchars($role['name'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></label>
<label>Password <?= $record?'<small>Leave blank to keep the current password</small>':'' ?><input type="password" name="password" <?= $record?'':'required' ?> minlength="8"></label>
<div class="form-actions"><button class="button" type="submit">Save user</button><a href="<?= htmlspecialchars(url('admin/users'),ENT_QUOTES,'UTF-8') ?>">Cancel</a></div></form>
