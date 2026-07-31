<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use Core\Paginator;
use mysqli_sql_exception;

final class Role
{
    public function all(): array
    {
        return Database::connection()->query('SELECT id, name, slug FROM roles ORDER BY name')->fetch_all(MYSQLI_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT id, name, slug FROM roles WHERE id = ? LIMIT 1');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM roles WHERE slug = ?' . ($exceptId === null ? '' : ' AND id <> ?') . ' LIMIT 1';
        $statement = Database::connection()->prepare($sql);
        $exceptId === null ? $statement->bind_param('s', $slug) : $statement->bind_param('si', $slug, $exceptId);
        $statement->execute();
        return $statement->get_result()->num_rows > 0;
    }

    public function paginate(string $search, int $page, int $perPage): array
    {
        $like = '%' . $search . '%';
        $where = $search === '' ? '' : ' WHERE roles.name LIKE ? OR roles.slug LIKE ?';
        $count = Database::connection()->prepare('SELECT COUNT(*) total FROM roles' . $where);
        if ($search !== '') {
            $count->bind_param('ss', $like, $like);
        }
        $count->execute();
        $total = (int) $count->get_result()->fetch_assoc()['total'];
        $offset = Paginator::offset($total, $page, $perPage);
        $query = Database::connection()->prepare(
            'SELECT roles.id, roles.name, roles.slug, COUNT(role_permissions.permission_id) permission_count
             FROM roles LEFT JOIN role_permissions ON role_permissions.role_id = roles.id'
            . $where . ' GROUP BY roles.id ORDER BY roles.id DESC LIMIT ? OFFSET ?'
        );
        $search === ''
            ? $query->bind_param('ii', $perPage, $offset)
            : $query->bind_param('ssii', $like, $like, $perPage, $offset);
        $query->execute();
        return Paginator::result($query->get_result()->fetch_all(MYSQLI_ASSOC), $total, $page, $perPage, ['search' => $search]);
    }

    public function create(string $name, string $slug): void
    {
        $statement = Database::connection()->prepare('INSERT INTO roles (name, slug) VALUES (?, ?)');
        $statement->bind_param('ss', $name, $slug);
        $statement->execute();
    }

    public function update(int $id, string $name, string $slug): void
    {
        $statement = Database::connection()->prepare('UPDATE roles SET name = ?, slug = ? WHERE id = ?');
        $statement->bind_param('ssi', $name, $slug, $id);
        $statement->execute();
    }

    public function delete(int $id): bool
    {
        try {
            $statement = Database::connection()->prepare('DELETE FROM roles WHERE id = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            return $statement->affected_rows > 0;
        } catch (mysqli_sql_exception) {
            return false;
        }
    }

    public function permissionIds(int $roleId): array
    {
        $statement = Database::connection()->prepare('SELECT permission_id FROM role_permissions WHERE role_id = ?');
        $statement->bind_param('i', $roleId);
        $statement->execute();
        return array_map('intval', array_column($statement->get_result()->fetch_all(MYSQLI_ASSOC), 'permission_id'));
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db = Database::connection();
        $db->begin_transaction();
        try {
            $delete = $db->prepare('DELETE FROM role_permissions WHERE role_id = ?');
            $delete->bind_param('i', $roleId);
            $delete->execute();
            $insert = $db->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
            foreach (array_unique($permissionIds) as $permissionId) {
                $insert->bind_param('ii', $roleId, $permissionId);
                $insert->execute();
            }
            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }
}
