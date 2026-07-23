<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ActivityLogger;
use App\Core\Controller;
use App\Core\HttpClient;
use App\Core\Response;
use App\Core\Request;
use App\Models\Activity;

final class DashboardController extends Controller
{
    public function index(): Response
    {
        // $response = (new HttpClient())->get('https://jsonplaceholder.typicode.com/todos');
        // dd($response->json());

        $user = Auth::user();
        ActivityLogger::log($user['name'] . ' viewed the dashboard from ' . Request::capture()->ip(), (int) $user['id']);
        $activities = (new Activity())->recent(Auth::hasRole('admin') ? null : (int) $user['id']);

        return $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'activities' => $activities,
        ], 'layouts/dashboard');
    }

    public function admin(): Response
    {
        $user = Auth::user();
        ActivityLogger::log($user['name'] . ' viewed the admin area from ' . Request::capture()->ip(), (int) $user['id']);
        return $this->view('dashboard/admin', ['title' => 'Admin', 'user' => $user], 'layouts/dashboard');
    }
}
