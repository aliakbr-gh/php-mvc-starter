<?php

declare(strict_types=1);

namespace Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewFile = dirname(__DIR__) . '/app/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            abort(500, 'View not found.');
        }

        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/app/Views/layouts/app.php';
    }

    protected function redirect(string $path = ''): never
    {
        redirect($path);
    }
}
