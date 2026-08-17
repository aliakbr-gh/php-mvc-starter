<?php

declare(strict_types=1);

namespace Core;

use mysqli;
use Throwable;

final class RateLimiter
{
    private const CLEANUP_CHANCE = 100;

    public static function settings(): array
    {
        return (new JsonStore(
            dirname(__DIR__) . '/storage/config/rate-limit.json',
            [
                'enabled' => true,
                'requests_per_second' => 10,
                'pause_seconds' => 120,
                'max_violations' => 3,
            ]
        ))->read();
    }

    public static function saveSettings(array $settings): void
    {
        (new JsonStore(
            dirname(__DIR__) . '/storage/config/rate-limit.json',
            [
                'enabled' => true,
                'requests_per_second' => 10,
                'pause_seconds' => 120,
                'max_violations' => 3,
            ]
        ))->write($settings);
    }

    public static function state(): array
    {
        $result = Database::connection()->query(
            'SELECT ip_address, window_started_at, request_count, violations,
                    last_violation_window, paused_until, blocked, last_request_at
             FROM rate_limit_entries
             ORDER BY ip_address'
        );
        $ips = [];

        while ($row = $result->fetch_assoc()) {
            $ips[$row['ip_address']] = self::normalizeEntry($row);
        }

        return ['ips' => $ips];
    }

    public static function unblock(string $ip): void
    {
        $statement = Database::connection()->prepare(
            'DELETE FROM rate_limit_entries WHERE ip_address = ?'
        );
        $statement->bind_param('s', $ip);
        $statement->execute();
    }

    public static function enforce(): void
    {
        $settings = self::settings();

        if (!$settings['enabled']) {
            return;
        }

        $database = Database::connection();
        $ip = client_ip();
        $now = microtime(true);
        $decision = self::recordRequest($database, $ip, $now, $settings);
        self::maybeCleanup($database, (int) $settings['pause_seconds']);

        if ($decision === 'blocked') {
            abort(429, 'This IP address has been blocked. Contact an administrator.');
        }

        if ($decision === 'paused') {
            abort(429, 'Too many requests. This IP address is temporarily paused.');
        }
    }

    private static function recordRequest(mysqli $database, string $ip, float $now, array $settings): ?string
    {
        $database->begin_transaction();

        try {
            $insert = $database->prepare(
                'INSERT IGNORE INTO rate_limit_entries
                    (ip_address, window_started_at, last_request_at)
                 VALUES (?, ?, ?)'
            );
            $nowTimestamp = (int) $now;
            $insert->bind_param('sdi', $ip, $now, $nowTimestamp);
            $insert->execute();

            $select = $database->prepare(
                'SELECT window_started_at, request_count, violations,
                        last_violation_window, paused_until, blocked, last_request_at
                 FROM rate_limit_entries
                 WHERE ip_address = ?
                 FOR UPDATE'
            );
            $select->bind_param('s', $ip);
            $select->execute();
            $entry = $select->get_result()->fetch_assoc();

            if (!is_array($entry)) {
                throw new \RuntimeException('Unable to load rate-limit state.');
            }

            $entry = self::normalizeEntry($entry);
            $decision = self::applyRules($entry, $now, $settings);
            self::updateEntry($database, $ip, $entry);
            $database->commit();

            return $decision;
        } catch (Throwable $exception) {
            $database->rollback();
            throw $exception;
        }
    }

    private static function applyRules(array &$entry, float $now, array $settings): ?string
    {
        if ($entry['blocked']) {
            return 'blocked';
        }

        if ($entry['paused_until'] > $now) {
            return 'paused';
        }

        if ($now - $entry['window_started_at'] >= 1) {
            $entry['window_started_at'] = $now;
            $entry['request_count'] = 0;
        }

        $entry['request_count']++;
        $entry['last_request_at'] = (int) $now;
        $windowId = (int) floor($entry['window_started_at']);

        if ($entry['request_count'] <= (int) $settings['requests_per_second']) {
            return null;
        }

        if ($entry['last_violation_window'] !== $windowId) {
            $entry['violations']++;
            $entry['last_violation_window'] = $windowId;
        }

        if ($entry['violations'] >= (int) $settings['max_violations']) {
            $entry['blocked'] = true;
            return 'blocked';
        }

        $entry['paused_until'] = (int) $now + (int) $settings['pause_seconds'];
        return 'paused';
    }

    private static function updateEntry(mysqli $database, string $ip, array $entry): void
    {
        $statement = $database->prepare(
            'UPDATE rate_limit_entries
             SET window_started_at = ?, request_count = ?, violations = ?,
                 last_violation_window = ?, paused_until = ?, blocked = ?, last_request_at = ?
             WHERE ip_address = ?'
        );
        $blocked = $entry['blocked'] ? 1 : 0;
        $statement->bind_param(
            'diiiiiis',
            $entry['window_started_at'],
            $entry['request_count'],
            $entry['violations'],
            $entry['last_violation_window'],
            $entry['paused_until'],
            $blocked,
            $entry['last_request_at'],
            $ip
        );
        $statement->execute();
    }

    private static function normalizeEntry(array $entry): array
    {
        return [
            'window_started_at' => (float) $entry['window_started_at'],
            'request_count' => (int) $entry['request_count'],
            'violations' => (int) $entry['violations'],
            'last_violation_window' => (int) $entry['last_violation_window'],
            'paused_until' => (int) $entry['paused_until'],
            'blocked' => (bool) $entry['blocked'],
            'last_request_at' => (int) $entry['last_request_at'],
        ];
    }

    private static function maybeCleanup(mysqli $database, int $pauseSeconds): void
    {
        if (random_int(1, self::CLEANUP_CHANCE) !== 1) {
            return;
        }

        $retentionSeconds = max(86400, $pauseSeconds * 2);
        $cutoff = time() - $retentionSeconds;

        try {
            $statement = $database->prepare(
                'DELETE FROM rate_limit_entries
                 WHERE blocked = 0 AND last_request_at < ?'
            );
            $statement->bind_param('i', $cutoff);
            $statement->execute();
        } catch (Throwable) {
            // Cleanup is best-effort and must not prevent the current request.
        }
    }
}
