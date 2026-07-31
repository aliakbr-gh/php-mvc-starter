<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Permission;
use Core\ActivityLogger;
use Core\Controller;
use Core\Paginator;

final class PermissionController extends Controller
{
    public function index(): void
    {
        $request = Paginator::request();
        $this->view('permissions/index', [
            'title' => 'Permissions',
            'pagination' => (new Permission())->paginate($request['search'], $request['page'], $request['per_page']),
            'allowedPerPage' => Paginator::PER_PAGE_OPTIONS,
            'paginationPath' => 'permissions',
        ]);
    }

    public function create(): void
    {
        $this->view('permissions/create', ['title' => 'Create Permission']);
    }

    public function store(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $_SESSION['_old'] = compact('name', 'slug');
        if (strlen($name) < 2 || strlen($name) > 120) {
            flash('error', 'Name must be between 2 and 120 characters.');
            $this->redirect('permissions/create');
        }
        if (!preg_match('/^[a-z0-9-]+(?:\.[a-z0-9-]+)+$/', $slug) && $slug !== 'sudo') {
            flash('error', 'Slug must look like module.action (for example, users.create).');
            $this->redirect('permissions/create');
        }
        if ((new Permission())->slugExists($slug)) {
            flash('error', 'That permission slug is already in use.');
            $this->redirect('permissions/create');
        }
        (new Permission())->create($name, $slug);
        unset($_SESSION['_old']);
        ActivityLogger::log('created new permission ' . $name);
        flash('success', 'Permission created successfully.');
        $this->redirect('permissions');
    }

    public function edit(): void
    {
        $this->view('permissions/edit', ['title' => 'Edit Permission', 'permission' => $this->requestedPermission()]);
    }

    public function update(): void
    {
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $model = new Permission();
        $existingPermission = $model->find($id);
        if (!$existingPermission) {
            flash('error', 'Permission not found.');
            $this->redirect('permissions');
        }
        if (Permission::isProtectedSlug($existingPermission['slug'])) {
            flash('error', 'System permissions cannot be renamed or edited.');
            $this->redirect('permissions');
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $_SESSION['_old'] = compact('name', 'slug');
        if (strlen($name) < 2 || strlen($name) > 120) {
            flash('error', 'Name must be between 2 and 120 characters.');
            $this->redirect('permissions/edit?id=' . $id);
        }
        if (!preg_match('/^[a-z0-9-]+(?:\.[a-z0-9-]+)+$/', $slug) && $slug !== 'sudo') {
            flash('error', 'Slug must look like module.action (for example, users.create).');
            $this->redirect('permissions/edit?id=' . $id);
        }
        if ($model->slugExists($slug, $id)) {
            flash('error', 'That permission slug is already in use.');
            $this->redirect('permissions/edit?id=' . $id);
        }
        $model->update($id, $name, $slug);
        unset($_SESSION['_old']);
        ActivityLogger::log('updated permission ' . $name);
        flash('success', 'Permission updated successfully.');
        $this->redirect('permissions');
    }

    public function delete(): void
    {
        $this->view('permissions/delete', ['title' => 'Delete Permission', 'permission' => $this->requestedPermission()]);
    }

    public function destroy(): void
    {
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $model = new Permission();
        $permission = $model->find($id);

        if ($permission !== null && Permission::isProtectedSlug($permission['slug'])) {
            flash('error', 'System permissions cannot be deleted.');
            $this->redirect('permissions');
        }

        $deleted = $model->delete($id);
        if ($deleted && $permission !== null) {
            ActivityLogger::log('deleted permission ' . $permission['name']);
        }
        flash($deleted ? 'success' : 'error', $deleted ? 'Permission deleted successfully.' : 'Permission not found.');
        $this->redirect('permissions');
    }

    private function requestedPermission(): array
    {
        $permission = (new Permission())->find(max(0, (int) ($_GET['id'] ?? 0)));
        if ($permission === null) {
            flash('error', 'Permission not found.');
            $this->redirect('permissions');
        }
        return $permission;
    }
}
