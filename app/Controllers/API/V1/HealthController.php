<?php

declare(strict_types=1);

namespace App\Controllers\API\V1;

use Core\API\Response;
use Core\Database;
use Throwable;

final class HealthController
{
    public function server(): never
    {
        $storagePath = dirname(__DIR__, 4) . '/storage';
        $storageWritable = is_dir($storagePath) && is_writable($storagePath);
        $status = $storageWritable ? 'healthy' : 'degraded';

        Response::success([
            'status' => $status,
            'php_version' => PHP_VERSION,
            'server_software' => (string) ($_SERVER['SERVER_SOFTWARE'] ?? PHP_SAPI),
            'timezone' => date_default_timezone_get(),
            'server_time' => date(DATE_ATOM),
            'storage_writable' => $storageWritable,
        ], 'Server health check completed.', $storageWritable ? 200 : 503);
    }

    public function database(): never
    {
        $startedAt = microtime(true);

        try {
            $connection = Database::connection();
            $connection->query('SELECT 1');

            Response::success([
                'status' => 'healthy',
                'version' => $connection->server_info,
                'latency_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            ], 'Database health check completed.');
        } catch (Throwable) {
            Response::success([
                'status' => 'unavailable',
                'version' => null,
                'latency_ms' => null,
            ], 'Database health check failed.', 503);
        }
    }
}
