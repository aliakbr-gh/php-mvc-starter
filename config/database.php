<?php

$environment = strtolower(trim((string) (getenv('APP_ENV') ?: 'development')));
$environment = match ($environment) {
    'local' => 'development',
    'test' => 'testing',
    'prod' => 'production',
    default => $environment,
};

$configs = [
    'development' => [
        'host' => '127.0.0.1',
        'port' => PHP_OS_FAMILY === 'Darwin' ? 3305 : 3306,
        'database' => 'mvc_db',
        'username' => 'root',
        'password' => 'root',
    ],
    'testing' => [
        'host' => '127.0.0.1',
        'port' => PHP_OS_FAMILY === 'Darwin' ? 3305 : 3306,
        'database' => 'mvc_db_test',
        'username' => 'root',
        'password' => 'root',
    ],
    'production' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => '',
        'username' => '',
        'password' => '',
    ],
];

$defaults = $configs[$environment] ?? $configs['production'];

return [
    'host' => getenv('DB_HOST') ?: $defaults['host'],
    'port' => (int) (getenv('DB_PORT') ?: $defaults['port']),
    'database' => getenv('DB_DATABASE') ?: $defaults['database'],
    'username' => getenv('DB_USERNAME') ?: $defaults['username'],
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : $defaults['password'],
];
