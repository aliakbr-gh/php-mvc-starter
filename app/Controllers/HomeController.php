<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

final class HomeController extends Controller
{
    public function index(): Response
    {
        return Response::redirect(url(Auth::check() ? 'dashboard' : 'login'));
    }

    public function hello(string $name): Response
    {
        return $this->view('home/hello', [
            'title' => 'Hello',
            'name' => $name,
        ]);
    }
}
