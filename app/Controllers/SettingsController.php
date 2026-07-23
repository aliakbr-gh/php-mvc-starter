<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AppSettings;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class SettingsController extends Controller
{
    public function index(): Response
    {
        return $this->view('settings/index', [
            'title' => 'App settings',
            'user' => Auth::user(),
            'navigationStyle' => AppSettings::navigationStyle(),
        ]);
    }

    public function update(): Response
    {
        $style = strtolower(trim((string) Request::capture()->input('navigation')));

        if (!AppSettings::setNavigationStyle($style)) {
            flash('error', 'Choose a valid navigation style.');
            return Response::redirect(url('settings'));
        }

        flash('success', 'Navigation preference updated.');

        return Response::redirect(url('settings'));
    }
}
