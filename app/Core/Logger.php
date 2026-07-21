<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    private static array $config = ['enabled' => false];

    public static function configure(array $config): void { self::$config = $config; }
    public static function info(string $message, array $context = []): void { self::write('INFO', $message, $context); }
    public static function warning(string $message, array $context = []): void { self::write('WARNING', $message, $context); }
    public static function error(string $message, array $context = []): void { self::write('ERROR', $message, $context); }

    private static function write(string $level, string $message, array $context): void
    {
        if (!(self::$config['enabled'] ?? false)) return;

        $path = self::$config['path'];
        if (!is_dir($path)) mkdir($path, 0775, true);
        $date = date(self::$config['date_format'] ?? 'Y-m-d');
        $file = $path . '/' . str_replace('{date}', $date, self::$config['filename'] ?? 'app-{date}.log');
        $context = $context === [] ? '' : ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $line = sprintf("[%s] %s: %s%s%s", date('Y-m-d H:i:s'), $level, $message, $context, PHP_EOL);
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
