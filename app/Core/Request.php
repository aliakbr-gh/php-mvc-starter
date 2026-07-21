<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
    ) {}

    public static function capture(): self
    {
        $uriPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $publicPosition = strpos($script, '/public/index.php');
        $basePath = $publicPosition === false ? rtrim(dirname($script), '/') : substr($script, 0, $publicPosition);

        if ($basePath !== '' && str_starts_with($uriPath, $basePath)) {
            $uriPath = substr($uriPath, strlen($basePath)) ?: '/';
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            '/' . ltrim($uriPath, '/'),
            $_GET,
            $_POST,
        );
    }

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    public function query(string $key, mixed $default = null): mixed { return $this->query[$key] ?? $default; }
    public function input(string $key, mixed $default = null): mixed { return $this->body[$key] ?? $default; }
    public function all(): array { return array_merge($this->query, $this->body); }
    public function ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; }
}
