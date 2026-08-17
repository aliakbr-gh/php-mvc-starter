<?php

declare(strict_types=1);

use App\Controllers\API\V1\AuthController;
use App\Controllers\API\V1\ExampleController;
use App\Controllers\API\V1\HealthController;

$router->post('/api/v1/auth/login', [AuthController::class, 'login']);
$router->get('/api/v1/health/server', [HealthController::class, 'server']);
$router->get('/api/v1/health/database', [HealthController::class, 'database']);
$router->get('/api/v1/example', [ExampleController::class, 'index'], ['api-auth']);
$router->post('/api/v1/open', [ExampleController::class, 'open']);
$router->get('/api/v1/example/{id}', [ExampleController::class, 'show'], ['api-auth']);
