<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\PermissionController;
use App\Controllers\AppSettingController;
use App\Controllers\LogController;
use App\Controllers\RateLimitController;
use App\Controllers\DatabaseBackupController;
use App\Controllers\ActivityLogController;
use App\Controllers\ProfileController;
use App\Controllers\HealthController;
use App\Controllers\ExternalApiController;
use App\Controllers\CommunicationController;

$router->get('/health', [HealthController::class, 'index']);
$router->get('/external-api/posts', [ExternalApiController::class, 'posts']);
$router->get('/', [DashboardController::class, 'index'], ['auth', 'permission:dashboard.view']);
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth', 'permission:dashboard.view']);
$router->get('/profile', [ProfileController::class, 'index'], ['auth']);
$router->get('/profile/password', [ProfileController::class, 'password'], ['auth']);
$router->post('/profile/password', [ProfileController::class, 'changePassword'], ['auth']);

$router->get('/users', [UserController::class, 'index'], ['auth', 'permission:users.view']);
$router->get('/users/create', [UserController::class, 'create'], ['auth', 'permission:users.create']);
$router->post('/users/store', [UserController::class, 'store'], ['auth', 'permission:users.create']);
$router->get('/users/edit', [UserController::class, 'edit'], ['auth', 'permission:users.update']);
$router->post('/users/update', [UserController::class, 'update'], ['auth', 'permission:users.update']);
$router->get('/users/delete', [UserController::class, 'delete'], ['auth', 'permission:users.delete']);
$router->post('/users/destroy', [UserController::class, 'destroy'], ['auth', 'permission:users.delete']);

$router->get('/roles', [RoleController::class, 'index'], ['auth', 'permission:roles.view']);
$router->get('/roles/create', [RoleController::class, 'create'], ['auth', 'permission:roles.create']);
$router->post('/roles/store', [RoleController::class, 'store'], ['auth', 'permission:roles.create']);
$router->get('/roles/edit', [RoleController::class, 'edit'], ['auth', 'permission:roles.update']);
$router->post('/roles/update', [RoleController::class, 'update'], ['auth', 'permission:roles.update']);
$router->get('/roles/delete', [RoleController::class, 'delete'], ['auth', 'permission:roles.delete']);
$router->post('/roles/destroy', [RoleController::class, 'destroy'], ['auth', 'permission:roles.delete']);
$router->get('/roles/permissions', [RoleController::class, 'permissions'], ['auth', 'permission:roles.update']);
$router->post('/roles/permissions', [RoleController::class, 'assignPermissions'], ['auth', 'permission:roles.update']);

$router->get('/permissions', [PermissionController::class, 'index'], ['auth', 'role:product-owner,admin']);
$router->get('/permissions/create', [PermissionController::class, 'create'], ['auth', 'permission:sudo']);
$router->post('/permissions/store', [PermissionController::class, 'store'], ['auth', 'permission:sudo']);
$router->get('/permissions/edit', [PermissionController::class, 'edit'], ['auth', 'permission:sudo']);
$router->post('/permissions/update', [PermissionController::class, 'update'], ['auth', 'permission:sudo']);
$router->get('/permissions/delete', [PermissionController::class, 'delete'], ['auth', 'permission:sudo']);
$router->post('/permissions/destroy', [PermissionController::class, 'destroy'], ['auth', 'permission:sudo']);

$router->get('/app-settings', [AppSettingController::class, 'index'], ['auth', 'permission:settings.view']);
$router->post('/app-settings', [AppSettingController::class, 'update'], ['auth', 'permission:settings.update']);
$router->get('/email-settings', [CommunicationController::class, 'settings'], ['auth', 'permission:email-settings.view']);
$router->post('/email-settings', [CommunicationController::class, 'updateSettings'], ['auth', 'permission:email-settings.update']);
$router->get('/test-email-sms', [CommunicationController::class, 'legacyCommunicationPage'], ['auth', 'permission:email.view']);
$router->get('/send-email', [CommunicationController::class, 'emailPage'], ['auth', 'permission:email.view']);
$router->post('/send-email', [CommunicationController::class, 'sendEmail'], ['auth', 'permission:email.send']);
$router->get('/send-sms', [CommunicationController::class, 'smsPage'], ['auth', 'permission:sms.view']);
$router->post('/send-sms', [CommunicationController::class, 'openWhatsApp'], ['auth', 'permission:sms.send']);

$router->get('/logs', [LogController::class, 'index'], ['auth', 'permission:logs.view']);
$router->get('/activity-logs', [ActivityLogController::class, 'index'], ['auth', 'permission:activity-logs.view']);

$router->get('/rate-limits', [RateLimitController::class, 'index'], ['auth', 'permission:rate-limits.view']);
$router->post('/rate-limits', [RateLimitController::class, 'update'], ['auth', 'permission:sudo']);
$router->post('/rate-limits/unblock', [RateLimitController::class, 'unblock'], ['auth', 'permission:rate-limits.update']);

$router->get('/database-backup', [DatabaseBackupController::class, 'index'], ['auth', 'permission:database-backup.view']);
$router->get('/database-backup/database', [DatabaseBackupController::class, 'downloadDatabase'], ['auth', 'permission:database-backup.download']);
$router->get('/database-backup/uploads', [DatabaseBackupController::class, 'downloadUploads'], ['auth', 'permission:database-backup.download']);
$router->get('/database-backup/full', [DatabaseBackupController::class, 'downloadFull'], ['auth', 'permission:database-backup.download']);
$router->post('/database-backup/google/settings', [DatabaseBackupController::class, 'saveGoogleSettings'], ['auth', 'permission:sudo']);
$router->get('/database-backup/google/connect', [DatabaseBackupController::class, 'connectGoogle'], ['auth', 'permission:sudo']);
$router->get('/database-backup/google/callback', [DatabaseBackupController::class, 'googleCallback'], ['auth', 'permission:sudo']);
$router->post('/database-backup/google/disconnect', [DatabaseBackupController::class, 'disconnectGoogle'], ['auth', 'permission:sudo']);
$router->post('/database-backup/google/upload', [DatabaseBackupController::class, 'uploadGoogle'], ['auth', 'permission:database-backup.download']);
