<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Permission;
use App\Models\User;
use Core\ActivityLogger;
use Core\Controller;

final class ProfileController extends Controller
{
    public function index(): void
    {
        $profile = (new User())->find((int) user()['id']);

        if ($profile === null) {
            abort(404, 'User profile not found.');
        }

        $permissionModel = new Permission();
        $assignedPermissions = $permissionModel->forRole((int) $profile['role_id']);
        $hasSudo = in_array('sudo', array_column($assignedPermissions, 'slug'), true);

        $this->view('profile/index', [
            'title' => 'My Profile',
            'profile' => $profile,
            'permissions' => $hasSudo ? $permissionModel->all() : $assignedPermissions,
            'hasSudo' => $hasSudo,
        ]);
    }

    public function password(): void
    {
        $this->view('profile/password', ['title' => 'Change Password']);
    }

    public function changePassword(): void
    {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmation = (string) ($_POST['confirm_password'] ?? '');
        $model = new User();
        $account = $model->findByUsername((string) user()['username']);

        if ($account === null || !password_verify($currentPassword, $account['password'])) {
            flash('error', 'The current password is incorrect.');
            $this->redirect('profile/password');
        }

        if (strlen($newPassword) < 5) {
            flash('error', 'The new password must contain at least 5 characters.');
            $this->redirect('profile/password');
        }

        if ($newPassword !== $confirmation) {
            flash('error', 'The new password confirmation does not match.');
            $this->redirect('profile/password');
        }

        if (password_verify($newPassword, $account['password'])) {
            flash('error', 'The new password must be different from the current password.');
            $this->redirect('profile/password');
        }

        ActivityLogger::log('changed their account password');
        $model->updatePassword((int) user()['id'], hashPassword($newPassword));
        end_authenticated_session('Password changed successfully. Please log in again.', 'success');
        $this->redirect('login');
    }
}
