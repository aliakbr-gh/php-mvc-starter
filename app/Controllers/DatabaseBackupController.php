<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\ActivityLogger;
use Core\Controller;
use Core\DatabaseBackup;
use Core\GoogleDriveService;
use Core\GoogleDriveSettings;
use Throwable;

final class DatabaseBackupController extends Controller
{
    public function index(): void
    {
        $googleSettings = (new GoogleDriveSettings())->get();
        $this->view('database-backup/index', [
            'title' => 'Database Backup',
            'googleDrive' => [
                'configured' => ($googleSettings['client_id'] ?? '') !== ''
                    && ($googleSettings['client_secret'] ?? '') !== '',
                'connected' => ($googleSettings['refresh_token'] ?? '') !== '',
                'client_id' => (string) ($googleSettings['client_id'] ?? ''),
                'client_secret_saved' => ($googleSettings['client_secret'] ?? '') !== '',
                'folder_id' => (string) ($googleSettings['folder_id'] ?? ''),
                'redirect_uri' => url('database-backup/google/callback'),
            ],
        ]);
    }

    public function saveGoogleSettings(): never
    {
        $clientId = trim((string) ($_POST['client_id'] ?? ''));
        $clientSecret = trim((string) ($_POST['client_secret'] ?? ''));
        $folderId = trim((string) ($_POST['folder_id'] ?? ''));
        $store = new GoogleDriveSettings();
        $current = $store->get();

        if ($clientId === '' || (!($current['client_secret'] ?? '') && $clientSecret === '')) {
            flash('error', 'Google OAuth client ID and client secret are required.');
            $this->redirect('database-backup');
        }
        if (strlen($clientId) > 255 || strlen($clientSecret) > 255) {
            flash('error', 'Google OAuth credentials are too long.');
            $this->redirect('database-backup');
        }
        if ($folderId !== '' && !preg_match('/^[a-zA-Z0-9_-]{10,255}$/', $folderId)) {
            flash('error', 'Enter a valid Google Drive folder ID or leave it blank for My Drive.');
            $this->redirect('database-backup');
        }

        $credentialsChanged = !hash_equals((string) ($current['client_id'] ?? ''), $clientId)
            || ($clientSecret !== ''
                && !hash_equals((string) ($current['client_secret'] ?? ''), $clientSecret));
        $store->save([
            'client_id' => $clientId,
            'client_secret' => $clientSecret !== ''
                ? $clientSecret
                : (string) ($current['client_secret'] ?? ''),
            'folder_id' => $folderId,
            'refresh_token' => $credentialsChanged ? '' : (string) ($current['refresh_token'] ?? ''),
        ]);

        ActivityLogger::log('updated Google Drive backup settings');
        flash('success', $credentialsChanged
            ? 'Google Drive settings saved. Connect the Google account next.'
            : 'Google Drive settings updated.');
        $this->redirect('database-backup');
    }

    public function connectGoogle(): never
    {
        $state = bin2hex(random_bytes(32));
        $_SESSION['google_drive_oauth'] = [
            'state' => $state,
            'created_at' => time(),
        ];

        try {
            $authorizationUrl = (new GoogleDriveService())->authorizationUrl(
                url('database-backup/google/callback'),
                $state
            );
        } catch (Throwable $exception) {
            unset($_SESSION['google_drive_oauth']);
            flash('error', $exception->getMessage());
            $this->redirect('database-backup');
        }

        header('Location: ' . $authorizationUrl);
        exit;
    }

    public function googleCallback(): never
    {
        $oauth = $_SESSION['google_drive_oauth'] ?? [];
        unset($_SESSION['google_drive_oauth']);
        $expectedState = (string) ($oauth['state'] ?? '');
        $state = (string) ($_GET['state'] ?? '');
        $createdAt = (int) ($oauth['created_at'] ?? 0);

        if ($expectedState === '' || $state === '' || !hash_equals($expectedState, $state)
            || $createdAt < time() - 600
        ) {
            flash('error', 'The Google authorization request expired or could not be verified.');
            $this->redirect('database-backup');
        }
        if (isset($_GET['error'])) {
            flash('error', 'Google Drive authorization was cancelled.');
            $this->redirect('database-backup');
        }

        $code = trim((string) ($_GET['code'] ?? ''));
        if ($code === '') {
            flash('error', 'Google did not return an authorization code.');
            $this->redirect('database-backup');
        }

        try {
            (new GoogleDriveService())->connect($code, url('database-backup/google/callback'));
            ActivityLogger::log('connected Google Drive for backups');
            flash('success', 'Google Drive connected successfully.');
        } catch (Throwable $exception) {
            flash('error', 'Google Drive connection failed: ' . $exception->getMessage());
        }

        $this->redirect('database-backup');
    }

    public function disconnectGoogle(): never
    {
        (new GoogleDriveSettings())->save(['refresh_token' => '']);
        ActivityLogger::log('disconnected Google Drive backups');
        flash('success', 'Google Drive disconnected.');
        $this->redirect('database-backup');
    }

    public function uploadGoogle(): never
    {
        $type = strtolower(trim((string) ($_POST['backup_type'] ?? '')));
        if (!in_array($type, ['database', 'uploads', 'full'], true)) {
            flash('error', 'Select a valid backup type.');
            $this->redirect('database-backup');
        }

        $timestamp = date('Y-m-d_H-i-s');
        $slug = appFilenameSlug();
        $paths = [];

        try {
            $backup = new DatabaseBackup();
            if ($type === 'database') {
                $path = $this->temporaryPath($slug . '-database-', '.sql');
                $paths[] = $path;
                $filename = $slug . '-database-' . $timestamp . '.sql';
                $mimeType = 'application/sql';
                $backup->createSqlDump($path);
            } elseif ($type === 'uploads') {
                $path = $this->temporaryPath($slug . '-uploads-', '.zip');
                $paths[] = $path;
                $filename = $slug . '-uploads-' . $timestamp . '.zip';
                $mimeType = 'application/zip';
                $backup->createUploadsArchive($path);
            } else {
                $sqlPath = $this->temporaryPath($slug . '-database-', '.sql');
                $path = $this->temporaryPath($slug . '-full-backup-', '.zip');
                $paths[] = $sqlPath;
                $paths[] = $path;
                $filename = $slug . '-full-backup-' . $timestamp . '.zip';
                $mimeType = 'application/zip';
                $backup->createSqlDump($sqlPath);
                $backup->createFullArchive($path, $sqlPath);
            }
            (new GoogleDriveService())->upload($path, $filename, $mimeType);
            ActivityLogger::log('uploaded a ' . $type . ' backup to Google Drive');
            flash('success', $filename . ' was saved to Google Drive.');
        } catch (Throwable $exception) {
            flash('error', 'Google Drive backup failed: ' . $exception->getMessage());
        } finally {
            foreach ($paths as $temporaryPath) {
                if (is_file($temporaryPath)) {
                    unlink($temporaryPath);
                }
            }
        }

        $this->redirect('database-backup');
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
