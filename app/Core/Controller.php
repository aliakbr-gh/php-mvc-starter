<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): Response
    {
        return Response::html(View::render($view, $data, $layout));
    }
}
