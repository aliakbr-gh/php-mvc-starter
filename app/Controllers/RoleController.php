<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Permission;
use App\Models\Role;
use PDOException;

final class RoleController extends Controller
{
    public function index(): Response
    {
        $request = Request::capture();
        [$search, $page, $perPage] = $this->filters($request);
        $result = (new Role())->paginate($search, $page, $perPage);

        return $this->view('admin/roles/index', [
            'title' => 'Roles', 'user' => Auth::user(), 'search' => $search,
            'page' => $page, 'perPage' => $perPage, 'result' => $result,
        ], 'layouts/dashboard');
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(): Response
    {
        $r = Request::capture();
        $d = $this->data($r);
        if (!$d) return Response::redirect(url('admin/roles/create'));
        try {
            (new Role())->create(...$d);
        } catch (PDOException $e) {
            return $this->error($e, url('admin/roles/create'));
        }
        ActivityLogger::log(Auth::user()['name'] . ' created role ' . $d[0] . ' from ' . $r->ip());
        flash('success', 'Role created.');
        return Response::redirect(url('admin/roles'));
    }

    public function edit(string $id): Response
    {
        $role = (new Role())->find((int)$id);
        return $role ? $this->form($role) : $this->missing();
    }

    public function update(string $id): Response
    {
        $r = Request::capture();
        $role = (new Role())->find((int)$id);
        if (!$role) return $this->missing();
        $d = $this->data($r);
        if (!$d) return Response::redirect(url('admin/roles/' . $id . '/edit'));
        try {
            (new Role())->update((int)$id, ...$d);
        } catch (PDOException $e) {
            return $this->error($e, url('admin/roles/' . $id . '/edit'));
        }
        ActivityLogger::log(Auth::user()['name'] . ' updated role ' . $d[0] . ' from ' . $r->ip());
        flash('success', 'Role updated.');
        return Response::redirect(url('admin/roles'));
    }

    public function delete(string $id): Response
    {
        $role = (new Role())->find((int)$id);
        if (!$role) return $this->missing();
        if (in_array($role['slug'], ['admin', 'user'], true)) {
            flash('error', 'System roles cannot be deleted.');
            return Response::redirect(url('admin/roles'));
        }
        try {
            (new Role())->delete((int)$id);
        } catch (PDOException) {
            flash('error', 'Assign this role’s users elsewhere before deleting it.');
            return Response::redirect(url('admin/roles'));
        }
        ActivityLogger::log(Auth::user()['name'] . ' deleted role ' . $role['name'] . ' from ' . Request::capture()->ip());
        flash('success', 'Role deleted.');
        return Response::redirect(url('admin/roles'));
    }

    private function form(?array $role): Response
    {
        return $this->view('admin/roles/form', [
            'title' => $role ? 'Edit role' : 'Create role',
            'user' => Auth::user(),
            'record' => $role,
            'permissions' => (new Permission())->all(),
            'selected' => $role ? (new Role())->permissionIds((int)$role['id']) : [],
        ], 'layouts/dashboard');
    }

    private function data(Request $request): ?array
    {
        $name = trim((string)$request->input('name'));
        $slug = strtolower(trim((string)$request->input('slug')));
        $permissions = $request->input('permissions', []);

        if (strlen($name) < 2 || !preg_match('/^[a-z0-9.-]+$/', $slug) || !is_array($permissions)) {
            flash('error', 'Enter a valid role name and lowercase slug.');
            return null;
        }

        return [$name, $slug, $permissions];
    }

    private function filters(Request $request): array
    {
        $perPage = (int)$request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        return [trim((string)$request->query('search', '')), max(1, (int)$request->query('page', 1)), $perPage];
    }

    private function error(PDOException $exception, string $back): Response
    {
        flash('error', (string)$exception->getCode() === '23000' ? 'That role slug already exists.' : 'Could not save the role.');
        return Response::redirect($back);
    }

    private function missing(): Response
    {
        flash('error', 'Role not found.');
        return Response::redirect(url('admin/roles'));
    }
}
