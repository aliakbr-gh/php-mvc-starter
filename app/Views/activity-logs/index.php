<div>
    <div class="page-heading">
        <div>
            <h1>Activity Logs</h1>
            <p>Review actions performed by authenticated users.</p>
        </div>
    </div>

    <form class="filters" method="get" action="<?= url('activity-logs') ?>">
        <div class="filter-group filter-search">
            <label>
                Activity date
                <input type="date" name="date" value="<?= e($date) ?>">
            </label>
            <button type="submit">View activities</button>
            <a class="button button-secondary" href="<?= url('activity-logs?per_page=' . $pagination['per_page']) ?>">Clear</a>
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
                    <th>Activity</th>
                    <th>Username</th>
                    <th>IP address</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pagination['rows'] === []): ?>
                    <tr>
                        <td class="table-empty" colspan="4">No activities found for this date.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['rows'] as $activity): ?>
                        <tr>
                            <td><?= e(date('H:i:s', strtotime($activity['created_at']))) ?></td>
                            <td>
                                <strong><?= e($activity['user_name']) ?></strong>
                                <?= e($activity['description']) ?>
                            </td>
                            <td>@<?= e($activity['username']) ?></td>
                            <td><?= e($activity['ip_address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php require dirname(__DIR__) . '/partials/pagination.php'; ?>
</div>
