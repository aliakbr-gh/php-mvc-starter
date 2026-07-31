<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

final class JsonStore
{
    public function __construct(
        private readonly string $path,
        private readonly array $defaults = []
    ) {
    }

    public function read(): array
    {
        if (!is_file($this->path)) {
            $this->write($this->defaults);
        }

        $handle = fopen($this->path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open JSON storage.');
        }

        try {
            flock($handle, LOCK_SH);
            $contents = stream_get_contents($handle);
            $data = json_decode($contents ?: '{}', true, 512, JSON_THROW_ON_ERROR);
            return array_replace_recursive($this->defaults, is_array($data) ? $data : []);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function write(array $data): void
    {
        $directory = dirname($this->path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create JSON storage directory.');
        }

        $handle = fopen($this->path, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Unable to write JSON storage.');
        }

        try {
            flock($handle, LOCK_EX);
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function update(callable $callback): array
    {
        $directory = dirname($this->path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create JSON storage directory.');
        }

        $handle = fopen($this->path, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Unable to update JSON storage.');
        }

        try {
            flock($handle, LOCK_EX);
            rewind($handle);
            $contents = stream_get_contents($handle);
            $decoded = json_decode($contents ?: '{}', true);
            $data = array_replace_recursive($this->defaults, is_array($decoded) ? $decoded : []);
            $updated = $callback($data);
            $updated = is_array($updated) ? $updated : $data;
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            fflush($handle);
            return $updated;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
