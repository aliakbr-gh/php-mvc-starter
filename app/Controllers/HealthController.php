<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use PDO;
use Throwable;

final class HealthController
{
    public function index(): Response
    {
        $startedAt = microtime(true);
        $database = [
            'status' => 'down',
            'response_time_ms' => null,
        ];

        try {
            $databaseStartedAt = microtime(true);
            $config = require BASE_PATH . '/config/database.php';
            $connection = new PDO($config['dsn'], $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);
            $connection->query('SELECT 1');

            $database = [
                'status' => 'up',
                'response_time_ms' => round((microtime(true) - $databaseStartedAt) * 1000, 2),
            ];
        } catch (Throwable) {
            // Keep health responses safe by not exposing connection details.
        }

        $healthy = $database['status'] === 'up';

        return Response::json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'app' => [
                'status' => 'up',
                'name' => app_name(),
                'php_version' => PHP_VERSION,
            ],
            'database' => $database,
            'response_time_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'checked_at' => gmdate(DATE_ATOM),
        ], $healthy ? 200 : 503);
    }
}
