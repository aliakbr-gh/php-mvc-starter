<?php

declare(strict_types=1);

namespace Core;

use JsonException;

final class HttpClientResponse
{
    public function __construct(
        private readonly int $statusCode,
        private readonly array $headers,
        private readonly string $body
    ) {
    }

    public function status(): int
    {
        return $this->statusCode;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        $values = $this->headers[strtolower($name)] ?? [];

        return $values === [] ? null : implode(', ', $values);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function successful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function json(): array
    {
        $decoded = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new JsonException('The response body does not contain a JSON array or object.');
        }

        return $decoded;
    }
}
