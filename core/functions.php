<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $config = $GLOBALS['config'];
    return rtrim($config['base_url'], '/') . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $assetUrl = url('assets/' . $path);
    $assetFile = dirname(__DIR__) . '/public/assets/' . $path;

    return is_file($assetFile)
        ? $assetUrl . '?v=' . filemtime($assetFile)
        : $assetUrl;
}

function client_ip(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '0.0.0.0';
}

function appSettings(): array
{
    return $GLOBALS['app_settings'] ?? [
        'app_name' => (string) ($GLOBALS['config']['name'] ?? 'PHP MVC Starter'),
        'logo' => '',
        'favicon' => '',
    ];
}

function appFilenameSlug(): string
{
    $name = trim((string) (appSettings()['app_name'] ?? ''));
    $ascii = function_exists('iconv')
        ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name)
        : $name;
    $slug = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', (string) $ascii));
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : (string) ($GLOBALS['config']['slug'] ?? 'application');
}

function redirect(string $path = ''): never
{
    header('Location: ' . url($path));
    exit;
}

function abort(int $status, string $message): never
{
    http_response_code($status);
    $errorView = dirname(__DIR__) . '/app/Views/errors/' . $status . '.php';

    if (!is_file($errorView)) {
        $errorView = dirname(__DIR__) . '/app/Views/errors/500.php';
    }

    require $errorView;
    exit;
}

function dd(mixed ...$values): never
{
    if (PHP_SAPI === 'cli') {
        foreach ($values as $value) {
            var_dump($value);
        }
        exit(1);
    }

    http_response_code(500);
    echo '<pre style="margin:0;padding:20px;min-height:100vh;color:#f8fafc;background:#0f172a;'
        . 'font:14px/1.5 ui-monospace,SFMono-Regular,Menlo,monospace;white-space:pre-wrap">';

    foreach ($values as $value) {
        ob_start();
        var_dump($value);
        echo e((string) ob_get_clean());
    }

    echo '</pre>';
    exit;
}

function hashPassword(string $password): string
{
    return password_hash(
        $password,
        PASSWORD_BCRYPT,
        ['cost' => (int) $GLOBALS['config']['bcrypt_cost']]
    );
}

function passwordNeedsRehash(string $hash): bool
{
    return password_needs_rehash(
        $hash,
        PASSWORD_BCRYPT,
        ['cost' => (int) $GLOBALS['config']['bcrypt_cost']]
    );
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function old(string $key): string
{
    return e($_SESSION['_old'][$key] ?? '');
}

function csrf_token(): string
{
    return $_SESSION['_token'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        abort(419, 'Page expired. Please go back and try again.');
    }
}

function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth(): bool
{
    return user() !== null;
}

function end_authenticated_session(string $message, string $flashType = 'error'): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
    flash($flashType, $message);
}

function enforce_session_security(): void
{
    $account = user();

    if ($account === null) {
        return;
    }

    $now = time();
    $lifetime = max(60, (int) ($GLOBALS['config']['session_lifetime'] ?? 1800));
    $lastActivityAt = (int) ($account['last_activity_at'] ?? 0);

    if ($lastActivityAt === 0 || $now - $lastActivityAt >= $lifetime) {
        end_authenticated_session('Your session expired. Please log in again.');
        return;
    }

    $current = (new \App\Models\User())->findAuthenticationState((int) $account['id']);

    if ($current === null || !(bool) $current['is_active']) {
        end_authenticated_session('Your account is inactive. Contact an administrator.');
        return;
    }

    if ((int) ($account['session_version'] ?? 0) !== (int) $current['session_version']) {
        end_authenticated_session('Your password changed. Please log in again.');
        return;
    }

    $_SESSION['user']['last_activity_at'] = $now;
}

function require_auth(): void
{
    if (!auth()) {
        flash('error', 'Please log in first.');
        redirect('login');
    }
}

function require_guest(): void
{
    if (auth()) {
        redirect('dashboard');
    }
}

function hasPermission(string $permission): bool
{
    $account = user();

    if ($account === null) {
        return false;
    }

    return (new \App\Models\Permission())->userHasPermission((int) $account['id'], $permission);
}

function require_permission(string $permission): void
{
    if (!hasPermission($permission)) {
        abort(403, 'You do not have permission to perform this action.');
    }
}

function hasRole(string ...$roleSlugs): bool
{
    $account = user();

    return $account !== null
        && (new \App\Models\User())->hasRole((int) $account['id'], $roleSlugs);
}

function require_role(string $roles): void
{
    $roleSlugs = array_values(array_filter(array_map('trim', explode(',', $roles))));

    if (!hasRole(...$roleSlugs)) {
        abort(403, 'You do not have permission to perform this action.');
    }
}
