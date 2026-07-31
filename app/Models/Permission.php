<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use Core\Paginator;

final class Permission
{
    public const PRODUCT_OWNER_ONLY = [
        'sudo',
        'settings.view',
        'settings.update',
        'logs.view',
        'rate-limits.view',
        'rate-limits.update',
        'permissions.view',
        'permissions.create',
        'permissions.update',
        'permissions.delete',
    ];

    private static array $authorizationCache = [];

    public function all(): array
    {
        return Database::connection()->query('SELECT id, name, slug FROM permissions ORDER BY slug')->fetch_all(MYSQLI_ASSOC);
    }

    public function allAssignable(): array
    {
        return Database::connection()
            ->query(
                'SELECT id, name, slug FROM permissions
                 WHERE slug NOT IN (' . self::productOwnerOnlySqlList() . ')
                 ORDER BY slug'
            )
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT id, name, slug FROM permissions WHERE id = ? LIMIT 1');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function forRole(int $roleId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT permissions.id, permissions.name, permissions.slug
             FROM permissions
             INNER JOIN role_permissions
                ON role_permissions.permission_id = permissions.id
             WHERE role_permissions.role_id = ?
             ORDER BY permissions.slug'
        );
        $statement->bind_param('i', $roleId);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function productOwnerOnlyIds(): array
    {
        $placeholders = implode(', ', array_fill(0, count(self::PRODUCT_OWNER_ONLY), '?'));
        $statement = Database::connection()->prepare(
            'SELECT id FROM permissions WHERE slug IN (' . $placeholders . ')'
        );
        $types = str_repeat('s', count(self::PRODUCT_OWNER_ONLY));
        $slugs = self::PRODUCT_OWNER_ONLY;
        $statement->bind_param($types, ...$slugs);
        $statement->execute();
        return array_map('intval', array_column($statement->get_result()->fetch_all(MYSQLI_ASSOC), 'id'));
    }

    public function idBySlug(string $slug): ?int
    {
        $statement = Database::connection()->prepare('SELECT id FROM permissions WHERE slug = ? LIMIT 1');
        $statement->bind_param('s', $slug);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();
        return $row ? (int) $row['id'] : null;
    }

    public static function isProtectedSlug(string $slug): bool
    {
        return in_array($slug, self::PRODUCT_OWNER_ONLY, true);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM permissions WHERE slug = ?' . ($exceptId === null ? '' : ' AND id <> ?') . ' LIMIT 1';
        $statement = Database::connection()->prepare($sql);
        $exceptId === null ? $statement->bind_param('s', $slug) : $statement->bind_param('si', $slug, $exceptId);
        $statement->execute();
        return $statement->get_result()->num_rows > 0;
    }

    public function paginate(string $search, int $page, int $perPage): array
    {
        $like = '%' . $search . '%';
        $where = ' WHERE slug NOT IN (' . self::productOwnerOnlySqlList() . ')';
        if ($search !== '') {
            $where .= ' AND (name LIKE ? OR slug LIKE ?)';
        }
        $count = Database::connection()->prepare('SELECT COUNT(*) total FROM permissions' . $where);
        if ($search !== '') {
            $count->bind_param('ss', $like, $like);
        }
        $count->execute();
        $total = (int) $count->get_result()->fetch_assoc()['total'];
        $offset = Paginator::offset($total, $page, $perPage);
        $query = Database::connection()->prepare('SELECT id, name, slug FROM permissions' . $where . ' ORDER BY id DESC LIMIT ? OFFSET ?');
        $search === ''
            ? $query->bind_param('ii', $perPage, $offset)
            : $query->bind_param('ssii', $like, $like, $perPage, $offset);
        $query->execute();
        return Paginator::result($query->get_result()->fetch_all(MYSQLI_ASSOC), $total, $page, $perPage, ['search' => $search]);
    }

    private static function productOwnerOnlySqlList(): string
    {
        return "'" . implode("', '", self::PRODUCT_OWNER_ONLY) . "'";
    }

    public function create(string $name, string $slug): void
    {
        $statement = Database::connection()->prepare('INSERT INTO permissions (name, slug) VALUES (?, ?)');
        $statement->bind_param('ss', $name, $slug);
        $statement->execute();
    }

    public function update(int $id, string $name, string $slug): void
    {
        $statement = Database::connection()->prepare('UPDATE permissions SET name = ?, slug = ? WHERE id = ?');
        $statement->bind_param('ssi', $name, $slug, $id);
        $statement->execute();
    }

    public function delete(int $id): bool
    {
        $statement = Database::connection()->prepare('DELETE FROM permissions WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->affected_rows > 0;
    }

    public function userHasPermission(int $userId, string $permission): bool
    {
        $key = $userId . ':' . $permission;
        if (array_key_exists($key, self::$authorizationCache)) {
            return self::$authorizationCache[$key];
        }
        $sudo = 'sudo';
        $statement = Database::connection()->prepare(
            'SELECT 1 FROM users
             INNER JOIN role_permissions ON role_permissions.role_id = users.role_id
             INNER JOIN permissions ON permissions.id = role_permissions.permission_id
             WHERE users.id = ? AND permissions.slug IN (?, ?) LIMIT 1'
        );
        $statement->bind_param('iss', $userId, $permission, $sudo);
        $statement->execute();
        return self::$authorizationCache[$key] = $statement->get_result()->num_rows > 0;
    }
}
