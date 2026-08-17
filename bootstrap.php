<?php

declare(strict_types=1);

$GLOBALS['config'] = require __DIR__ . '/config/app.php';
date_default_timezone_set($GLOBALS['config']['timezone']);
require_once __DIR__ . '/core/functions.php';

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\' => __DIR__ . '/app/',
        'Core\\' => __DIR__ . '/core/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (str_starts_with($class, $prefix)) {
            $file = $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

            if (is_file($file)) {
                require $file;
            }
        }
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.gc_maxlifetime', (string) $GLOBALS['config']['session_lifetime']);
    session_name($GLOBALS['config']['session_name']);
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (PHP_SAPI !== 'cli') {
    \Core\ExceptionHandler::register();
}

$GLOBALS['app_settings'] = (new \Core\AppSettings())->get();
$GLOBALS['config']['name'] = $GLOBALS['app_settings']['app_name'];

$bootstrapRequestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$bootstrapBasePath = (string) (parse_url((string) ($GLOBALS['config']['base_url'] ?? ''), PHP_URL_PATH) ?: '');
if ($bootstrapBasePath !== '' && str_starts_with($bootstrapRequestPath, $bootstrapBasePath)) {
    $bootstrapRequestPath = substr($bootstrapRequestPath, strlen($bootstrapBasePath)) ?: '/';
}

if (PHP_SAPI !== 'cli' && !str_starts_with($bootstrapRequestPath, '/api/')) {
    enforce_session_security();
}
