<?php

declare(strict_types=1);

namespace App\Controllers\API\V1;

use Core\API\APIAuthenticator;
use Core\API\Request;
use Core\API\Response;

final class ExampleController
{
    public function index(): never
    {
        Response::success([
            'client' => APIAuthenticator::client()['client_id'] ?? null,
            'query' => Request::capture()->query(),
        ], 'Protected API endpoint reached successfully.');
    }

    public function open(): never
    {
        Response::success([
            'client' => APIAuthenticator::client()['client_id'] ?? null,
        ], 'Public API endpoint reached successfully.');
    }

    public function show(): never
    {
        Response::success([
            'id' => Request::capture()->route('id'),
        ], 'Protected API resource retrieved successfully.');
    }
}
