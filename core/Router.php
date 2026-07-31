<?php

declare(strict_types=1);

namespace Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $action, array $middleware = []): void
    {
        $this->add('GET', $path, $action, $middleware);
    }

    public function post(string $path, array $action, array $middleware = []): void
    {
        $this->add('POST', $path, $action, $middleware);
    }

    private function add(string $method, string $path, array $action, array $middleware): void
    {
        $this->routes[$method][$this->normalize($path)] = compact('action', 'middleware');
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = $this->normalize($path);
        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            abort(404, 'Page not found.');
        }

        if ($method === 'POST') {
            verify_csrf();
        }

        foreach ($route['middleware'] as $middleware) {
            $this->runMiddleware($middleware);
        }

        $action = $route['action'];
        [$controller, $methodName] = $action;
        (new $controller())->$methodName();
    }

    private function runMiddleware(string $middleware): void
    {
        [$name, $parameter] = array_pad(explode(':', $middleware, 2), 2, null);

        match ($name) {
            'auth' => require_auth(),
            'guest' => require_guest(),
            'permission' => require_permission((string) $parameter),
            'role' => require_role((string) $parameter),
            default => abort(500, 'Unknown route middleware: ' . $name),
        };
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
