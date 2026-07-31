<div>
    <div class="page-heading">
        <div>
            <h1>Request Logs</h1>
            <p>Review application requests grouped by date.</p>
        </div>
        <span class="status-badge <?= $loggingEnabled ? 'status-success' : 'status-danger' ?>">
            Logging <?= $loggingEnabled ? 'enabled' : 'disabled' ?>
        </span>
    </div>

    <form class="filters" method="get" action="<?= url('logs') ?>">
        <div class="filter-group filter-search">
            <label>
                Log date
                <input type="date" name="date" value="<?= e($date) ?>">
            </label>
            <button type="submit">View logs</button>
            <a class="button button-secondary" href="<?= url('logs?per_page=' . $pagination['per_page']) ?>">Clear</a>
        </div>

        <div class="filter-group filter-page-size">
            <label>
                Per page
                <select name="per_page" data-native-select>
                    <?php foreach ($allowedPerPage as $option): ?>
                        <option value="<?= $option ?>" <?= $pagination['per_page'] === $option ? 'selected' : '' ?>>
                            <?= $option ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="button-secondary" type="submit">Apply</button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Method</th>
                    <th>URI</th>
                    <th>Status</th>
                    <th>IP address</th>
                    <th>User</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pagination['rows'] === []): ?>
                    <tr>
                        <td class="table-empty" colspan="7">No request logs found for this date.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['rows'] as $log): ?>
                        <tr>
                            <td><?= e(date('H:i:s', strtotime($log['timestamp']))) ?></td>
                            <td><?= e($log['method']) ?></td>
                            <td><?= e($log['uri']) ?></td>
                            <td><?= (int) $log['status'] ?></td>
                            <td><?= e($log['ip']) ?></td>
                            <td><?= $log['user_id'] === null ? 'Guest' : (int) $log['user_id'] ?></td>
                            <td><?= e($log['duration_ms']) ?> ms</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php require dirname(__DIR__) . '/partials/pagination.php'; ?>
</div>
