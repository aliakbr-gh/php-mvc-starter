<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Application
{
    private Router $router;

    public function __construct(private readonly array $config)
    {
        $this->router = new Router();
        View::setBasePath(BASE_PATH . '/app/Views');
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function config(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->config : ($this->config[$key] ?? $default);
    }

    public function run(Request $request): void
    {
        try {
            $limit = (new RateLimiter($this->config['rate_limiter']))->attempt($request->ip());
            if (!$limit['allowed']) {
                Logger::warning('Rate limit exceeded', ['ip' => $request->ip(), 'retry_after' => $limit['retry_after']]);
                (new Response(
                    View::render('errors/429', ['title' => 'Too many requests', 'retryAfter' => $limit['retry_after']]),
                    429,
                    ['Content-Type' => 'text/html; charset=UTF-8', 'Retry-After' => (string) $limit['retry_after']]
                ))->send();
                return;
            }

            Logger::info('Request', ['method' => $request->method(), 'path' => $request->path(), 'ip' => $request->ip()]);
            $response = $this->router->dispatch($request);
        } catch (Throwable $exception) {
            Logger::error('Unhandled exception', ['exception' => (string) $exception]);
            if ($this->config('debug', false)) {
                $response = Response::html(
                    '<h1>Application error</h1><pre>' . htmlspecialchars((string) $exception) . '</pre>',
                    500
                );
            } else {
                $response = Response::html(View::render('errors/500'), 500);
            }
        }

        $response->send();
    }
}
