<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ActivityLogger;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;

final class AuthController extends Controller
{
    public function loginForm(): Response
    {
        return $this->view('auth/login', ['title' => 'Login']);
    }

    public function login(): Response
    {
        $request = Request::capture();
        $username = strtolower(trim((string) $request->input('username')));
        if ($username === '' || !Auth::attempt($username, (string) $request->input('password'))) {
            Logger::warning('Failed login', ['username' => $username, 'ip' => $request->ip()]);
            flash('error', 'The username or password is incorrect.');
            return Response::redirect(url('login'));
        }
        Logger::info('User logged in', ['user_id' => Auth::user()['id']]);
        ActivityLogger::log(
            Auth::user()['name'] . ' has been logged in from ' . $request->ip(),
            (int) Auth::user()['id']
        );
        flash('success', 'Welcome back, ' . Auth::user()['name'] . '!');
        return Response::redirect(url('dashboard'));
    }

    public function logout(): Response
    {
        $user = Auth::user();
        $id = $user['id'] ?? null;
        if ($user !== null) {
            ActivityLogger::log(
                $user['name'] . ' has been logged out from ' . Request::capture()->ip(),
                (int) $user['id']
            );
        }
        Auth::logout();
        Logger::info('User logged out', ['user_id' => $id]);
        flash('success', 'You have been logged out.');
        return Response::redirect(url('login'));
    }
}
