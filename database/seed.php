<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$permissions = [
    'sudo' => 'Sudo',
    'dashboard.view' => 'View dashboard',
    'users.view' => 'View users',
    'users.create' => 'Create users',
    'users.update' => 'Update users',
    'users.delete' => 'Delete users',
    'roles.view' => 'View roles',
    'roles.create' => 'Create roles',
    'roles.update' => 'Update roles',
    'roles.delete' => 'Delete roles',
    'permissions.view' => 'View permissions',
    'permissions.create' => 'Create permissions',
    'permissions.update' => 'Update permissions',
    'permissions.delete' => 'Delete permissions',
    'settings.view' => 'View application settings',
    'settings.update' => 'Update application settings',
    'logs.view' => 'View request logs',
    'rate-limits.view' => 'View rate limits',
    'rate-limits.update' => 'Update rate limits',
    'database-backup.view' => 'View database backups',
    'database-backup.download' => 'Download database backups',
    'activity-logs.view' => 'View activity logs',
];
$productOwnerOnlyPermissions = [
    'sudo',
    'settings.view',
    'settings.update',
    'logs.view',
    'rate-limits.view',
    'rate-limits.update',
    'permissions.view',
    'permissions.create',
    'permissions.update',
    'permissions.delete',
];

$databaseConfig = require dirname(__DIR__) . '/config/database.php';
$databaseName = (string) $databaseConfig['database'];

try {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $databaseName)) {
        throw new RuntimeException('The configured database name contains unsupported characters.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(
        $databaseConfig['host'],
        $databaseConfig['username'],
        $databaseConfig['password'],
        '',
        $databaseConfig['port']
    );
    $db->set_charset('utf8mb4');

    $databaseIdentifier = '`' . $databaseName . '`';
    $db->query('DROP DATABASE IF EXISTS ' . $databaseIdentifier);
    $db->query(
        'CREATE DATABASE ' . $databaseIdentifier
        . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    $db->select_db($databaseName);

    $db->query(
        'CREATE TABLE roles (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )'
    );
    $db->query(
        'CREATE TABLE permissions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(120) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )'
    );
    $db->query(
        'CREATE TABLE role_permissions (
            role_id BIGINT UNSIGNED NOT NULL,
            permission_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ) '
    );
    $db->query(
        'CREATE TABLE users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            session_version BIGINT UNSIGNED NOT NULL DEFAULT 1,
            role_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_users_role_id (role_id),
            CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
        ) '
    );
    $db->query(
        'CREATE TABLE activity_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            user_name VARCHAR(100) NOT NULL,
            username VARCHAR(50) NOT NULL,
            description VARCHAR(500) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_activity_logs_created_at (created_at),
            INDEX idx_activity_logs_user_id (user_id),
            CONSTRAINT fk_activity_logs_user
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) '
    );
    $db->query(
        'CREATE TABLE rate_limit_entries (
            ip_address VARCHAR(45) NOT NULL PRIMARY KEY,
            window_started_at DECIMAL(16,6) NOT NULL,
            request_count INT UNSIGNED NOT NULL DEFAULT 0,
            violations INT UNSIGNED NOT NULL DEFAULT 0,
            last_violation_window BIGINT UNSIGNED NOT NULL DEFAULT 0,
            paused_until BIGINT UNSIGNED NOT NULL DEFAULT 0,
            blocked TINYINT(1) NOT NULL DEFAULT 0,
            last_request_at BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_rate_limit_entries_cleanup (blocked, last_request_at)
        ) '
    );

    $db->begin_transaction();

    $permissionStatement = $db->prepare(
        'INSERT INTO permissions (slug, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)'
    );
    foreach ($permissions as $slug => $name) {
        $permissionStatement->bind_param('ss', $slug, $name);
        $permissionStatement->execute();
    }

    $roleSlug = 'product-owner';
    $roleName = 'Product Owner';
    $roleStatement = $db->prepare(
        'INSERT INTO roles (slug, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)'
    );
    $roleStatement->bind_param('ss', $roleSlug, $roleName);
    $roleStatement->execute();

    $assignment = $db->prepare(
        'INSERT IGNORE INTO role_permissions (role_id, permission_id)
         SELECT roles.id, permissions.id FROM roles
         INNER JOIN permissions ON permissions.slug = ?
         WHERE roles.slug = ?'
    );

    $adminRoleSlug = 'admin';
    $adminRoleName = 'Admin';
    $roleStatement->bind_param('ss', $adminRoleSlug, $adminRoleName);
    $roleStatement->execute();

    $clearRolePermissions = $db->prepare(
        'DELETE role_permissions
         FROM role_permissions
         INNER JOIN roles ON roles.id = role_permissions.role_id
         WHERE roles.slug = ?'
    );

    foreach ([$roleSlug, $adminRoleSlug] as $fixedRoleSlug) {
        $clearRolePermissions->bind_param('s', $fixedRoleSlug);
        $clearRolePermissions->execute();
    }

    $sudoSlug = 'sudo';
    $assignment->bind_param('ss', $sudoSlug, $roleSlug);
    $assignment->execute();

    foreach (array_keys($permissions) as $permissionSlug) {
        if (in_array($permissionSlug, $productOwnerOnlyPermissions, true)) {
            continue;
        }

        $assignment->bind_param('ss', $permissionSlug, $adminRoleSlug);
        $assignment->execute();
    }

    $userStatement = $db->prepare(
        'INSERT INTO users (name, username, password, role_id, is_active, session_version)
         SELECT ?, ?, ?, roles.id, 1, 1 FROM roles WHERE roles.slug = ?
         ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password),
            role_id = VALUES(role_id), is_active = 1, session_version = 1'
    );

    $defaultUsers = [
        [
            'name' => 'Admin',
            'username' => 'admin',
            'password' => 'admin123',
            'role_slug' => $adminRoleSlug,
        ],
        [
            'name' => 'Muhammad Ali Akbar',
            'username' => 'sudo',
            'password' => 'sudo123',
            'role_slug' => $roleSlug,
        ],
    ];

    foreach ($defaultUsers as $defaultUser) {
        $name = $defaultUser['name'];
        $username = $defaultUser['username'];
        $password = hashPassword($defaultUser['password']);
        $userRoleSlug = $defaultUser['role_slug'];
        $userStatement->bind_param('ssss', $name, $username, $password, $userRoleSlug);
        $userStatement->execute();
    }

    $db->commit();
    echo 'Database "' . $databaseName . '" reset successfully: '
        . count($permissions)
        . ' permissions, 2 roles, and 2 default users created.'
        . PHP_EOL;
} catch (Throwable $exception) {
    if (isset($db) && $db instanceof \mysqli) {
        try {
            $db->rollback();
        } catch (Throwable) {
        }
    }

    fwrite(STDERR, 'Seed failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
