<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Config;
use App\Core\Logger;

require BASE_PATH . '/app/Core/Autoloader.php';
require BASE_PATH . '/app/Core/helpers.php';

$config = require BASE_PATH . '/config/config.php';
Config::load($config);
date_default_timezone_set($config['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['session']['name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

Logger::configure($config['logging']);

return new Application($config);
