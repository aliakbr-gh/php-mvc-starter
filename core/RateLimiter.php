<?php

declare(strict_types=1);

namespace Core;

final class RateLimiter
{
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
        return self::stateStore()->read();
    }

    public static function unblock(string $ip): void
    {
        self::stateStore()->update(static function (array $state) use ($ip): array {
            unset($state['ips'][$ip]);
            return $state;
        });
    }

    public static function enforce(): void
    {
        $settings = self::settings();

        if (!$settings['enabled']) {
            return;
        }

        $ip = client_ip();
        $now = microtime(true);
        $decision = null;

        self::stateStore()->update(static function (array $state) use ($ip, $now, $settings, &$decision): array {
            $entry = $state['ips'][$ip] ?? [
                'window_started_at' => $now,
                'request_count' => 0,
                'violations' => 0,
                'last_violation_window' => 0,
                'paused_until' => 0,
                'blocked' => false,
                'last_request_at' => 0,
            ];

            if ($entry['blocked']) {
                $decision = 'blocked';
                return $state;
            }

            if ((float) $entry['paused_until'] > $now) {
                $decision = 'paused';
                return $state;
            }

            if ($now - (float) $entry['window_started_at'] >= 1) {
                $entry['window_started_at'] = $now;
                $entry['request_count'] = 0;
            }

            $entry['request_count']++;
            $entry['last_request_at'] = time();
            $windowId = (int) floor($entry['window_started_at']);

            if ($entry['request_count'] > (int) $settings['requests_per_second']) {
                if ((int) $entry['last_violation_window'] !== $windowId) {
                    $entry['violations']++;
                    $entry['last_violation_window'] = $windowId;
                }

                if ($entry['violations'] >= (int) $settings['max_violations']) {
                    $entry['blocked'] = true;
                    $decision = 'blocked';
                } else {
                    $entry['paused_until'] = time() + (int) $settings['pause_seconds'];
                    $decision = 'paused';
                }
            }

            $state['ips'][$ip] = $entry;
            return $state;
        });

        if ($decision === 'blocked') {
            abort(429, 'This IP address has been blocked. Contact an administrator.');
        }

        if ($decision === 'paused') {
            abort(429, 'Too many requests. This IP address is temporarily paused.');
        }
    }

    private static function stateStore(): JsonStore
    {
        return new JsonStore(
            dirname(__DIR__) . '/storage/cache/rate-limit-state.json',
            ['ips' => []]
        );
    }
}
