<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use Core\Paginator;

final class ActivityLog
{
    public function paginate(string $date, int $page, int $perPage): array
    {
        $database = Database::connection();
        $count = $database->prepare(
            'SELECT COUNT(*) AS total
             FROM activity_logs
             WHERE created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)'
        );
        $count->bind_param('ss', $date, $date);
        $count->execute();
        $total = (int) $count->get_result()->fetch_assoc()['total'];
        $offset = Paginator::offset($total, $page, $perPage);
        $query = $database->prepare(
            'SELECT id, user_id, user_name, username, description, ip_address, created_at
             FROM activity_logs
             WHERE created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
             ORDER BY id DESC
             LIMIT ? OFFSET ?'
        );
        $query->bind_param('ssii', $date, $date, $perPage, $offset);
        $query->execute();

        return Paginator::result(
            $query->get_result()->fetch_all(MYSQLI_ASSOC),
            $total,
            $page,
            $perPage,
            ['date' => $date]
        );
    }
}
