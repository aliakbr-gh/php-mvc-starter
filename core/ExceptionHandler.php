<?php

declare(strict_types=1);

namespace Core;

use ErrorException;
use Throwable;

final class ExceptionHandler
{
    public static function register(): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ALL);
        set_error_handler(
            static function (
                int $severity,
                string $message,
                string $file,
                int $line
            ): bool {
                if (!(error_reporting() & $severity)) {
                    return false;
                }

                throw new ErrorException($message, 0, $severity, $file, $line);
            }
        );
        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $exception): never
    {
        self::writeLog($exception);

        if ($exception instanceof DatabaseConnectionException) {
            $message = 'The database is temporarily unavailable. Please try again later.';
        } elseif (self::debugEnabled()) {
            $message = sprintf(
                '%s: %s in %s on line %d',
                $exception::class,
                $exception->getMessage(),
                self::relativePath($exception->getFile()),
                $exception->getLine()
            );
        } else {
            $message = 'Something went wrong while processing your request. Please try again.';
        }

        abort(500, $message);
    }

    private static function debugEnabled(): bool
    {
        return (bool) ($GLOBALS['config']['debug'] ?? false);
    }

    private static function relativePath(string $file): string
    {
        $root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        return str_starts_with($file, $root)
            ? substr($file, strlen($root))
            : basename($file);
    }

    private static function writeLog(Throwable $exception): void
    {
        try {
            $directory = dirname(__DIR__) . '/storage/logs';

            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                return;
            }

            $entry = [
                'timestamp' => date(DATE_ATOM),
                'method' => $_SERVER['REQUEST_METHOD'] ?? PHP_SAPI,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];

            file_put_contents(
                $directory . '/errors-' . date('Y-m-d') . '.log',
                json_encode(
                    $entry,
                    JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
                ) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        } catch (Throwable) {
            error_log(
                $exception::class . ': ' . $exception->getMessage()
                . ' in ' . $exception->getFile() . ':' . $exception->getLine()
            );
        }
    }
}
