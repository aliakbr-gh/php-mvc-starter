<?php

declare(strict_types=1);

namespace Core\API;

final class Response
{
    public static function json(bool $success, string $message, mixed $data = [], int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $normalizedData = $success
            ? ($data === null || $data === [] ? (object) [] : $data)
            : null;
        $json = json_encode(
            ['success' => $success, 'message' => $message, 'data' => $normalizedData],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            http_response_code(500);
            $json = '{"success":false,"message":"Unable to encode the response.","data":null}';
        }

        echo $json;
        exit;
    }

    public static function success(mixed $data = [], string $message = 'Request completed successfully.', int $status = 200): never
    {
        self::json(true, $message, $data, $status);
    }

    public static function created(mixed $data = [], string $message = 'Resource created successfully.'): never
    {
        self::success($data, $message, 201);
    }

    public static function error(string $message, int $status = 400): never
    {
        self::json(false, $message, null, $status);
    }

    public static function validation(array $errors, string $message = 'The submitted data is invalid.'): never
    {
        self::error($message, 422);
    }

    public static function unauthorized(string $message = 'Invalid or expired access token.'): never
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Access to this resource is forbidden.'): never
    {
        self::error($message, 403);
    }

    public static function notFound(string $message = 'Resource not found.'): never
    {
        self::error($message, 404);
    }
}
