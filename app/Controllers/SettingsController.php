<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AppSettings;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\FileUploader;
use App\Core\Request;
use App\Core\Response;
use RuntimeException;

final class SettingsController extends Controller
{
    public function index(): Response
    {
        return $this->view('settings/index', [
            'title' => 'App settings',
            'user' => Auth::user(),
            'navigationStyle' => AppSettings::navigationStyle(),
            'appName' => AppSettings::appName(),
            'logoUrl' => logo_url(),
            'faviconUrl' => favicon_url(),
        ]);
    }

    public function update(): Response
    {
        $request = Request::capture();
        $style = strtolower(trim((string) $request->input('navigation')));
        $appName = trim((string) $request->input('app_name'));

        if (!in_array($style, AppSettings::navigationStyles(), true)) {
            flash('error', 'Choose a valid navigation style.');
            return Response::redirect(url('settings'));
        }

        if (strlen($appName) < 2 || strlen($appName) > 80) {
            flash('error', 'The app name must contain between 2 and 80 characters.');
            return Response::redirect(url('settings'));
        }

        $uploader = new FileUploader(
            BASE_PATH . '/public/uploads/branding',
            'uploads/branding',
        );

        try {
            $logoPath = $uploader->upload($request->file('logo') ?? [], [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
            ], 2097152) ?? AppSettings::logoPath();

            $faviconPath = $uploader->upload($request->file('favicon') ?? [], [
                'image/png' => 'png',
                'image/x-icon' => 'ico',
                'image/vnd.microsoft.icon' => 'ico',
                'image/webp' => 'webp',
            ], 1048576) ?? AppSettings::faviconPath();

            AppSettings::updateBranding($appName, $logoPath, $faviconPath);
            AppSettings::setNavigationStyle($style);
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            return Response::redirect(url('settings'));
        }

        flash('success', 'App settings updated successfully.');

        return Response::redirect(url('settings'));
    }
}
