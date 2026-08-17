<?php

declare(strict_types=1);

use Core\Database;

require dirname(__DIR__) . '/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

Database::connection()->query(
    'CREATE TABLE IF NOT EXISTS api_clients (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        client_id VARCHAR(100) NOT NULL UNIQUE,
        client_secret_hash VARCHAR(255) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        token_version BIGINT UNSIGNED NOT NULL DEFAULT 1,
        last_authenticated_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )'
);

echo "API database migration completed.\n";
