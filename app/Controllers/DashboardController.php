<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->view('dashboard', ['title' => 'Dashboard']);
    }
}
