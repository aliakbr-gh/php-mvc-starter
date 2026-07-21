<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Activity extends Model
{
    public function create(string $activity, ?int $userId): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO activities (activity, user_id) VALUES (:activity, :user_id)'
        );
        $statement->bindValue(':activity', $activity);
        $statement->bindValue(':user_id', $userId, $userId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $statement->execute();

        return (int) $this->db()->lastInsertId();
    }

    public function recent(?int $userId = null, int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));
        $sql = 'SELECT id, activity, user_id, created_at FROM activities';
        if ($userId !== null) $sql .= ' WHERE user_id = :user_id';
        $sql .= ' ORDER BY id DESC LIMIT ' . $limit;

        $statement = $this->db()->prepare($sql);
        if ($userId !== null) $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
