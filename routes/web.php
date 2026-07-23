<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\PermissionController;
use App\Controllers\SettingsController;
use App\Controllers\HealthController;

$router = $app->router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/health', [HealthController::class, 'index']);
$router->get('/login', [AuthController::class, 'loginForm'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/register', [AuthController::class, 'registerForm'], ['guest']);
$router->post('/register', [AuthController::class, 'register'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);
$router->get('/settings', [SettingsController::class, 'index'], ['auth']);
$router->post('/settings', [SettingsController::class, 'update'], ['auth']);
$router->get('/admin/users', [UserController::class, 'index'], ['auth', 'permission:users.view']);
$router->get('/admin/users/create', [UserController::class, 'create'], ['auth', 'permission:users.create']);
$router->post('/admin/users', [UserController::class, 'store'], ['auth', 'permission:users.create']);
$router->get('/admin/users/{id}/edit', [UserController::class, 'edit'], ['auth', 'permission:users.update']);
$router->post('/admin/users/{id}/update', [UserController::class, 'update'], ['auth', 'permission:users.update']);
$router->post('/admin/users/{id}/delete', [UserController::class, 'delete'], ['auth', 'permission:users.delete']);

$router->get('/admin/roles', [RoleController::class, 'index'], ['auth', 'permission:roles.view']);
$router->get('/admin/roles/create', [RoleController::class, 'create'], ['auth', 'permission:roles.create']);
$router->post('/admin/roles', [RoleController::class, 'store'], ['auth', 'permission:roles.create']);
$router->get('/admin/roles/{id}/edit', [RoleController::class, 'edit'], ['auth', 'permission:roles.update']);
$router->post('/admin/roles/{id}/update', [RoleController::class, 'update'], ['auth', 'permission:roles.update']);
$router->post('/admin/roles/{id}/delete', [RoleController::class, 'delete'], ['auth', 'permission:roles.delete']);

$router->get('/admin/permissions', [PermissionController::class, 'index'], ['auth', 'permission:permissions.view']);
$router->get('/admin/permissions/create', [PermissionController::class, 'create'], ['auth', 'permission:permissions.create']);
$router->post('/admin/permissions', [PermissionController::class, 'store'], ['auth', 'permission:permissions.create']);
$router->get('/admin/permissions/{id}/edit', [PermissionController::class, 'edit'], ['auth', 'permission:permissions.update']);
$router->post('/admin/permissions/{id}/update', [PermissionController::class, 'update'], ['auth', 'permission:permissions.update']);
$router->post('/admin/permissions/{id}/delete', [PermissionController::class, 'delete'], ['auth', 'permission:permissions.delete']);
