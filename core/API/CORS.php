<?php

declare(strict_types=1);

namespace Core\API;

final class CORS
{
    public static function apply(): void
    {
        if (!APIContext::isApiRequest()) {
            return;
        }

        $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
        $allowedOrigins = $GLOBALS['config']['api']['cors_origins'] ?? [];
        $originIsAllowed = $origin !== '' && in_array($origin, $allowedOrigins, true);

        if ($originIsAllowed) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin', false);
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
            header('Access-Control-Max-Age: 86400');
        }

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'OPTIONS') {
            http_response_code($originIsAllowed ? 204 : 403);
            exit;
        }
    }
}
