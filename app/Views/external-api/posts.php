<div class="page-heading">
    <div>
        <h1>External API Demo</h1>
        <p>Posts loaded server-side from JSONPlaceholder using the reusable HTTP client.</p>
    </div>
</div>

<?php if ($error !== null): ?>
    <div class="card">
        <p><?= e($error) ?></p>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Post</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= e($post['id'] ?? '—') ?></td>
                            <td><?= e($post['title'] ?? '—') ?></td>
                            <td><?= e($post['body'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
