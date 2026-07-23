<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class AppSettings
{
    public const NAVIGATION_SIDEBAR = 'sidebar';
    public const NAVIGATION_HEADER = 'header';

    private static ?array $branding = null;

    public static function navigationStyle(): string
    {
        $userId = Auth::user()['id'] ?? null;

        if ($userId === null) {
            return self::NAVIGATION_SIDEBAR;
        }

        $style = $_SESSION['app_settings'][(int) $userId]['navigation'] ?? self::NAVIGATION_SIDEBAR;

        return in_array($style, self::navigationStyles(), true)
            ? $style
            : self::NAVIGATION_SIDEBAR;
    }

    public static function setNavigationStyle(string $style): bool
    {
        $userId = Auth::user()['id'] ?? null;

        if ($userId === null || !in_array($style, self::navigationStyles(), true)) {
            return false;
        }

        $_SESSION['app_settings'][(int) $userId]['navigation'] = $style;

        return true;
    }

    public static function navigationStyles(): array
    {
        return [self::NAVIGATION_SIDEBAR, self::NAVIGATION_HEADER];
    }

    public static function appName(): string
    {
        return (string) (self::branding()['app_name'] ?? Config::get('name', 'Core MVC'));
    }

    public static function logoPath(): ?string
    {
        $path = self::branding()['logo_path'] ?? Config::get('branding.logo_path');

        return is_string($path) && $path !== '' ? $path : null;
    }

    public static function faviconPath(): ?string
    {
        $path = self::branding()['favicon_path'] ?? Config::get('branding.favicon_path');

        return is_string($path) && $path !== '' ? $path : null;
    }

    public static function updateBranding(string $appName, ?string $logoPath, ?string $faviconPath): void
    {
        self::$branding = [
            'app_name' => $appName,
            'logo_path' => $logoPath,
            'favicon_path' => $faviconPath,
        ];

        $file = self::settingsFile();
        $directory = dirname($file);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create the settings directory.');
        }

        $handle = fopen($file, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Could not open the settings file.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Could not lock the settings file.');
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) json_encode(
                self::$branding,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
            ));
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }

    private static function branding(): array
    {
        if (self::$branding !== null) {
            return self::$branding;
        }

        $file = self::settingsFile();
        if (!is_file($file)) {
            return self::$branding = [];
        }

        $settings = json_decode((string) file_get_contents($file), true);

        return self::$branding = is_array($settings) ? $settings : [];
    }

    private static function settingsFile(): string
    {
        return BASE_PATH . '/storage/cache/app_settings.json';
    }
}
