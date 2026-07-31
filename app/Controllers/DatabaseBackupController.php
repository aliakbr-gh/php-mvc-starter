<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\ActivityLogger;
use Core\Controller;
use Core\DatabaseBackup;

final class DatabaseBackupController extends Controller
{
    public function index(): void
    {
        $this->view('database-backup/index', ['title' => 'Database Backup']);
    }

    public function downloadDatabase(): never
    {
        $timestamp = date('Y-m-d_H-i-s');
        $slug = appFilenameSlug();
        $path = $this->temporaryPath($slug . '-database-', '.sql');
        (new DatabaseBackup())->createSqlDump($path);
        ActivityLogger::log('downloaded a database backup');
        $this->download($path, $slug . '-database-' . $timestamp . '.sql', 'application/sql');
    }

    public function downloadUploads(): never
    {
        $timestamp = date('Y-m-d_H-i-s');
        $slug = appFilenameSlug();
        $path = $this->temporaryPath($slug . '-uploads-', '.zip');
        (new DatabaseBackup())->createUploadsArchive($path);
        ActivityLogger::log('downloaded an uploads backup');
        $this->download($path, $slug . '-uploads-' . $timestamp . '.zip', 'application/zip');
    }

    public function downloadFull(): never
    {
        $timestamp = date('Y-m-d_H-i-s');
        $slug = appFilenameSlug();
        $sqlPath = $this->temporaryPath($slug . '-database-', '.sql');
        $zipPath = $this->temporaryPath($slug . '-full-backup-', '.zip');
        $backup = new DatabaseBackup();

        try {
            $backup->createSqlDump($sqlPath);
            $backup->createFullArchive($zipPath, $sqlPath);
        } finally {
            if (is_file($sqlPath)) {
                unlink($sqlPath);
            }
        }

        ActivityLogger::log('downloaded a full system backup');
        $this->download($zipPath, $slug . '-full-backup-' . $timestamp . '.zip', 'application/zip');
    }

    private function temporaryPath(string $prefix, string $extension): string
    {
        return sys_get_temp_dir() . '/' . $prefix . bin2hex(random_bytes(12)) . $extension;
    }

    private function download(string $path, string $filename, string $contentType): never
    {
        if (!is_file($path)) {
            abort(500, 'The backup file could not be created.');
        }

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Backup-Created-At: ' . date(DATE_ATOM));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        readfile($path);
        unlink($path);
        exit;
    }
}
