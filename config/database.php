<?php

$defaultPort = PHP_OS_FAMILY === 'Darwin' ? 3305 : 3306;

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('DB_PORT') ?: $defaultPort),
    'database' => getenv('DB_DATABASE') ?: 'lab360_db',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: 'root',
];
