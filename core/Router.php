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

    public function put(string $path, array $action, array $middleware = []): void
    {
        $this->add('PUT', $path, $action, $middleware);
    }

    public function patch(string $path, array $action, array $middleware = []): void
    {
        $this->add('PATCH', $path, $action, $middleware);
    }

    public function delete(string $path, array $action, array $middleware = []): void
    {
        $this->add('DELETE', $path, $action, $middleware);
    }

    private function add(string $method, string $path, array $action, array $middleware): void
    {
        $path = $this->normalize($path);
        [$pattern, $parameters] = $this->compile($path);
        $this->routes[$method][] = compact('path', 'pattern', 'parameters', 'action', 'middleware');
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = $this->normalize($path);
        $route = null;
        $routeParameters = [];

        foreach ($this->routes[$method] ?? [] as $candidate) {
            if (!preg_match($candidate['pattern'], $path, $matches)) {
                continue;
            }

            $route = $candidate;
            foreach ($candidate['parameters'] as $parameter) {
                $routeParameters[$parameter] = rawurldecode((string) ($matches[$parameter] ?? ''));
            }
            break;
        }

        if ($route === null) {
            foreach ($this->routes as $routes) {
                foreach ($routes as $candidate) {
                    if (preg_match($candidate['pattern'], $path)) {
                        abort(405, 'Method not allowed.');
                    }
                }
            }

            abort(404, 'Page not found.');
        }

        if ($method === 'POST' && !API\APIContext::isApiRequest()) {
            verify_csrf();
        }

        API\Request::capture()->setRouteParameters($routeParameters);

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
            'api-auth' => API\APIAuthenticator::authenticate(),
            'auth' => require_auth(),
            'guest' => require_guest(),
            'permission' => require_permission((string) $parameter),
            'role' => require_role((string) $parameter),
            default => abort(500, 'Unknown route middleware: ' . $name),
        };
    }

    private function compile(string $path): array
    {
        $parameters = [];
        $quoted = preg_quote($path, '#');
        $pattern = preg_replace_callback(
            '/\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}/',
            static function (array $matches) use (&$parameters): string {
                $parameters[] = $matches[1];
                return '(?P<' . $matches[1] . '>[^/]+)';
            },
            $quoted
        );

        return ['#^' . $pattern . '$#', $parameters];
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
