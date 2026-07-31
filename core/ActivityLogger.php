<?php

declare(strict_types=1);

namespace Core;

final class ActivityLogger
{
    public static function log(string $description): void
    {
        $account = user();

        if ($account === null || trim($description) === '') {
            return;
        }

        $userId = (int) $account['id'];
        $userName = (string) $account['name'];
        $username = (string) $account['username'];
        $description = trim($description);
        $ipAddress = client_ip();
        $statement = Database::connection()->prepare(
            'INSERT INTO activity_logs
                (user_id, user_name, username, description, ip_address)
             VALUES (?, ?, ?, ?, ?)'
        );
        $statement->bind_param(
            'issss',
            $userId,
            $userName,
            $username,
            $description,
            $ipAddress
        );
        $statement->execute();
    }
}
