<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Permission extends Model
{
    public function all(): array { return $this->db()->query('SELECT id,name,slug FROM permissions ORDER BY name')->fetchAll(); }
    public function find(int $id): ?array { $s=$this->db()->prepare('SELECT id,name,slug FROM permissions WHERE id=:id');$s->execute(['id'=>$id]);return $s->fetch()?:null; }
    public function paginate(string $search,int $page,int $perPage): array { $w=$search===''?'':' WHERE p.name LIKE :search_name OR p.slug LIKE :search_slug';$c=$this->db()->prepare('SELECT COUNT(*) FROM permissions p'.$w);if($search!==''){$c->bindValue(':search_name','%'.$search.'%');$c->bindValue(':search_slug','%'.$search.'%');}$c->execute();$q=$this->db()->prepare('SELECT p.id,p.name,p.slug,p.created_at,COUNT(rp.role_id) role_count FROM permissions p LEFT JOIN role_permissions rp ON rp.permission_id=p.id'.$w.' GROUP BY p.id ORDER BY p.id DESC LIMIT :limit OFFSET :offset');if($search!==''){$q->bindValue(':search_name','%'.$search.'%');$q->bindValue(':search_slug','%'.$search.'%');}$q->bindValue(':limit',$perPage,\PDO::PARAM_INT);$q->bindValue(':offset',($page-1)*$perPage,\PDO::PARAM_INT);$q->execute();return ['items'=>$q->fetchAll(),'total'=>(int)$c->fetchColumn()]; }
    public function create(string $name,string $slug): int { $this->db()->prepare('INSERT INTO permissions(name,slug) VALUES(:name,:slug)')->execute(compact('name','slug'));return(int)$this->db()->lastInsertId(); }
    public function update(int $id,string $name,string $slug): void { $this->db()->prepare('UPDATE permissions SET name=:name,slug=:slug WHERE id=:id')->execute(compact('id','name','slug')); }
    public function delete(int $id): void { $this->db()->prepare('DELETE FROM permissions WHERE id=:id')->execute(['id'=>$id]); }
}
