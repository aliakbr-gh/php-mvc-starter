<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Permission extends Model
{
    public function all(): array
    {
        return $this->db()
            ->query('SELECT id, name, slug FROM permissions ORDER BY name')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, name, slug FROM permissions WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function paginate(string $search, int $page, int $perPage): array
    {
        $where = $search === ''
            ? ''
            : ' WHERE p.name LIKE :search_name OR p.slug LIKE :search_slug';

        $count = $this->db()->prepare('SELECT COUNT(*) FROM permissions p' . $where);
        $this->bindSearch($count, $search);
        $count->execute();

        $query = $this->db()->prepare(
            'SELECT p.id, p.name, p.slug, p.created_at, p.updated_at, COUNT(rp.role_id) AS role_count
             FROM permissions p
             LEFT JOIN role_permissions rp ON rp.permission_id = p.id' . $where . '
             GROUP BY p.id
             ORDER BY p.id DESC
             LIMIT :limit OFFSET :offset'
        );
        $this->bindSearch($query, $search);
        $query->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $query->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $query->execute();

        return ['items' => $query->fetchAll(), 'total' => (int) $count->fetchColumn()];
    }

    public function create(string $name, string $slug): int
    {
        $this->db()
            ->prepare('INSERT INTO permissions (name, slug) VALUES (:name, :slug)')
            ->execute(compact('name', 'slug'));

        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, string $name, string $slug): void
    {
        $this->db()
            ->prepare('UPDATE permissions SET name = :name, slug = :slug WHERE id = :id')
            ->execute(compact('id', 'name', 'slug'));
    }

    public function delete(int $id): void
    {
        $this->db()
            ->prepare('DELETE FROM permissions WHERE id = :id')
            ->execute(['id' => $id]);
    }

    private function bindSearch(\PDOStatement $statement, string $search): void
    {
        if ($search === '') {
            return;
        }

        $statement->bindValue(':search_name', '%' . $search . '%');
        $statement->bindValue(':search_slug', '%' . $search . '%');
    }
}
