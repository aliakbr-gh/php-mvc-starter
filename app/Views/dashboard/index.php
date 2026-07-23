<header class="page-head">
    <div><span class="eyebrow">OVERVIEW</span>
        <h1>Dashboard</h1></div>
    <span class="role-badge"><?= htmlspecialchars($user['role_name'], ENT_QUOTES, 'UTF-8') ?></span></header>
<div class="stats-grid">
    <article><span>Account</span><strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong></article>
    <article><span>Email</span><strong><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></strong></article>
    <article>
        <span>Member since</span><strong><?= htmlspecialchars(date('M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8') ?></strong>
    </article>
</div>
<section class="panel"><h2>Role-based access is active</h2>
    <p>Your sidebar is generated from your role. Administrators see the protected admin area; standard users do not.</p>
</section>
<section class="panel activity-panel">
    <div class="panel-heading">
        <div><h2>Recent activity</h2>
            <p><?= \App\Core\Auth::hasRole('admin') ? 'Latest activity from every user.' : 'Your latest account activity.' ?></p>
        </div>
        <span><?= count($activities) ?> events</span>
    </div>
    <?php if ($activities): ?>
        <div class="activity-list">
            <?php foreach ($activities as $activity): ?>
                <article>
                    <span class="activity-dot"></span>
                    <div>
                        <strong><?= htmlspecialchars($activity['activity'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <time datetime="<?= htmlspecialchars($activity['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(date('M j, Y · g:i A', strtotime($activity['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                        </time>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="empty-state">No activity has been recorded yet.</p>
    <?php endif; ?>
</section>
