<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Role extends Model
{
    public function all(): array { return $this->db()->query('SELECT id, name, slug FROM roles ORDER BY name')->fetchAll(); }
    public function find(int $id): ?array { $s = $this->db()->prepare('SELECT id, name, slug FROM roles WHERE id=:id'); $s->execute(['id'=>$id]); return $s->fetch() ?: null; }
    public function permissionIds(int $id): array { $s=$this->db()->prepare('SELECT permission_id FROM role_permissions WHERE role_id=:id'); $s->execute(['id'=>$id]); return array_map('intval', $s->fetchAll(\PDO::FETCH_COLUMN)); }
    public function paginate(string $search, int $page, int $perPage): array
    {
        $where=$search===''?'':' WHERE r.name LIKE :search_name OR r.slug LIKE :search_slug';
        $c=$this->db()->prepare('SELECT COUNT(*) FROM roles r'.$where); if($search!==''){ $c->bindValue(':search_name','%'.$search.'%');$c->bindValue(':search_slug','%'.$search.'%'); } $c->execute();
        $q=$this->db()->prepare('SELECT r.id,r.name,r.slug,COUNT(DISTINCT rp.permission_id) permission_count,COUNT(DISTINCT u.id) user_count FROM roles r LEFT JOIN role_permissions rp ON rp.role_id=r.id LEFT JOIN users u ON u.role_id=r.id'.$where.' GROUP BY r.id ORDER BY r.id DESC LIMIT :limit OFFSET :offset');
        if($search!==''){ $q->bindValue(':search_name','%'.$search.'%');$q->bindValue(':search_slug','%'.$search.'%'); } $q->bindValue(':limit',$perPage,\PDO::PARAM_INT); $q->bindValue(':offset',($page-1)*$perPage,\PDO::PARAM_INT); $q->execute();
        return ['items'=>$q->fetchAll(),'total'=>(int)$c->fetchColumn()];
    }
    public function create(string $name,string $slug,array $permissions): int { $this->db()->beginTransaction();try{$this->db()->prepare('INSERT INTO roles(name,slug) VALUES(:name,:slug)')->execute(compact('name','slug'));$id=(int)$this->db()->lastInsertId();$this->syncPermissions($id,$permissions);$this->db()->commit();return $id;}catch(\Throwable $e){$this->db()->rollBack();throw $e;} }
    public function update(int $id,string $name,string $slug,array $permissions): void { $this->db()->beginTransaction();try{$this->db()->prepare('UPDATE roles SET name=:name,slug=:slug WHERE id=:id')->execute(compact('id','name','slug'));$this->syncPermissions($id,$permissions);$this->db()->commit();}catch(\Throwable $e){$this->db()->rollBack();throw $e;} }
    public function delete(int $id): void { $this->db()->prepare('DELETE FROM roles WHERE id=:id')->execute(['id'=>$id]); }
    private function syncPermissions(int $id,array $permissions): void { $this->db()->prepare('DELETE FROM role_permissions WHERE role_id=:id')->execute(['id'=>$id]);$s=$this->db()->prepare('INSERT INTO role_permissions(role_id,permission_id) VALUES(:role,:permission)');foreach(array_unique(array_map('intval',$permissions)) as $permission)$s->execute(['role'=>$id,'permission'=>$permission]); }
}
