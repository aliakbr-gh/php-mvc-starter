<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\ActivityLogger;
use Core\Controller;
use Core\Paginator;
use Core\RateLimiter;

final class RateLimitController extends Controller
{
    public function index(): void
    {
        $request = Paginator::request(10);
        $state = RateLimiter::state();
        $ips = $state['ips'];

        if ($request['search'] !== '') {
            $search = strtolower($request['search']);
            $ips = array_filter(
                $ips,
                static fn (array $entry, string $ip): bool => str_contains(strtolower($ip), $search),
                ARRAY_FILTER_USE_BOTH
            );
        }

        ksort($ips, SORT_NATURAL);
        $total = count($ips);
        $page = $request['page'];
        $offset = Paginator::offset($total, $page, $request['per_page']);
        $pagination = Paginator::result(
            array_slice($ips, $offset, $request['per_page'], true),
            $total,
            $page,
            $request['per_page'],
            ['search' => $request['search']]
        );

        $this->view('rate-limits/index', [
            'title' => 'Rate Limits',
            'settings' => RateLimiter::settings(),
            'pagination' => $pagination,
            'allowedPerPage' => Paginator::PER_PAGE_OPTIONS,
            'paginationPath' => 'rate-limits',
        ]);
    }

    public function update(): void
    {
        $requestsPerSecond = (int) ($_POST['requests_per_second'] ?? 0);
        $pauseSeconds = (int) ($_POST['pause_seconds'] ?? 0);
        $maxViolations = (int) ($_POST['max_violations'] ?? 0);

        if ($requestsPerSecond < 1 || $requestsPerSecond > 1000) {
            flash('error', 'Requests per second must be between 1 and 1000.');
            $this->redirect('rate-limits');
        }

        if ($pauseSeconds < 1 || $pauseSeconds > 86400) {
            flash('error', 'Pause duration must be between 1 and 86400 seconds.');
            $this->redirect('rate-limits');
        }

        if ($maxViolations < 1 || $maxViolations > 20) {
            flash('error', 'Maximum violations must be between 1 and 20.');
            $this->redirect('rate-limits');
        }

        RateLimiter::saveSettings([
            'enabled' => isset($_POST['enabled']),
            'requests_per_second' => $requestsPerSecond,
            'pause_seconds' => $pauseSeconds,
            'max_violations' => $maxViolations,
        ]);

        ActivityLogger::log('updated rate-limit configuration');
        flash('success', 'Rate-limit configuration updated successfully.');
        $this->redirect('rate-limits');
    }

    public function unblock(): void
    {
        $ip = (string) ($_POST['ip'] ?? '');

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            flash('error', 'Invalid IP address.');
            $this->redirect('rate-limits');
        }

        RateLimiter::unblock($ip);
        ActivityLogger::log('unblocked IP address ' . $ip);
        flash('success', 'IP address was unblocked and its rate-limit history was cleared.');
        $this->redirect('rate-limits');
    }
}
