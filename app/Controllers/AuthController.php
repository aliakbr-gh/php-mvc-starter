<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Core\ActivityLogger;
use Core\Controller;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function login(): void
    {
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $_SESSION['_old'] = ['username' => $username];
        $model = new User();
        $account = $model->findByUsername($username);

        if (!$account || !password_verify($password, $account['password'])) {
            flash('error', 'The username or password is incorrect.');
            $this->redirect('login');
        }

        if (!(bool) $account['is_active']) {
            flash('error', 'Your account is inactive. Contact an administrator.');
            $this->redirect('login');
        }

        if (passwordNeedsRehash($account['password'])) {
            $model->updatePassword(
                (int) $account['id'],
                hashPassword($password)
            );
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $account['id'],
            'name' => $account['name'],
            'username' => $account['username'],
            'session_version' => (int) $account['session_version'],
            'last_activity_at' => time(),
        ];
        unset($_SESSION['_old']);
        ActivityLogger::log('logged in');
        flash('success', 'Welcome back, ' . $account['name'] . '!');
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        ActivityLogger::log('logged out');
        unset($_SESSION['user'], $_SESSION['_old']);
        session_regenerate_id(true);
        flash('success', 'You have been logged out.');
        $this->redirect('login');
    }
}
