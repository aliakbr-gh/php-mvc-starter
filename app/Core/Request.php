<?php

declare(strict_types=1);

namespace App\Core;

use JsonException;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly string $rawBody = '',
        private readonly mixed $jsonBody = null,
        private readonly bool $jsonValid = false,
    ) {
    }

    public static function capture(): self
    {
        $uriPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $publicPosition = strpos($script, '/public/index.php');
        $basePath = $publicPosition === false ? rtrim(dirname($script), '/') : substr($script, 0, $publicPosition);

        if ($basePath !== '' && str_starts_with($uriPath, $basePath)) {
            $uriPath = substr($uriPath, strlen($basePath)) ?: '/';
        }

        $rawBody = (string) file_get_contents('php://input');
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        $body = $_POST;
        $jsonBody = null;
        $jsonValid = false;

        if ($contentType === 'application/json' || str_ends_with($contentType, '+json')) {
            try {
                $jsonBody = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
                $jsonValid = true;

                if (is_array($jsonBody)) {
                    $body = $jsonBody;
                }
            } catch (JsonException) {
                $jsonBody = null;
            }
        } elseif ($body === [] && $rawBody !== '' && $contentType === 'application/x-www-form-urlencoded') {
            parse_str($rawBody, $body);
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            '/' . ltrim($uriPath, '/'),
            $_GET,
            $body,
            $rawBody,
            $jsonBody,
            $jsonValid,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function raw(): string
    {
        return $this->rawBody;
    }

    public function json(mixed $default = null): mixed
    {
        return $this->jsonValid ? $this->jsonBody : $default;
    }

    public function hasValidJson(): bool
    {
        return $this->jsonValid;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
