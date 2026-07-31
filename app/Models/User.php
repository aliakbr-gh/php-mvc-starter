<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use Core\Paginator;

final class User
{
    public function hasRole(int $userId, array $roleSlugs): bool
    {
        if ($roleSlugs === []) {
            return false;
        }

        $placeholders = implode(', ', array_fill(0, count($roleSlugs), '?'));
        $statement = Database::connection()->prepare(
            'SELECT 1 FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = ? AND roles.slug IN (' . $placeholders . ') LIMIT 1'
        );
        $types = 'i' . str_repeat('s', count($roleSlugs));
        $parameters = [$userId, ...$roleSlugs];
        $statement->bind_param($types, ...$parameters);
        $statement->execute();
        return $statement->get_result()->num_rows > 0;
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT users.id, users.name, users.username, users.role_id,
                    users.is_active, users.session_version,
                    users.created_at, users.updated_at,
                    roles.name AS role_name, roles.slug AS role_slug
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = ? LIMIT 1'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, name, username, password, role_id, is_active, session_version
             FROM users WHERE username = ? LIMIT 1'
        );
        $statement->bind_param('s', $username);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function findAuthenticationState(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT is_active, session_version FROM users WHERE id = ? LIMIT 1'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function usernameExists(string $username, ?int $exceptId = null): bool
    {
        if ($exceptId === null) {
            $statement = Database::connection()->prepare(
                'SELECT id FROM users WHERE username = ? LIMIT 1'
            );
            $statement->bind_param('s', $username);
        } else {
            $statement = Database::connection()->prepare(
                'SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1'
            );
            $statement->bind_param('si', $username, $exceptId);
        }

        $statement->execute();
        return $statement->get_result()->num_rows > 0;
    }

    public function roleHasUser(int $roleId, ?int $exceptUserId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE role_id = ?'
            . ($exceptUserId === null ? '' : ' AND id <> ?')
            . ' LIMIT 1';
        $statement = Database::connection()->prepare($sql);

        if ($exceptUserId === null) {
            $statement->bind_param('i', $roleId);
        } else {
            $statement->bind_param('ii', $roleId, $exceptUserId);
        }

        $statement->execute();
        return $statement->get_result()->num_rows > 0;
    }

    public function paginate(string $search, int $page, int $perPage): array
    {
        $where = $search === ''
            ? ''
            : ' WHERE users.name LIKE ? OR users.username LIKE ?';
        $parameters = $search === '' ? [] : ['%' . $search . '%', '%' . $search . '%'];
        $db = Database::connection();
        $count = $db->prepare('SELECT COUNT(*) AS total FROM users' . $where);

        if ($parameters !== []) {
            $count->bind_param('ss', ...$parameters);
        }

        $count->execute();
        $total = (int) $count->get_result()->fetch_assoc()['total'];
        $offset = Paginator::offset($total, $page, $perPage);
        $query = $db->prepare(
            'SELECT users.id, users.name, users.username, users.is_active,
                    users.created_at, users.updated_at,
                    roles.name AS role_name, roles.slug AS role_slug
             FROM users
             INNER JOIN roles ON roles.id = users.role_id'
            . $where . ' ORDER BY users.id DESC LIMIT ? OFFSET ?'
        );

        if ($parameters === []) {
            $query->bind_param('ii', $perPage, $offset);
        } else {
            $query->bind_param('ssii', $parameters[0], $parameters[1], $perPage, $offset);
        }

        $query->execute();

        return Paginator::result(
            $query->get_result()->fetch_all(MYSQLI_ASSOC),
            $total,
            $page,
            $perPage,
            ['search' => $search]
        );
    }

    public function create(array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO users (name, username, password, role_id, is_active) VALUES (?, ?, ?, ?, ?)'
        );
        $statement->bind_param(
            'sssii',
            $data['name'],
            $data['username'],
            $data['password'],
            $data['role_id'],
            $data['is_active']
        );
        $statement->execute();
    }

    public function update(int $id, array $data): void
    {
        if (isset($data['password'])) {
            $statement = Database::connection()->prepare(
                'UPDATE users SET name = ?, username = ?, role_id = ?, is_active = ?,
                    password = ?, session_version = session_version + 1 WHERE id = ?'
            );
            $statement->bind_param(
                'ssiisi',
                $data['name'],
                $data['username'],
                $data['role_id'],
                $data['is_active'],
                $data['password'],
                $id
            );
        } else {
            $statement = Database::connection()->prepare(
                'UPDATE users SET name = ?, username = ?, role_id = ?, is_active = ? WHERE id = ?'
            );
            $statement->bind_param(
                'ssiii',
                $data['name'],
                $data['username'],
                $data['role_id'],
                $data['is_active'],
                $id
            );
        }

        $statement->execute();
    }

    public function updatePassword(int $id, string $password): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE users SET password = ?, session_version = session_version + 1 WHERE id = ?'
        );
        $statement->bind_param('si', $password, $id);
        $statement->execute();
    }

    public function delete(int $id): bool
    {
        $statement = Database::connection()->prepare('DELETE FROM users WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->affected_rows > 0;
    }
}
