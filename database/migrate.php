<?php

declare(strict_types=1);

use Core\Database;
use Core\MigrationRunner;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$command = strtolower((string) ($argv[1] ?? 'migrate'));

if (!in_array($command, ['migrate', 'status', 'rollback'], true)) {
    fwrite(STDERR, 'Usage: php migrate.php [migrate|status|rollback]' . PHP_EOL);
    exit(1);
}

require dirname(__DIR__) . '/bootstrap.php';

try {
    $runner = new MigrationRunner(Database::connection(), __DIR__ . '/migrations');

    if ($command === 'status') {
        $rows = $runner->status();

        if ($rows === []) {
            echo 'No migration files found.' . PHP_EOL;
            exit(0);
        }

        echo str_pad('Status', 14) . str_pad('Batch', 8) . 'Migration' . PHP_EOL;
        echo str_repeat('-', 72) . PHP_EOL;

        foreach ($rows as $row) {
            echo str_pad($row['status'], 14)
                . str_pad($row['batch'] === null ? '-' : (string) $row['batch'], 8)
                . $row['name']
                . PHP_EOL;
        }

        exit(0);
    }

    if ($command === 'rollback') {
        $migrations = $runner->rollback();
        echo $migrations === []
            ? 'Nothing to roll back.' . PHP_EOL
            : 'Rolled back:' . PHP_EOL . '  ' . implode(PHP_EOL . '  ', $migrations) . PHP_EOL;
        exit(0);
    }

    $migrations = $runner->migrate();
    echo $migrations === []
        ? 'Database is already up to date.' . PHP_EOL
        : 'Migrated:' . PHP_EOL . '  ' . implode(PHP_EOL . '  ', $migrations) . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
