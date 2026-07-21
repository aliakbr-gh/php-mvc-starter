<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;use App\Core\Auth;use App\Core\Controller;use App\Core\Request;use App\Core\Response;use App\Models\Permission;use PDOException;

final class PermissionController extends Controller
{
    public function index(): Response { $r=Request::capture();[$s,$p,$pp]=$this->filters($r);$result=(new Permission())->paginate($s,$p,$pp);return $this->view('admin/permissions/index',['title'=>'Permissions','user'=>Auth::user(),'search'=>$s,'page'=>$p,'perPage'=>$pp,'result'=>$result],'layouts/dashboard'); }
    public function create(): Response { return $this->form(null); }
    public function store(): Response { $r=Request::capture();$d=$this->data($r);if(!$d)return Response::redirect(url('admin/permissions/create'));try{(new Permission())->create(...$d);}catch(PDOException $e){return $this->error($e,url('admin/permissions/create'));}ActivityLogger::log(Auth::user()['name'].' created permission '.$d[1].' from '.$r->ip());flash('success','Permission created.');return Response::redirect(url('admin/permissions')); }
    public function edit(string $id): Response { $record=(new Permission())->find((int)$id);return $record?$this->form($record):$this->missing(); }
    public function update(string $id): Response { $r=Request::capture();if(!(new Permission())->find((int)$id))return $this->missing();$d=$this->data($r);if(!$d)return Response::redirect(url('admin/permissions/'.$id.'/edit'));try{(new Permission())->update((int)$id,...$d);}catch(PDOException $e){return $this->error($e,url('admin/permissions/'.$id.'/edit'));}ActivityLogger::log(Auth::user()['name'].' updated permission '.$d[1].' from '.$r->ip());flash('success','Permission updated.');return Response::redirect(url('admin/permissions')); }
    public function delete(string $id): Response { $record=(new Permission())->find((int)$id);if(!$record)return $this->missing();(new Permission())->delete((int)$id);ActivityLogger::log(Auth::user()['name'].' deleted permission '.$record['slug'].' from '.Request::capture()->ip());flash('success','Permission deleted.');return Response::redirect(url('admin/permissions')); }
    private function form(?array $record): Response { return $this->view('admin/permissions/form',['title'=>$record?'Edit permission':'Create permission','user'=>Auth::user(),'record'=>$record],'layouts/dashboard'); }
    private function data(Request $r): ?array { $name=trim((string)$r->input('name'));$slug=strtolower(trim((string)$r->input('slug')));if(strlen($name)<2||!preg_match('/^[a-z0-9.-]+$/',$slug)){flash('error','Use a valid name and slug such as reports.view.');return null;}return[$name,$slug]; }
    private function filters(Request $r): array { $pp=(int)$r->query('per_page',10);if(!in_array($pp,[10,25,50],true))$pp=10;return[trim((string)$r->query('search','')),max(1,(int)$r->query('page',1)),$pp]; }
    private function error(PDOException $e,string $back): Response { flash('error',(string)$e->getCode()==='23000'?'That permission slug already exists.':'Could not save the permission.');return Response::redirect($back); }
    private function missing(): Response { flash('error','Permission not found.');return Response::redirect(url('admin/permissions')); }
}
