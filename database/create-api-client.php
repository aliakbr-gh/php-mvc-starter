<?php

declare(strict_types=1);

use Core\Database;

require dirname(__DIR__) . '/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$name = trim((string) ($argv[1] ?? ''));

if ($name === '') {
    fwrite(STDERR, "Usage: php database/create-api-client.php \"Client name\"\n");
    exit(1);
}

$clientId = 'client_' . bin2hex(random_bytes(12));
$clientSecret = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
$secretHash = hash('sha256', $clientSecret);

$database = Database::connection();
$legacyScopes = $database->query("SHOW COLUMNS FROM api_clients LIKE 'scopes'")->num_rows > 0;

if ($legacyScopes) {
    $emptyScopes = '';
    $statement = $database->prepare(
        'INSERT INTO api_clients (name, client_id, client_secret_hash, scopes)
         VALUES (?, ?, ?, ?)'
    );
    $statement->bind_param('ssss', $name, $clientId, $secretHash, $emptyScopes);
} else {
    $statement = $database->prepare(
        'INSERT INTO api_clients (name, client_id, client_secret_hash)
         VALUES (?, ?, ?)'
    );
    $statement->bind_param('sss', $name, $clientId, $secretHash);
}

$statement->execute();

echo "API client created. Save the secret now; it cannot be retrieved later.\n";
echo "Client ID: " . $clientId . "\n";
echo "Client secret: " . $clientSecret . "\n";
