<div>
    <div class="page-heading">
        <div>
            <h1>Rate Limits</h1>
            <p>Control request limits and manage paused or blocked IP addresses.</p>
        </div>
    </div>

    <div class="settings-grid">
        <div class="card">
            <h2>Configuration</h2>
            <?php if (hasPermission('sudo')): ?>
                <form method="post" action="<?= url('rate-limits') ?>">
                    <?= csrf_field() ?>

                    <label class="checkbox-option">
                        <input type="checkbox" name="enabled" value="1" <?= $settings['enabled'] ? 'checked' : '' ?>>
                        <span>
                            <strong>Enable rate limiting</strong>
                            <small>Apply the configured rules to application requests.</small>
                        </span>
                    </label>

                    <label>
                        Requests per second
                        <input type="number" name="requests_per_second" value="<?= (int) $settings['requests_per_second'] ?>" min="1" max="1000" required>
                    </label>

                    <label>
                        Pause duration in seconds
                        <input type="number" name="pause_seconds" value="<?= (int) $settings['pause_seconds'] ?>" min="1" max="86400" required>
                    </label>

                    <label>
                        Violations before permanent block
                        <input type="number" name="max_violations" value="<?= (int) $settings['max_violations'] ?>" min="1" max="20" required>
                    </label>

                    <button type="submit">Save configuration</button>
                </form>
            <?php else: ?>
                <div class="config-list">
                    <div><span>Status</span><strong><?= $settings['enabled'] ? 'Enabled' : 'Disabled' ?></strong></div>
                    <div><span>Requests per second</span><strong><?= (int) $settings['requests_per_second'] ?></strong></div>
                    <div><span>Pause duration</span><strong><?= (int) $settings['pause_seconds'] ?> seconds</strong></div>
                    <div><span>Violations before block</span><strong><?= (int) $settings['max_violations'] ?></strong></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>How it works</h2>
            <p>Requests are counted per IP in one-second windows.</p>
            <p>Exceeding the limit pauses the IP for the configured duration.</p>
            <p>After the configured number of violations, the IP remains blocked until an administrator unblocks it.</p>
        </div>
    </div>

    <div class="subheading">
        <h2>Tracked IP addresses</h2>
        <p>Current counters, violations, pauses, and blocks.</p>
    </div>

    <form class="filters" method="get" action="<?= url('rate-limits') ?>">
        <div class="filter-group filter-search">
            <label>
                Search IP address
                <input
                    type="search"
                    name="search"
                    value="<?= e($pagination['query']['search']) ?>"
                    placeholder="For example, 192.168"
                >
            </label>
            <button type="submit">Search</button>
            <?php if ($pagination['query']['search'] !== ''): ?>
                <a class="button button-secondary" href="<?= url('rate-limits?per_page=' . $pagination['per_page']) ?>">Clear</a>
            <?php endif; ?>
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
                    <th>IP address</th>
                    <th>Requests</th>
                    <th>Violations</th>
                    <th>Paused until</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pagination['rows'] === []): ?>
                    <tr>
                        <td class="table-empty" colspan="6">
                            <?= $pagination['query']['search'] === '' ? 'No IP addresses have been tracked yet.' : 'No IP addresses match your search.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['rows'] as $ip => $entry): ?>
                        <tr>
                            <td><?= e($ip) ?></td>
                            <td><?= (int) $entry['request_count'] ?></td>
                            <td><?= (int) $entry['violations'] ?></td>
                            <td>
                                <?= (int) $entry['paused_until'] > time() ? e(date('Y-m-d H:i:s', (int) $entry['paused_until'])) : '—' ?>
                            </td>
                            <td>
                                <?php if ($entry['blocked']): ?>
                                    <span class="status-badge status-danger">Blocked</span>
                                <?php elseif ((int) $entry['paused_until'] > time()): ?>
                                    <span class="status-badge status-warning">Paused</span>
                                <?php else: ?>
                                    <span class="status-badge status-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (hasPermission('rate-limits.update')): ?>
                                    <form class="inline-form" method="post" action="<?= url('rate-limits/unblock') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="ip" value="<?= e($ip) ?>">
                                        <button class="button-secondary" type="submit">Clear</button>
                                    </form>
                                <?php else: ?>
                                    <span aria-label="No actions available">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php require dirname(__DIR__) . '/partials/pagination.php'; ?>
</div>
