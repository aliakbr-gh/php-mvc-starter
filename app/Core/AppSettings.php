<?php

declare(strict_types=1);

namespace App\Core;

final class AppSettings
{
    public const NAVIGATION_SIDEBAR = 'sidebar';
    public const NAVIGATION_HEADER = 'header';

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
}
