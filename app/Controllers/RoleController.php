<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Core\ActivityLogger;
use Core\Controller;
use Core\Paginator;

final class RoleController extends Controller
{
    public function index(): void
    {
        $request = Paginator::request();
        $this->view('roles/index', [
            'title' => 'Roles',
            'pagination' => (new Role())->paginate($request['search'], $request['page'], $request['per_page']),
            'allowedPerPage' => Paginator::PER_PAGE_OPTIONS,
            'paginationPath' => 'roles',
        ]);
    }

    public function create(): void
    {
        $this->view('roles/create', ['title' => 'Create Role']);
    }

    public function store(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $_SESSION['_old'] = compact('name', 'slug');
        if (strlen($name) < 2 || strlen($name) > 100) {
            flash('error', 'Name must be between 2 and 100 characters.');
            $this->redirect('roles/create');
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            flash('error', 'Slug must use lowercase letters, numbers, and single hyphens.');
            $this->redirect('roles/create');
        }
        if ((new Role())->slugExists($slug)) {
            flash('error', 'That role slug is already in use.');
            $this->redirect('roles/create');
        }
        (new Role())->create($name, $slug);
        unset($_SESSION['_old']);
        ActivityLogger::log('created new role ' . $name);
        flash('success', 'Role created successfully.');
        $this->redirect('roles');
    }

    public function edit(): void
    {
        $role = $this->requestedRole();
        $this->preventFixedRoleEditing($role);
        $this->view('roles/edit', ['title' => 'Edit Role', 'role' => $role]);
    }

    public function update(): void
    {
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $model = new Role();
        $existingRole = $model->find($id);
        if (!$existingRole) {
            flash('error', 'Role not found.');
            $this->redirect('roles');
        }
        $this->preventFixedRoleEditing($existingRole);
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $_SESSION['_old'] = compact('name', 'slug');
        if (
            in_array($existingRole['slug'], ['product-owner', 'admin'], true)
            && $slug !== $existingRole['slug']
        ) {
            flash('error', 'The Product Owner and Admin role slugs cannot be changed.');
            $this->redirect('roles/edit?id=' . $id);
        }
        if (strlen($name) < 2 || strlen($name) > 100) {
            flash('error', 'Name must be between 2 and 100 characters.');
            $this->redirect('roles/edit?id=' . $id);
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            flash('error', 'Slug must use lowercase letters, numbers, and single hyphens.');
            $this->redirect('roles/edit?id=' . $id);
        }
        if ($model->slugExists($slug, $id)) {
            flash('error', 'That role slug is already in use.');
            $this->redirect('roles/edit?id=' . $id);
        }
        $model->update($id, $name, $slug);
        unset($_SESSION['_old']);
        ActivityLogger::log('updated role ' . $name);
        flash('success', 'Role updated successfully.');
        $this->redirect('roles');
    }

    public function delete(): void
    {
        $this->view('roles/delete', ['title' => 'Delete Role', 'role' => $this->requestedRole()]);
    }

    public function destroy(): void
    {
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $model = new Role();
        $role = $model->find($id);

        if ($role !== null && in_array($role['slug'], ['product-owner', 'admin'], true)) {
            flash('error', 'The Product Owner and Admin roles cannot be deleted.');
            $this->redirect('roles');
        }

        $deleted = $model->delete($id);
        if ($deleted && $role !== null) {
            ActivityLogger::log('deleted role ' . $role['name']);
        }
        flash($deleted ? 'success' : 'error', $deleted ? 'Role deleted successfully.' : 'Role could not be deleted. It may be assigned to users.');
        $this->redirect('roles');
    }

    public function permissions(): void
    {
        $role = $this->requestedRole();
        $this->preventFixedRolePermissionChanges($role);
        $permissionModel = new Permission();
        $this->view('roles/permissions', [
            'title' => 'Assign Permissions',
            'role' => $role,
            'permissions' => $permissionModel->allAssignable(),
            'assignedIds' => (new Role())->permissionIds((int) $role['id']),
        ]);
    }

    public function assignPermissions(): void
    {
        $roleId = max(0, (int) ($_POST['id'] ?? 0));
        $model = new Role();
        $role = $model->find($roleId);
        if (!$role) {
            flash('error', 'Role not found.');
            $this->redirect('roles');
        }
        $this->preventFixedRolePermissionChanges($role);

        $permissionModel = new Permission();
        $validIds = array_map('intval', array_column($permissionModel->all(), 'id'));
        $submitted = is_array($_POST['permissions'] ?? null) ? $_POST['permissions'] : [];
        $permissionIds = array_values(array_intersect($validIds, array_map('intval', $submitted)));

        if ($role['slug'] === 'product-owner') {
            $sudoId = $permissionModel->idBySlug('sudo');
            if ($sudoId !== null) {
                $permissionIds[] = $sudoId;
            }
        } else {
            $permissionIds = array_values(array_diff($permissionIds, $permissionModel->productOwnerOnlyIds()));
        }

        $model->syncPermissions($roleId, $permissionIds);
        ActivityLogger::log('updated permissions for role ' . $role['name']);
        flash('success', 'Role permissions updated successfully.');
        $this->redirect('roles');
    }

    private function requestedRole(): array
    {
        $role = (new Role())->find(max(0, (int) ($_GET['id'] ?? 0)));
        if ($role === null) {
            flash('error', 'Role not found.');
            $this->redirect('roles');
        }
        return $role;
    }

    private function preventFixedRolePermissionChanges(array $role): void
    {
        if (in_array($role['slug'], ['product-owner', 'admin'], true)) {
            flash('error', 'Permissions for Product Owner and Admin are managed by the database seeder.');
            $this->redirect('roles');
        }
    }

    private function preventFixedRoleEditing(array $role): void
    {
        if (in_array($role['slug'], ['product-owner', 'admin'], true)) {
            flash('error', 'The Product Owner and Admin roles cannot be edited.');
            $this->redirect('roles');
        }
    }
}
