<?php

declare(strict_types=1);

namespace App\Controllers\API\V1;

use App\Models\User;
use Core\API\JWT;
use Core\API\Request;
use Core\API\Response;
use Throwable;

final class AuthController
{
    public function login(): never
    {
        $request = Request::capture();
        if ($request->hasInvalidJson()) {
            Response::error('The request body must contain valid JSON.', 400);
        }

        $username = strtolower(trim((string) $request->json('username', '')));
        $password = (string) $request->json('password', '');
        $errors = [];
        if ($username === '') {
            $errors['username'][] = 'The username field is required.';
        }
        if ($password === '') {
            $errors['password'][] = 'The password field is required.';
        }
        if ($errors !== []) {
            Response::validation($errors);
        }

        $model = new User();
        $account = $model->findByUsername($username);
        if ($account === null || !password_verify($password, (string) $account['password'])) {
            usleep(random_int(100000, 250000));
            Response::unauthorized('The username or password is incorrect.');
        }

        if (!(bool) $account['is_active']) {
            Response::unauthorized('This user account is inactive.');
        }

        $config = $GLOBALS['config']['api'];
        $now = time();
        $lifetime = (int) $config['token_lifetime'];
        $claims = [
            'iss' => (string) $config['issuer'],
            'sub' => (string) $account['id'],
            'aud' => (string) $config['audience'],
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $lifetime,
            'jti' => bin2hex(random_bytes(16)),
            'type' => 'user',
            'username' => (string) $account['username'],
            'ver' => (int) $account['session_version'],
        ];

        try {
            $token = JWT::encode($claims, (string) $config['jwt_secret']);
        } catch (Throwable) {
            Response::error('API authentication is not configured correctly.', 500);
        }

        Response::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $lifetime,
        ], 'Login successful.');
    }
}
