<?php

declare(strict_types=1);

namespace Core\API;

final class APIContext
{
    private static bool $apiRequest = false;

    public static function setRequestPath(string $path): void
    {
        self::$apiRequest = $path === '/api' || str_starts_with($path, '/api/');
    }

    public static function isApiRequest(): bool
    {
        return self::$apiRequest;
    }
}
