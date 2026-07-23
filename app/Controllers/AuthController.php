<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ActivityLogger;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use PDOException;

final class AuthController extends Controller
{
    public function loginForm(): Response {
        return $this->view('auth/login', ['title' => 'Login']);
    }
    public function registerForm(): Response { return $this->view('auth/register', ['title' => 'Register']); }

    public function login(): Response
    {
        $request = Request::capture();
        $email = trim((string) $request->input('email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !Auth::attempt($email, (string) $request->input('password'))) {
            Logger::warning('Failed login', ['email' => $email, 'ip' => $request->ip()]);
            flash('error', 'The email or password is incorrect.');
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

    public function register(): Response
    {
        $request = Request::capture();
        $name = trim((string) $request->input('name'));
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            flash('error', 'Use a valid name, email, and password of at least 8 characters.');
            return Response::redirect(url('register'));
        }
        try {
            $id = (new User())->create($name, $email, $password);
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                flash('error', 'That email address is already registered.');
                return Response::redirect(url('register'));
            }
            throw $exception;
        }
        Auth::login($id);
        Logger::info('User registered', ['user_id' => $id]);
        ActivityLogger::log($name . ' has registered from ' . $request->ip(), $id);
        flash('success', 'Your account has been created.');
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
