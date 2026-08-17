<?php

declare(strict_types=1);

namespace Core\API;

final class Request
{
    private static ?self $instance = null;
    private ?array $json = null;
    private bool $invalidJson = false;
    private array $routeParameters = [];

    public static function capture(): self
    {
        return self::$instance ??= new self();
    }

    public function method(): string
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (strtolower($name) === 'content-type') {
            $key = 'CONTENT_TYPE';
        }

        $value = $_SERVER[$key] ?? null;
        if (strtolower($name) === 'authorization') {
            $value = $value ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        }

        if ((!is_string($value) || $value === '') && function_exists('getallheaders')) {
            foreach (getallheaders() as $headerName => $headerValue) {
                if (strcasecmp((string) $headerName, $name) === 0) {
                    $value = $headerValue;
                    break;
                }
            }
        }

        return is_string($value) && $value !== '' ? trim($value) : $default;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('Authorization');
        return $authorization !== null && preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches)
            ? $matches[1]
            : null;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $_GET : ($_GET[$key] ?? $default);
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        $this->decodeJson();
        return $key === null ? ($this->json ?? []) : ($this->json[$key] ?? $default);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $input = array_replace($_GET, $this->json());
        return $key === null ? $input : ($input[$key] ?? $default);
    }

    public function hasInvalidJson(): bool
    {
        $this->decodeJson();
        return $this->invalidJson;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters[$key] ?? $default;
    }

    public function setRouteParameters(array $parameters): void
    {
        $this->routeParameters = $parameters;
    }

    public function ip(): string
    {
        return client_ip();
    }

    private function decodeJson(): void
    {
        if ($this->json !== null || $this->invalidJson) {
            return;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            $this->json = [];
            return;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            $this->invalidJson = true;
            return;
        }

        $this->json = $decoded;
    }
}
