<header class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <p class="text-body-secondary mb-0">A quick overview of your account.</p>
    </div>
    <span class="badge text-bg-primary"><?= e($user['role_name']) ?></span>
</header>

<div class="row g-3 mb-4">
    <?php
    $stats = [
        ['Account', $user['name']],
        ['Email', $user['email']],
        ['Member since', date('M Y', strtotime($user['created_at']))],
    ];
    foreach ($stats as [$label, $value]):
        ?>
        <div class="col-12 col-md-4">
            <article class="card h-100 shadow-sm">
                <div class="card-body">
                    <span
                        class="small text-uppercase text-body-secondary"><?= e($label) ?></span>
                    <strong class="d-block mt-2 text-break"><?= e($value) ?></strong>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<section class="card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5">Role-based access is active</h2>
        <p class="text-body-secondary mb-0">Your sidebar is generated from your role. Administrators see the protected
            admin area; standard users do not.</p>
    </div>
</section>

<section class="card shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-start gap-3 py-3">
        <div>
            <h2 class="h5 mb-1">Recent activity</h2>
            <p class="small text-body-secondary mb-0">
                <?= \App\Core\Auth::hasRole('admin') ? 'Latest activity from every user.' : 'Your latest account activity.' ?>
            </p>
        </div>
        <span class="badge text-bg-secondary"><?= count($activities) ?> events</span>
    </div>
    <?php if ($activities): ?>
        <div class="list-group list-group-flush">
            <?php foreach ($activities as $activity): ?>
                <article class="list-group-item d-flex gap-3 py-3">
                    <span class="activity-dot flex-shrink-0"></span>
                    <div>
                        <strong class="d-block"><?= e($activity['activity']) ?></strong>
                        <time class="small text-body-secondary"
                            datetime="<?= e($activity['created_at']) ?>">
                            <?= e(date('M j, Y · g:i A', strtotime($activity['created_at']))) ?>
                        </time>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card-body text-body-secondary">No activity has been recorded yet.</div>
    <?php endif; ?>
</section>