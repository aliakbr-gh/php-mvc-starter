<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Throwable;

final class HealthController extends Controller
{
    public function index(): void
    {
        $storagePath = dirname(__DIR__, 2) . '/storage';
        $server = [
            'status' => 'healthy',
            'php_version' => PHP_VERSION,
            'server_software' => (string) ($_SERVER['SERVER_SOFTWARE'] ?? PHP_SAPI),
            'timezone' => date_default_timezone_get(),
            'server_time' => date(DATE_ATOM),
            'storage_writable' => is_dir($storagePath) && is_writable($storagePath),
        ];

        if (!$server['storage_writable']) {
            $server['status'] = 'degraded';
        }

        $database = [
            'status' => 'unavailable',
            'version' => null,
            'latency_ms' => null,
        ];

        try {
            $startedAt = microtime(true);
            $connection = Database::connection();
            $connection->query('SELECT 1');
            $database['latency_ms'] = round((microtime(true) - $startedAt) * 1000, 2);
            $database['version'] = $connection->server_info;
            $database['status'] = 'healthy';
        } catch (Throwable) {
            $database['status'] = 'unavailable';
        }

        $healthy = $server['status'] === 'healthy' && $database['status'] === 'healthy';
        http_response_code($healthy ? 200 : 503);

        $this->view('health/index', [
            'title' => 'System Health',
            'healthy' => $healthy,
            'server' => $server,
            'database' => $database,
        ]);
    }
}
