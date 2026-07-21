<?php

declare(strict_types=1);

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $publicPosition = strpos($script, '/public/index.php');
        $base = $publicPosition === false ? rtrim(dirname($script), '/') : substr($script, 0, $publicPosition);

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$values): never
    {
        http_response_code(500);
        echo '<pre style="background:#111;color:#9ff7be;padding:20px;white-space:pre-wrap">';
        foreach ($values as $value) var_dump($value);
        echo '</pre>';
        exit(1);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_token'])) $_SESSION['_token'] = bin2hex(random_bytes(32));
        return $_SESSION['_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void { $_SESSION['_flash'][] = compact('type', 'message'); }
}
