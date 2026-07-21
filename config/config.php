<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'Core MVC',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOL),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Karachi',

    'session' => [
        'name' => 'coremvc_session',
    ],

    // Global dynamic-request limit. Exceeding max_requests blocks the client
    // for block_seconds (120 seconds by default).
    'rate_limiter' => [
        'enabled' => true,
        'max_requests' => 60,
        'window_seconds' => 60,
        'block_seconds' => 120,
        'storage_path' => BASE_PATH . '/storage/cache/rate_limits',
    ],

    // One log file is created per day, for example storage/logs/app-2026-07-21.log.
    'logging' => [
        'enabled' => true,
        'path' => BASE_PATH . '/storage/logs',
        'filename' => 'app-{date}.log',
        'date_format' => 'Y-m-d',
    ],
];
