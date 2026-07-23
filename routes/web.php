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
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);
$router->get('/settings', [SettingsController::class, 'index'], ['auth']);
$router->post('/settings', [SettingsController::class, 'update'], ['auth']);
$router->get('/users', [UserController::class, 'index'], ['auth', 'permission:users.view']);
$router->get('/users/create', [UserController::class, 'create'], ['auth', 'permission:users.create']);
$router->post('/users', [UserController::class, 'store'], ['auth', 'permission:users.create']);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], ['auth', 'permission:users.update']);
$router->post('/users/{id}/update', [UserController::class, 'update'], ['auth', 'permission:users.update']);
$router->post('/users/{id}/delete', [UserController::class, 'delete'], ['auth', 'permission:users.delete']);

$router->get('/roles', [RoleController::class, 'index'], ['auth', 'permission:roles.view']);
$router->get('/roles/create', [RoleController::class, 'create'], ['auth', 'permission:roles.create']);
$router->post('/roles', [RoleController::class, 'store'], ['auth', 'permission:roles.create']);
$router->get('/roles/{id}/edit', [RoleController::class, 'edit'], ['auth', 'permission:roles.update']);
$router->post('/roles/{id}/update', [RoleController::class, 'update'], ['auth', 'permission:roles.update']);
$router->post('/roles/{id}/delete', [RoleController::class, 'delete'], ['auth', 'permission:roles.delete']);

$router->get('/permissions', [PermissionController::class, 'index'], ['auth', 'permission:permissions.view']);
$router->get('/permissions/create', [PermissionController::class, 'create'], ['auth', 'permission:permissions.create']);
$router->post('/permissions', [PermissionController::class, 'store'], ['auth', 'permission:permissions.create']);
$router->get('/permissions/{id}/edit', [PermissionController::class, 'edit'], ['auth', 'permission:permissions.update']);
$router->post('/permissions/{id}/update', [PermissionController::class, 'update'], ['auth', 'permission:permissions.update']);
$router->post('/permissions/{id}/delete', [PermissionController::class, 'delete'], ['auth', 'permission:permissions.delete']);
