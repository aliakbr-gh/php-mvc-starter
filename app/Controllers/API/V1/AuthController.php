<?php

declare(strict_types=1);

namespace App\Controllers\API\V1;

use App\Models\APIClient;
use Core\API\JWT;
use Core\API\Request;
use Core\API\Response;
use Throwable;

final class AuthController
{
    public function token(): never
    {
        $request = Request::capture();
        if ($request->hasInvalidJson()) {
            Response::error('The request body must contain valid JSON.', 400);
        }

        $clientId = trim((string) $request->json('client_id', ''));
        $clientSecret = (string) $request->json('client_secret', '');
        $errors = [];
        if ($clientId === '') {
            $errors['client_id'][] = 'The client_id field is required.';
        }
        if ($clientSecret === '') {
            $errors['client_secret'][] = 'The client_secret field is required.';
        }
        if ($errors !== []) {
            Response::validation($errors);
        }

        $model = new APIClient();
        $client = $model->findActiveByClientId($clientId);
        if ($client === null || !$model->verifySecret($client, $clientSecret)) {
            usleep(random_int(100000, 250000));
            Response::unauthorized('Invalid client credentials.');
        }

        $config = $GLOBALS['config']['api'];
        $now = time();
        $lifetime = (int) $config['token_lifetime'];
        $claims = [
            'iss' => (string) $config['issuer'],
            'sub' => (string) $client['client_id'],
            'aud' => (string) $config['audience'],
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $lifetime,
            'jti' => bin2hex(random_bytes(16)),
            'ver' => (int) $client['token_version'],
        ];

        try {
            $token = JWT::encode($claims, (string) $config['jwt_secret']);
        } catch (Throwable) {
            Response::error('API authentication is not configured correctly.', 500);
        }

        $model->recordAuthentication((int) $client['id']);
        Response::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $lifetime,
        ], 'Access token issued successfully.');
    }
}
