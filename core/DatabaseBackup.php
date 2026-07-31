<?php

declare(strict_types=1);

namespace Core;

use mysqli;
use RuntimeException;
use ZipArchive;

final class DatabaseBackup
{
    public function createSqlDump(string $path): void
    {
        $database = Database::connection();
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Unable to create the database backup file.');
        }

        try {
            $this->write(
                $handle,
                '-- ' . $GLOBALS['config']['name'] . ' database backup' . PHP_EOL
            );
            $this->write($handle, '-- Created: ' . date(DATE_ATOM) . PHP_EOL);
            $this->write($handle, 'SET NAMES utf8mb4;' . PHP_EOL);
            $this->write($handle, 'SET FOREIGN_KEY_CHECKS=0;' . PHP_EOL . PHP_EOL);

            $objects = $database
                ->query("SHOW FULL TABLES WHERE Table_type IN ('BASE TABLE', 'VIEW')")
                ->fetch_all(MYSQLI_NUM);
            $tables = array_column(array_filter($objects, static fn (array $row): bool => $row[1] === 'BASE TABLE'), 0);
            $views = array_column(array_filter($objects, static fn (array $row): bool => $row[1] === 'VIEW'), 0);

            foreach ($tables as $table) {
                $identifier = $this->quoteIdentifier((string) $table);
                $create = $database->query('SHOW CREATE TABLE ' . $identifier)->fetch_assoc();
                $createStatement = (string) ($create['Create Table'] ?? '');
                $this->write($handle, 'DROP TABLE IF EXISTS ' . $identifier . ';' . PHP_EOL);
                $this->write($handle, $createStatement . ';' . PHP_EOL . PHP_EOL);
            }

            foreach ($tables as $table) {
                $this->writeTableData($database, $handle, (string) $table);
            }

            foreach ($views as $view) {
                $identifier = $this->quoteIdentifier((string) $view);
                $create = $database->query('SHOW CREATE VIEW ' . $identifier)->fetch_assoc();
                $createStatement = (string) ($create['Create View'] ?? '');
                $this->write($handle, 'DROP VIEW IF EXISTS ' . $identifier . ';' . PHP_EOL);
                $this->write($handle, $createStatement . ';' . PHP_EOL . PHP_EOL);
            }

            $this->write($handle, 'SET FOREIGN_KEY_CHECKS=1;' . PHP_EOL);
        } finally {
            fclose($handle);
        }
    }

    public function createUploadsArchive(string $path): void
    {
        $zip = $this->openZip($path);
        $zip->addEmptyDir('uploads');
        $this->addDirectory($zip, dirname(__DIR__) . '/public/uploads', 'uploads');
        $zip->close();
    }

    public function createFullArchive(string $path, string $sqlPath): void
    {
        $zip = $this->openZip($path);
        $zip->addFile($sqlPath, 'database.sql');
        $zip->addEmptyDir('uploads');
        $this->addDirectory($zip, dirname(__DIR__) . '/public/uploads', 'uploads');
        $zip->close();
    }

    private function writeTableData(mysqli $database, mixed $handle, string $table): void
    {
        $identifier = $this->quoteIdentifier($table);
        $result = $database->query('SELECT * FROM ' . $identifier);

        while ($row = $result->fetch_assoc()) {
            $columns = array_map(fn (string $column): string => $this->quoteIdentifier($column), array_keys($row));
            $values = array_map(
                static fn (mixed $value): string => $value === null
                    ? 'NULL'
                    : "'" . $database->real_escape_string((string) $value) . "'",
                array_values($row)
            );

            $this->write(
                $handle,
                'INSERT INTO ' . $identifier
                . ' (' . implode(', ', $columns) . ') VALUES ('
                . implode(', ', $values) . ');' . PHP_EOL
            );
        }

        $this->write($handle, PHP_EOL);
    }

    private function openZip(string $path): ZipArchive
    {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create the backup archive.');
        }

        return $zip;
    }

    private function addDirectory(ZipArchive $zip, string $directory, string $archiveDirectory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($directory) + 1);
            $zip->addFile($file->getPathname(), $archiveDirectory . '/' . $relative);
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function write(mixed $handle, string $contents): void
    {
        if (fwrite($handle, $contents) === false) {
            throw new RuntimeException('Unable to write the database backup.');
        }
    }
}
