<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\ActivityLogger;
use Core\AppSettings;
use Core\Controller;
use Core\FileUploader;
use RuntimeException;

final class AppSettingController extends Controller
{
    public function index(): void
    {
        $this->view('app-settings/index', [
            'title' => 'Application Settings',
            'settings' => (new AppSettings())->get(),
        ]);
    }

    public function update(): void
    {
        $appName = trim((string) ($_POST['app_name'] ?? ''));

        if (strlen($appName) < 2 || strlen($appName) > 100) {
            flash('error', 'App name must be between 2 and 100 characters.');
            $this->redirect('app-settings');
        }

        $store = new AppSettings();
        $settings = $store->get();
        $uploader = new FileUploader();
        $newLogo = null;
        $newFavicon = null;

        try {
            if (($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $newLogo = $uploader->upload(
                    $_FILES['logo'],
                    'app-settings',
                    ['image/jpeg', 'image/png', 'image/webp']
                );
            }

            if (($_FILES['favicon']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $newFavicon = $uploader->upload(
                    $_FILES['favicon'],
                    'app-settings',
                    ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'],
                    524_288
                );
            }
        } catch (RuntimeException $exception) {
            $uploader->delete($newLogo);
            $uploader->delete($newFavicon);
            flash('error', $exception->getMessage());
            $this->redirect('app-settings');
        }

        if ($newLogo !== null) {
            $uploader->delete($settings['logo']);
            $settings['logo'] = $newLogo;
        }

        if ($newFavicon !== null) {
            $uploader->delete($settings['favicon']);
            $settings['favicon'] = $newFavicon;
        }

        $settings['app_name'] = $appName;
        $store->save($settings);
        ActivityLogger::log('updated application settings');
        flash('success', 'Application settings updated successfully.');
        $this->redirect('app-settings');
    }
}
