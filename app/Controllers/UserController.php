<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Role;
use App\Models\User;
use PDOException;

final class UserController extends Controller
{
    public function index(): Response
    {
        $request=Request::capture(); [$search,$page,$perPage]=$this->filters($request);
        $result=(new User())->paginate($search,$page,$perPage);
        return $this->view('admin/users/index',compact('search','page','perPage','result')+['title'=>'Users','user'=>Auth::user()],'layouts/dashboard');
    }
    public function create(): Response { return $this->form(null); }
    public function store(): Response
    {
        $r=Request::capture(); $data=$this->validate($r,true); if($data===null)return Response::redirect(url('admin/users/create'));
        try { $id=(new User())->createManaged(...$data); } catch(PDOException $e){ return $this->databaseError($e,url('admin/users/create')); }
        ActivityLogger::log(Auth::user()['name'].' created user '.$data[1].' from '.$r->ip()); flash('success','User created successfully.'); return Response::redirect(url('admin/users'));
    }
    public function edit(string $id): Response { $record=(new User())->findById((int)$id); if(!$record)return $this->missing(); return $this->form($record); }
    public function update(string $id): Response
    {
        $r=Request::capture();$record=(new User())->findById((int)$id);if(!$record)return $this->missing();$data=$this->validate($r,false);if($data===null)return Response::redirect(url('admin/users/'.$id.'/edit'));
        try{(new User())->update((int)$id,$data[0],$data[1],$data[3],$data[2]?:null);}catch(PDOException $e){return $this->databaseError($e,url('admin/users/'.$id.'/edit'));}
        ActivityLogger::log(Auth::user()['name'].' updated user '.$data[1].' from '.$r->ip());flash('success','User updated successfully.');return Response::redirect(url('admin/users'));
    }
    public function delete(string $id): Response
    {
        if((int)$id===(int)Auth::user()['id']){flash('error','You cannot delete your own account.');return Response::redirect(url('admin/users'));}
        $record=(new User())->findById((int)$id);if(!$record)return $this->missing();(new User())->delete((int)$id);ActivityLogger::log(Auth::user()['name'].' deleted user '.$record['email'].' from '.Request::capture()->ip());flash('success','User deleted.');return Response::redirect(url('admin/users'));
    }
    private function form(?array $record): Response { return $this->view('admin/users/form',['title'=>$record?'Edit user':'Create user','user'=>Auth::user(),'record'=>$record,'roles'=>(new Role())->all()],'layouts/dashboard'); }
    private function validate(Request $r,bool $passwordRequired): ?array { $name=trim((string)$r->input('name'));$email=trim((string)$r->input('email'));$password=(string)$r->input('password');$role=(int)$r->input('role_id');if(strlen($name)<2||!filter_var($email,FILTER_VALIDATE_EMAIL)||($passwordRequired&&strlen($password)<8)||($password!==''&&strlen($password)<8)||$role<1){flash('error','Enter valid details; passwords must be at least 8 characters.');return null;}return[$name,$email,$password,$role]; }
    private function filters(Request $r): array { $per=(int)$r->query('per_page',10);if(!in_array($per,[10,25,50],true))$per=10;return[trim((string)$r->query('search','')),max(1,(int)$r->query('page',1)),$per]; }
    private function databaseError(PDOException $e,string $back): Response { flash('error',(string)$e->getCode()==='23000'?'Email already exists or the selected role is invalid.':'Could not save the user.');return Response::redirect($back); }
    private function missing(): Response { flash('error','User not found.');return Response::redirect(url('admin/users')); }
}
