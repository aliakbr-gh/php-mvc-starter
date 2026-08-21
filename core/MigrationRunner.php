<?php

declare(strict_types=1);

namespace Core;

use mysqli;
use RuntimeException;
use Throwable;

final class MigrationRunner
{
    private const LOCK_NAME = 'phpmvc_database_migrations';

    public function __construct(
        private readonly mysqli $database,
        private readonly string $directory
    ) {
    }

    /** @return list<array{name: string, batch: int|null, executed_at: string|null, status: string}> */
    public function status(): array
    {
        $this->ensureRepository();
        $applied = $this->appliedMigrations();
        $status = [];

        foreach ($this->migrationFiles() as $name => $file) {
            $record = $applied[$name] ?? null;
            $state = 'Pending';

            if ($record !== null) {
                $state = hash_equals($record['checksum'], $this->checksum($file))
                    ? 'Applied'
                    : 'Modified';
            }

            $status[] = [
                'name' => $name,
                'batch' => $record['batch'] ?? null,
                'executed_at' => $record['executed_at'] ?? null,
                'status' => $state,
            ];
        }

        foreach ($applied as $name => $record) {
            if (!isset($this->migrationFiles()[$name])) {
                $status[] = [
                    'name' => $name,
                    'batch' => $record['batch'],
                    'executed_at' => $record['executed_at'],
                    'status' => 'Missing file',
                ];
            }
        }

        return $status;
    }

    /** @return list<string> */
    public function migrate(): array
    {
        return $this->withLock(function (): array {
            $this->ensureRepository();
            $files = $this->migrationFiles();
            $applied = $this->appliedMigrations();
            $this->assertAppliedMigrationsAreValid($files, $applied);
            $batch = $this->nextBatch();
            $completed = [];

            foreach ($files as $name => $file) {
                if (isset($applied[$name])) {
                    continue;
                }

                $migration = $this->loadMigration($file);
                $migration->up($this->database);
                $this->recordMigration($name, $batch, $this->checksum($file));
                $completed[] = $name;
            }

            return $completed;
        });
    }

    /** @return list<string> */
    public function rollback(): array
    {
        return $this->withLock(function (): array {
            $this->ensureRepository();
            $files = $this->migrationFiles();
            $applied = $this->appliedMigrations();
            $this->assertAppliedMigrationsAreValid($files, $applied);
            $batch = $this->latestBatch();

            if ($batch === 0) {
                return [];
            }

            $statement = $this->database->prepare(
                'SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC'
            );
            $statement->bind_param('i', $batch);
            $statement->execute();
            $result = $statement->get_result();
            $rolledBack = [];

            while ($row = $result->fetch_assoc()) {
                $name = (string) $row['migration'];
                $file = $files[$name] ?? null;

                if ($file === null) {
                    throw new RuntimeException('Cannot roll back missing migration file: ' . $name);
                }

                $this->loadMigration($file)->down($this->database);
                $this->removeMigration($name);
                $rolledBack[] = $name;
            }

            return $rolledBack;
        });
    }

    private function ensureRepository(): void
    {
        $this->database->query(
            'CREATE TABLE IF NOT EXISTS migrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL,
                checksum CHAR(64) NOT NULL,
                executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_migrations_batch (batch)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /** @return array<string, string> */
    private function migrationFiles(): array
    {
        if (!is_dir($this->directory)) {
            throw new RuntimeException('Migration directory does not exist: ' . $this->directory);
        }

        $paths = glob($this->directory . '/*.php');
        $files = [];

        foreach ($paths === false ? [] : $paths as $path) {
            $name = pathinfo($path, PATHINFO_FILENAME);

            if (!preg_match('/^(?:\d{14}|\d{8}_(?:\d{4}|\d{6}))_[a-z0-9_]+$/', $name)) {
                throw new RuntimeException('Invalid migration filename: ' . basename($path));
            }

            $files[$name] = $path;
        }

        ksort($files, SORT_STRING);

        return $files;
    }

    /** @return array<string, array{batch: int, checksum: string, executed_at: string}> */
    private function appliedMigrations(): array
    {
        $result = $this->database->query(
            'SELECT migration, batch, checksum, executed_at FROM migrations ORDER BY id'
        );
        $applied = [];

        while ($row = $result->fetch_assoc()) {
            $applied[(string) $row['migration']] = [
                'batch' => (int) $row['batch'],
                'checksum' => (string) $row['checksum'],
                'executed_at' => (string) $row['executed_at'],
            ];
        }

        return $applied;
    }

    /**
     * @param array<string, string> $files
     * @param array<string, array{batch: int, checksum: string, executed_at: string}> $applied
     */
    private function assertAppliedMigrationsAreValid(array $files, array $applied): void
    {
        foreach ($applied as $name => $record) {
            if (!isset($files[$name])) {
                throw new RuntimeException('Applied migration file is missing: ' . $name);
            }

            if (!hash_equals($record['checksum'], $this->checksum($files[$name]))) {
                throw new RuntimeException('Applied migration was modified: ' . $name);
            }
        }
    }

    private function loadMigration(string $file): Migration
    {
        $migration = require $file;

        if (!$migration instanceof Migration) {
            throw new RuntimeException(basename($file) . ' must return an instance of Core\\Migration.');
        }

        return $migration;
    }

    private function checksum(string $file): string
    {
        $checksum = hash_file('sha256', $file);

        if ($checksum === false) {
            throw new RuntimeException('Unable to checksum migration: ' . basename($file));
        }

        return $checksum;
    }

    private function nextBatch(): int
    {
        return $this->latestBatch() + 1;
    }

    private function latestBatch(): int
    {
        $result = $this->database->query('SELECT COALESCE(MAX(batch), 0) AS batch FROM migrations');

        return (int) ($result->fetch_assoc()['batch'] ?? 0);
    }

    private function recordMigration(string $name, int $batch, string $checksum): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO migrations (migration, batch, checksum) VALUES (?, ?, ?)'
        );
        $statement->bind_param('sis', $name, $batch, $checksum);
        $statement->execute();
    }

    private function removeMigration(string $name): void
    {
        $statement = $this->database->prepare('DELETE FROM migrations WHERE migration = ?');
        $statement->bind_param('s', $name);
        $statement->execute();
    }

    /** @template T @param callable(): T $operation @return T */
    private function withLock(callable $operation): mixed
    {
        $statement = $this->database->prepare('SELECT GET_LOCK(?, 10) AS acquired');
        $lockName = self::LOCK_NAME;
        $statement->bind_param('s', $lockName);
        $statement->execute();
        $acquired = (int) ($statement->get_result()->fetch_assoc()['acquired'] ?? 0);

        if ($acquired !== 1) {
            throw new RuntimeException('Could not acquire the database migration lock.');
        }

        try {
            return $operation();
        } finally {
            try {
                $release = $this->database->prepare('SELECT RELEASE_LOCK(?)');
                $release->bind_param('s', $lockName);
                $release->execute();
            } catch (Throwable) {
            }
        }
    }
}
