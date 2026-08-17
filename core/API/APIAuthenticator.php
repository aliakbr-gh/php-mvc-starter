<?php

declare(strict_types=1);

namespace Core\API;

use App\Models\APIClient;
use Throwable;

final class APIAuthenticator
{
    private static ?array $client = null;

    public static function authenticate(): void
    {
        $token = Request::capture()->bearerToken();
        if ($token === null) {
            Response::unauthorized('A bearer access token is required.');
        }

        $config = $GLOBALS['config']['api'];
        try {
            $claims = JWT::decode(
                $token,
                (string) $config['jwt_secret'],
                (string) $config['issuer'],
                (string) $config['audience']
            );
        } catch (Throwable) {
            Response::unauthorized();
        }

        $client = (new APIClient())->findActiveByClientId((string) $claims['sub']);
        if ($client === null || (int) ($claims['ver'] ?? 0) !== $client['token_version']) {
            Response::unauthorized();
        }

        self::$client = $client;
    }

    public static function client(): ?array
    {
        return self::$client;
    }
}
