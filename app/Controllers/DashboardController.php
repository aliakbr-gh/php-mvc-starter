<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ActivityLogger;
use App\Core\Controller;
use App\Core\Response;
use App\Core\Request;
use App\Models\Activity;

final class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        ActivityLogger::log($user['name'] . ' viewed the dashboard from ' . Request::capture()->ip(), (int) $user['id']);
        $activities = (new Activity())->recent(
            Auth::hasRole('admin') ? null : (int) $user['id'],
            5
        );

        return $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'activities' => $activities,
        ]);
    }
}
