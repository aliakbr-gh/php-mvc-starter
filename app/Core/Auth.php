<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $user = null;
    private static ?array $permissions = null;

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        return self::$user ??= (new User())->findById((int)$_SESSION['user_id']);
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = (new User())->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) return false;
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        self::$user = $user;
        self::$permissions = null;
        return true;
    }

    public static function login(int $id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        self::$user = null;
        self::$permissions = null;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        self::$user = null;
        self::$permissions = null;
        session_regenerate_id(true);
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role_slug'], $roles, true);
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if ($user === null) return false;
        self::$permissions ??= (new User())->permissions((int)$user['id']);
        return in_array($permission, self::$permissions, true);
    }
}
