<?php

declare(strict_types=1);

use App\Core\Config;

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('app_name')) {
    function app_name(): string
    {
        return (string) config('name', 'Core MVC');
    }
}

if (!function_exists('page_title')) {
    function page_title(?string $page = null): string
    {
        $name = app_name();

        return $page === null || $page === '' || $page === $name
            ? $name
            : $page . ' - ' . $name;
    }
}

if (!function_exists('logo_url')) {
    function logo_url(): ?string
    {
        $path = config('branding.logo_path');

        return is_string($path) && $path !== '' ? url($path) : null;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $publicPosition = strpos($script, '/public/index.php');
        $base = $publicPosition === false ? rtrim(dirname($script), '/') : substr($script, 0, $publicPosition);

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$values): never
    {
        http_response_code(500);
        echo '<pre style="background:#111;color:#9ff7be;padding:20px;white-space:pre-wrap">';
        foreach ($values as $value) {
            var_dump($value);
        }
        echo '</pre>';
        exit(1);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = compact('type', 'message');
    }
}
