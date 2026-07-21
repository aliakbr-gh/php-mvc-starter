<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

final class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->view('home/index', [
            'title' => 'Core MVCC',
            'features' => ['Clean URLs', 'Zero dependencies', 'Simple routing', 'PDO-ready models'],
        ]);
    }

    public function hello(string $name): Response
    {
        return $this->view('home/hello', [
            'title' => 'Hello',
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        ]);
    }
}
