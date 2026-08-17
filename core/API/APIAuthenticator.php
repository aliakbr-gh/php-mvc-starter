<?php

declare(strict_types=1);

namespace Core\API;

use App\Models\User;
use Throwable;

final class APIAuthenticator
{
    private static ?array $user = null;

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

        if (($claims['type'] ?? null) !== 'user') {
            Response::unauthorized();
        }

        $userId = max(0, (int) $claims['sub']);
        $account = $userId > 0 ? (new User())->find($userId) : null;
        if (
            $account === null
            || !(bool) $account['is_active']
            || (int) ($claims['ver'] ?? 0) !== (int) $account['session_version']
        ) {
            Response::unauthorized();
        }

        self::$user = $account;
    }

    public static function user(): ?array
    {
        return self::$user;
    }
}
