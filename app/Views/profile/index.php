<div>
    <div class="page-heading">
        <div>
            <h1>My Profile</h1>
            <p>Review your account, role, and access permissions.</p>
        </div>
        <a class="button" href="<?= url('profile/password') ?>">Change password</a>
    </div>

    <div class="profile-layout">
        <div class="card">
            <div class="profile-summary">
                <span class="profile-avatar">
                    <?= e(strtoupper(substr($profile['name'], 0, 1))) ?>
                </span>
                <div>
                    <h2><?= e($profile['name']) ?></h2>
                    <p>@<?= e($profile['username']) ?></p>
                </div>
            </div>

            <div class="profile-details">
                <div>
                    <span>User ID</span>
                    <strong>#<?= (int) $profile['id'] ?></strong>
                </div>
                <div>
                    <span>Full name</span>
                    <strong><?= e($profile['name']) ?></strong>
                </div>
                <div>
                    <span>Username</span>
                    <strong>@<?= e($profile['username']) ?></strong>
                </div>
                <div>
                    <span>Role</span>
                    <strong><?= e($profile['role_name']) ?></strong>
                </div>
                <div>
                    <span>Account created</span>
                    <strong><?= e(date('M j, Y', strtotime($profile['created_at']))) ?></strong>
                </div>
                <div>
                    <span>Last updated</span>
                    <strong><?= e(date('M j, Y H:i', strtotime($profile['updated_at']))) ?></strong>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-heading">
                <div>
                    <h2>Role permissions</h2>
                    <p>Permissions currently assigned through <?= e($profile['role_name']) ?>.</p>
                </div>
            </div>

            <?php if ($hasSudo): ?>
                <div class="access-notice">
                    <strong>Full system access</strong>
                    <span>The sudo permission grants access to every capability listed below.</span>
                </div>
            <?php endif; ?>

            <?php if ($permissions === []): ?>
                <p class="empty-state">No permissions are assigned to this role.</p>
            <?php else: ?>
                <div class="permission-list">
                    <?php foreach ($permissions as $permission): ?>
                        <div class="permission-list-item">
                            <div>
                                <strong><?= e($permission['name']) ?></strong>
                                <small><?= e($permission['slug']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
