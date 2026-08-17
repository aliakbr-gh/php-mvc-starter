<?php

$appName = 'PHP MVC Starter';
$appSlug = 'php-mvc-starter';
$documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
$projectRoot = realpath(dirname(__DIR__));
$detectedBaseUrl = '';
$environment = getenv('APP_ENV') ?: 'development';

if ($documentRoot !== false && $projectRoot !== false && str_starts_with($projectRoot, $documentRoot)) {
    $detectedBaseUrl = '/' . trim(str_replace(DIRECTORY_SEPARATOR, '/', substr($projectRoot, strlen($documentRoot))), '/');
    $detectedBaseUrl = $detectedBaseUrl === '/' ? '' : $detectedBaseUrl;
}

return [
    'name' => $appName,
    'slug' => $appSlug,
    'base_url' => getenv('APP_URL') ?: $detectedBaseUrl,
    'session_name' => str_replace('-', '_', $appSlug) . '_session',
    'session_lifetime' => max(60, (int) (getenv('APP_SESSION_LIFETIME') ?: 1800)),
    'bcrypt_cost' => 5,
    'request_logging' => true,
    'environment' => $environment,
    'debug' => filter_var(
        getenv('APP_DEBUG') !== false
            ? getenv('APP_DEBUG')
            : ($environment === 'development' ? '1' : '0'),
        FILTER_VALIDATE_BOOL
    ),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Karachi',
    'api' => [
        'issuer' => getenv('APP_API_ISSUER') ?: $appSlug,
        'audience' => getenv('APP_API_AUDIENCE') ?: $appSlug . '-api',
        'jwt_secret' => getenv('APP_JWT_SECRET') ?: 'f7a92c50e6d84bdfa2439185c874a9588e731bf59182572173f897e935baba12',
        'token_lifetime' => max(60, (int) (getenv('APP_API_TOKEN_LIFETIME') ?: 900)),
    ],
];
