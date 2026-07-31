<?php

declare(strict_types=1);

namespace Core;

final class RequestLogger
{
    public static function register(float $startedAt): void
    {
        if (!($GLOBALS['config']['request_logging'] ?? false)) {
            return;
        }

        register_shutdown_function(static function () use ($startedAt): void {
            $directory = dirname(__DIR__) . '/storage/logs';

            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $entry = [
                'timestamp' => date(DATE_ATOM),
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'uri' => $_SERVER['REQUEST_URI'] ?? '/',
                'status' => http_response_code() ?: 200,
                'ip' => client_ip(),
                'user_id' => user()['id'] ?? null,
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            ];

            file_put_contents(
                $directory . '/' . date('Y-m-d') . '.log',
                json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        });
    }

    public static function read(string $date): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return [];
        }

        $path = dirname(__DIR__) . '/storage/logs/' . $date . '.log';

        if (!is_file($path)) {
            return [];
        }

        $rows = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $rows[] = $decoded;
            }
        }

        return array_reverse($rows);
    }
}
