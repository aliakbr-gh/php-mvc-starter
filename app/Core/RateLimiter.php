<?php

declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    public function __construct(private readonly array $config) {}

    /** @return array{allowed: bool, retry_after: int} */
    public function attempt(string $key): array
    {
        if (!($this->config['enabled'] ?? true)) return ['allowed' => true, 'retry_after' => 0];

        $directory = $this->config['storage_path'];
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $file = $directory . '/' . hash('sha256', $key) . '.json';
        $handle = fopen($file, 'c+');
        if ($handle === false) return ['allowed' => true, 'retry_after' => 0];

        flock($handle, LOCK_EX);
        $raw = stream_get_contents($handle);
        $state = $raw ? json_decode($raw, true) : null;
        $now = time();
        $state = is_array($state) ? $state : ['started_at' => $now, 'attempts' => 0, 'blocked_until' => 0];

        if ($state['blocked_until'] > $now) {
            $result = ['allowed' => false, 'retry_after' => $state['blocked_until'] - $now];
        } else {
            if (($now - $state['started_at']) >= $this->config['window_seconds']) {
                $state = ['started_at' => $now, 'attempts' => 0, 'blocked_until' => 0];
            }
            $state['attempts']++;
            if ($state['attempts'] > $this->config['max_requests']) {
                $state['blocked_until'] = $now + $this->config['block_seconds'];
                $result = ['allowed' => false, 'retry_after' => $this->config['block_seconds']];
            } else {
                $result = ['allowed' => true, 'retry_after' => 0];
            }
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($state));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        return $result;
    }
}
