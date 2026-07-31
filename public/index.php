<?php

declare(strict_types=1);

use Core\Router;
use Core\RateLimiter;
use Core\RequestLogger;

$requestStartedAt = microtime(true);
require dirname(__DIR__) . '/bootstrap.php';
RequestLogger::register($requestStartedAt);
RateLimiter::enforce();

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = parse_url($GLOBALS['config']['base_url'], PHP_URL_PATH) ?: '';

if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
