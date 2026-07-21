<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOStatement;
use Throwable;

final class Role extends Model
{
    public function all(): array
    {
        return $this->db()->query('SELECT id, name, slug FROM roles ORDER BY name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare('SELECT id, name, slug FROM roles WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function permissionIds(int $id): array
    {
        $statement = $this->db()->prepare(
            'SELECT permission_id FROM role_permissions WHERE role_id = :id'
        );
        $statement->execute(['id' => $id]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public function paginate(string $search, int $page, int $perPage): array
    {
        $where = $search === ''
            ? ''
            : ' WHERE r.name LIKE :search_name OR r.slug LIKE :search_slug';

        $count = $this->db()->prepare('SELECT COUNT(*) FROM roles r' . $where);
        $this->bindSearch($count, $search);
        $count->execute();

        $query = $this->db()->prepare(
            'SELECT r.id, r.name, r.slug,
                    COUNT(DISTINCT rp.permission_id) AS permission_count,
                    COUNT(DISTINCT u.id) AS user_count
             FROM roles r
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             LEFT JOIN users u ON u.role_id = r.id' . $where . '
             GROUP BY r.id
             ORDER BY r.id DESC
             LIMIT :limit OFFSET :offset'
        );
        $this->bindSearch($query, $search);
        $query->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $query->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $query->execute();

        return ['items' => $query->fetchAll(), 'total' => (int) $count->fetchColumn()];
    }

    public function create(string $name, string $slug, array $permissions): int
    {
        $this->db()->beginTransaction();

        try {
            $this->db()
                ->prepare('INSERT INTO roles (name, slug) VALUES (:name, :slug)')
                ->execute(compact('name', 'slug'));
            $id = (int) $this->db()->lastInsertId();
            $this->syncPermissions($id, $permissions);
            $this->db()->commit();

            return $id;
        } catch (Throwable $exception) {
            $this->db()->rollBack();
            throw $exception;
        }
    }

    public function update(int $id, string $name, string $slug, array $permissions): void
    {
        $this->db()->beginTransaction();

        try {
            $this->db()
                ->prepare('UPDATE roles SET name = :name, slug = :slug WHERE id = :id')
                ->execute(compact('id', 'name', 'slug'));
            $this->syncPermissions($id, $permissions);
            $this->db()->commit();
        } catch (Throwable $exception) {
            $this->db()->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $this->db()->prepare('DELETE FROM roles WHERE id = :id')->execute(['id' => $id]);
    }

    private function syncPermissions(int $id, array $permissions): void
    {
        $this->db()
            ->prepare('DELETE FROM role_permissions WHERE role_id = :id')
            ->execute(['id' => $id]);
        $statement = $this->db()->prepare(
            'INSERT INTO role_permissions (role_id, permission_id)
             VALUES (:role, :permission)'
        );

        foreach (array_unique(array_map('intval', $permissions)) as $permission) {
            $statement->execute(['role' => $id, 'permission' => $permission]);
        }
    }

    private function bindSearch(PDOStatement $statement, string $search): void
    {
        if ($search === '') {
            return;
        }

        $statement->bindValue(':search_name', '%' . $search . '%');
        $statement->bindValue(':search_slug', '%' . $search . '%');
    }
}
