<?php

declare(strict_types=1);

namespace App\Core;

use JsonException;

final class HttpResponse
{
    public function __construct(
        private readonly int $status,
        private readonly array $headers,
        private readonly string $body,
    ) {
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function json(mixed $default = null): mixed
    {
        try {
            return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $default;
        }
    }

    public function successful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function clientError(): bool
    {
        return $this->status >= 400 && $this->status < 500;
    }

    public function serverError(): bool
    {
        return $this->status >= 500;
    }
}
