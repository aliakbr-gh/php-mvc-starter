<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Role;
use App\Models\User;
use Core\ActivityLogger;
use Core\Controller;
use Core\Paginator;

final class UserController extends Controller
{
    public function index(): void
    {
        $request = Paginator::request(5);
        $pagination = (new User())->paginate(
            $request['search'],
            $request['page'],
            $request['per_page']
        );

        $this->view('users/index', [
            'title' => 'Users',
            'pagination' => $pagination,
            'allowedPerPage' => Paginator::PER_PAGE_OPTIONS,
        ]);
    }

    public function create(): void
    {
        $userModel = new User();
        $roles = array_values(array_filter(
            (new Role())->all(),
            static fn (array $role): bool => $role['slug'] !== 'product-owner'
                || !$userModel->roleHasUser((int) $role['id'])
        ));

        $this->view('users/create', [
            'title' => 'Create User',
            'roles' => $roles,
        ]);
    }

    public function store(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $roleId = max(0, (int) ($_POST['role_id'] ?? 0));
        $password = (string) ($_POST['password'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $_SESSION['_old'] = [
            'name' => $name,
            'username' => $username,
            'role_id' => $roleId,
            'is_active' => $isActive,
        ];
        if (strlen($name) < 2 || strlen($name) > 100) {
            flash('error', 'Name must be between 2 and 100 characters.');
            $this->redirect('users/create');
        }

        if (strlen($username) < 3 || strlen($username) > 50 || !preg_match('/^[a-z0-9._-]+$/', $username)) {
            flash('error', 'Username must be 3–50 characters and use letters, numbers, dots, underscores, or hyphens.');
            $this->redirect('users/create');
        }

        if (strlen($password) < 5) {
            flash('error', 'Password must contain at least 5 characters.');
            $this->redirect('users/create');
        }

        if ($password !== (string) ($_POST['confirm_password'] ?? '')) {
            flash('error', 'Password confirmation does not match.');
            $this->redirect('users/create');
        }

        $role = (new Role())->find($roleId);

        if ($role === null) {
            flash('error', 'Please select a valid role.');
            $this->redirect('users/create');
        }

        $userModel = new User();

        if ($role['slug'] === 'product-owner' && $userModel->roleHasUser($roleId)) {
            flash('error', 'Only one Product Owner user is allowed.');
            $this->redirect('users/create');
        }

        if ($userModel->usernameExists($username)) {
            flash('error', 'That username is already in use.');
            $this->redirect('users/create');
        }

        $userModel->create([
            'name' => $name,
            'username' => $username,
            'role_id' => $roleId,
            'password' => hashPassword($password),
            'is_active' => $isActive,
        ]);
        unset($_SESSION['_old']);
        ActivityLogger::log('created new user ' . $name);
        flash('success', 'User created successfully.');
        $this->redirect('users');
    }

    public function edit(): void
    {
        $editingUser = $this->findRequestedUser();
        $this->preventFixedAccountEditing($editingUser);
        $userModel = new User();
        $roles = array_values(array_filter(
            (new Role())->all(),
            static fn (array $role): bool => $role['slug'] !== 'product-owner'
                || (int) $editingUser['role_id'] === (int) $role['id']
                || !$userModel->roleHasUser((int) $role['id'])
        ));

        $this->view('users/edit', [
            'title' => 'Edit User',
            'editingUser' => $editingUser,
            'roles' => $roles,
        ]);
    }

    public function update(): void
    {
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $model = new User();

        $existingUser = $model->find($id);

        if (!$existingUser) {
            flash('error', 'User not found.');
            $this->redirect('users');
        }
        $this->preventFixedAccountEditing($existingUser);

        $name = trim((string) ($_POST['name'] ?? ''));
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $roleId = max(0, (int) ($_POST['role_id'] ?? 0));
        $password = (string) ($_POST['password'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $_SESSION['_old'] = [
            'name' => $name,
            'username' => $username,
            'role_id' => $roleId,
            'is_active' => $isActive,
        ];
        if (strlen($name) < 2 || strlen($name) > 100) {
            flash('error', 'Name must be between 2 and 100 characters.');
            $this->redirect('users/edit?id=' . $id);
        }

        if (strlen($username) < 3 || strlen($username) > 50 || !preg_match('/^[a-z0-9._-]+$/', $username)) {
            flash('error', 'Username must be 3–50 characters and use letters, numbers, dots, underscores, or hyphens.');
            $this->redirect('users/edit?id=' . $id);
        }

        if ($password !== '' && strlen($password) < 5) {
            flash('error', 'Password must contain at least 5 characters.');
            $this->redirect('users/edit?id=' . $id);
        }

        $role = (new Role())->find($roleId);

        if ($role === null) {
            flash('error', 'Please select a valid role.');
            $this->redirect('users/edit?id=' . $id);
        }

        if (
            $role['slug'] === 'product-owner'
            && (int) $existingUser['role_id'] !== $roleId
            && $model->roleHasUser($roleId, $id)
        ) {
            flash('error', 'Only one Product Owner user is allowed.');
            $this->redirect('users/edit?id=' . $id);
        }

        if ($model->usernameExists($username, $id)) {
            flash('error', 'That username is already in use.');
            $this->redirect('users/edit?id=' . $id);
        }

        $data = [
            'name' => $name,
            'username' => $username,
            'role_id' => $roleId,
            'is_active' => $isActive,
        ];
        if ($password !== '') {
            $data['password'] = hashPassword($password);
        }
        $model->update($id, $data);

        if ($password !== '' && $id === (int) user()['id']) {
            ActivityLogger::log('updated their account and changed their password');
            unset($_SESSION['_old']);
            end_authenticated_session('Password changed successfully. Please log in again.', 'success');
            $this->redirect('login');
        }

        if (!$isActive && $id === (int) user()['id']) {
            ActivityLogger::log('deactivated their account');
            unset($_SESSION['_old']);
            end_authenticated_session('Your account was deactivated.', 'success');
            $this->redirect('login');
        }

        if ($id === (int) user()['id']) {
            $_SESSION['user']['name'] = $data['name'];
            $_SESSION['user']['username'] = $data['username'];
        }

        unset($_SESSION['_old']);
        ActivityLogger::log('updated user ' . $name);
        flash('success', 'User updated successfully.');
        $this->redirect('users');
    }

    public function delete(): void
    {
        $deletingUser = $this->findRequestedUser();

        if ((int) $deletingUser['id'] === (int) user()['id']) {
            flash('error', 'You cannot delete your own account.');
            $this->redirect('users');
        }

        if (in_array($deletingUser['role_slug'], ['product-owner', 'admin'], true)) {
            flash('error', 'Product Owner and Admin accounts cannot be deleted.');
            $this->redirect('users');
        }

        $this->view('users/delete', [
            'title' => 'Delete User',
            'deletingUser' => $deletingUser,
        ]);
    }

    public function destroy(): void
    {
        $id = max(0, (int) ($_POST['id'] ?? 0));

        if ($id === (int) user()['id']) {
            flash('error', 'You cannot delete your own account.');
            $this->redirect('users');
        }

        $model = new User();
        $deletedUser = $model->find($id);

        if (
            $deletedUser !== null
            && in_array($deletedUser['role_slug'], ['product-owner', 'admin'], true)
        ) {
            flash('error', 'Product Owner and Admin accounts cannot be deleted.');
            $this->redirect('users');
        }

        $deleted = $model->delete($id);
        if ($deleted && $deletedUser !== null) {
            ActivityLogger::log('deleted user ' . $deletedUser['name']);
        }
        flash($deleted ? 'success' : 'error', $deleted ? 'User deleted successfully.' : 'User not found.');
        $this->redirect('users');
    }

    private function findRequestedUser(): array
    {
        $record = (new User())->find(max(0, (int) ($_GET['id'] ?? 0)));

        if (!$record) {
            flash('error', 'User not found.');
            $this->redirect('users');
        }

        return $record;
    }

    private function preventFixedAccountEditing(array $account): void
    {
        if (in_array($account['role_slug'], ['product-owner', 'admin'], true)) {
            flash('error', 'Product Owner and Admin accounts cannot be edited.');
            $this->redirect('users');
        }
    }
}
