<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Router
{
    /** @var array<string, list<array{path: string, handler: callable|array, middleware: array}>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    public function add(string $method, string $path, callable|array $handler, array $middleware = []): self
    {
        $path = '/' . trim($path, '/');
        $this->routes[strtoupper($method)][] = [
            'path' => $path === '//' ? '/' : $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        return $this;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes[$request->method()] ?? [] as $route) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['path']);
            if (!preg_match('#^' . $pattern . '/?$#', $request->path(), $matches)) {
                continue;
            }

            $guard = $this->guard($route['middleware'], $request);
            if ($guard !== null) return $guard;

            $parameters = array_map('urldecode', array_filter(
                $matches,
                static fn (string|int $key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY
            ));

            $result = $this->call($route['handler'], array_values($parameters));
            return $result instanceof Response ? $result : Response::html((string) $result);
        }

        return Response::html(View::render('errors/404'), 404);
    }

    private function guard(array $middleware, Request $request): ?Response
    {
        if ($request->method() === 'POST' && !hash_equals($_SESSION['_token'] ?? '', (string) $request->input('_token', ''))) {
            return Response::html(View::render('errors/419'), 419);
        }
        foreach ($middleware as $rule) {
            if ($rule === 'auth' && Auth::guest()) {
                flash('warning', 'Please log in to continue.');
                return Response::redirect(url('login'));
            }
            if ($rule === 'guest' && Auth::check()) return Response::redirect(url('dashboard'));
            if (str_starts_with($rule, 'role:')) {
                $roles = explode(',', substr($rule, 5));
                if (!Auth::check()) return Response::redirect(url('login'));
                if (!Auth::hasRole(...$roles)) return Response::html(View::render('errors/403'), 403);
            }
            if (str_starts_with($rule, 'permission:')) {
                if (!Auth::check()) return Response::redirect(url('login'));
                if (!Auth::can(substr($rule, 11))) return Response::html(View::render('errors/403'), 403);
            }
        }
        return null;
    }

    private function call(callable|array $handler, array $parameters): mixed
    {
        if (is_array($handler) && is_string($handler[0])) {
            $controller = new $handler[0]();
            if (!method_exists($controller, $handler[1])) {
                throw new RuntimeException('Controller action does not exist.');
            }
            return $controller->{$handler[1]}(...$parameters);
        }

        return $handler(...$parameters);
    }
}
