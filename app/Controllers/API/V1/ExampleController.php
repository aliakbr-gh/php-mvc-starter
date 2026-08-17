<?php

declare(strict_types=1);

namespace App\Controllers\API\V1;

use Core\API\APIAuthenticator;
use Core\API\Request;
use Core\API\Response;

final class ExampleController
{
    private const REQUEST_KEY = 'replace-with-your-private-request-key';

    public function index(): never
    {
        Response::success([
            'user' => APIAuthenticator::user()['username'] ?? null,
            'query' => Request::capture()->query(),
        ], 'Protected API endpoint reached successfully.');
    }

    public function open(): never
    {
        $request = Request::capture();
        if ($request->hasInvalidJson()) {
            Response::error('The request body must contain valid JSON.', 400);
        }

        $providedKey = (string) $request->json('key', '');
        if ($providedKey === '' || !hash_equals(self::REQUEST_KEY, $providedKey)) {
            Response::unauthorized('Invalid request key.');
        }

        Response::success([
            'payload' => $request->json('payload', []),
        ], 'JSON request key verified successfully.');
    }

    public function show(): never
    {
        Response::success([
            'id' => Request::capture()->route('id'),
        ], 'Protected API resource retrieved successfully.');
    }
}
