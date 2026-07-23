<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public function findById(int $id): ?array
    {
        $statement = $this->db()->prepare('SELECT u.id, u.name, u.email, u.password, u.role_id, u.created_at, r.name AS role_name, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db()->prepare('SELECT u.id, u.name, u.email, u.password, u.role_id, u.created_at, r.name AS role_name, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = :email LIMIT 1');
        $statement->execute(['email' => strtolower(trim($email))]);
        return $statement->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO users (name, email, password, role_id) SELECT :name, :email, :password, id FROM roles WHERE slug = :role LIMIT 1'
        );
        $statement->execute([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
        ]);
        return (int) $this->db()->lastInsertId();
    }

    public function permissions(int $userId): array
    {
        $statement = $this->db()->prepare('SELECT p.slug FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id JOIN users u ON u.role_id = rp.role_id WHERE u.id = :id');
        $statement->execute(['id' => $userId]);
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function createManaged(string $name, string $email, string $password, int $roleId): int
    {
        $this->db()->prepare('INSERT INTO users(name,email,password,role_id) VALUES(:name,:email,:password,:role_id)')->execute([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $roleId,
        ]);
        return (int) $this->db()->lastInsertId();
    }

    public function paginate(string $search, int $page, int $perPage): array
    {
        $where = $search === '' ? '' : ' WHERE u.name LIKE :search_name OR u.email LIKE :search_email OR r.name LIKE :search_role';
        $count = $this->db()->prepare('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id' . $where);
        if ($search !== '')
            foreach ([':search_name', ':search_email', ':search_role'] as $key)
                $count->bindValue($key, '%' . $search . '%');
        $count->execute();
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $query = $this->db()->prepare('SELECT u.id, u.name, u.email, u.role_id, u.created_at, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id' . $where . ' ORDER BY u.id DESC LIMIT :limit OFFSET :offset');
        if ($search !== '')
            foreach ([':search_name', ':search_email', ':search_role'] as $key)
                $query->bindValue($key, '%' . $search . '%');
        $query->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $query->execute();
        return ['items' => $query->fetchAll(), 'total' => $total];
    }

    public function update(int $id, string $name, string $email, int $roleId, ?string $password = null): void
    {
        $sql = 'UPDATE users SET name = :name, email = :email, role_id = :role_id';
        $values = ['id' => $id, 'name' => trim($name), 'email' => strtolower(trim($email)), 'role_id' => $roleId];
        if ($password !== null && $password !== '') {
            $sql .= ', password = :password';
            $values['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id = :id';
        $this->db()->prepare($sql)->execute($values);
    }

    public function delete(int $id): void
    {
        $this->db()->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
    }
}
