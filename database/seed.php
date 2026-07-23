<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));

$database = require BASE_PATH . '/config/database.php';
$adminName = trim(getenv('ADMIN_NAME') ?: 'admin');
$adminUsername = strtolower(trim(getenv('ADMIN_USERNAME') ?: 'admin'));
$adminPassword = getenv('ADMIN_PASSWORD') ?: 'Admin@12345';

if (strlen($adminName) < 2
    || !preg_match('/^[a-z0-9._-]{3,50}$/', $adminUsername)
    || strlen($adminPassword) < 8) {
    fwrite(STDERR, "Use a valid admin name, username, and password of at least 8 characters.\n");
    exit(1);
}

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
    'settings.view' => 'View app settings',
    'settings.update' => 'Update app settings',
];

try {
    $pdo = new PDO($database['dsn'], $database['username'], $database['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $userColumns = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('email', $userColumns, true) && !in_array('username', $userColumns, true)) {
        $pdo->exec(
            'ALTER TABLE users
             CHANGE COLUMN email username VARCHAR(190) NOT NULL'
        );

        $legacyUsers = $pdo->query('SELECT id, username FROM users ORDER BY id')->fetchAll();
        $usedUsernames = [];
        foreach ($legacyUsers as $legacyUser) {
            $value = strtolower((string) $legacyUser['username']);
            if (preg_match('/^[a-z0-9._-]{3,50}$/', $value)) {
                $usedUsernames[$value] = true;
            }
        }

        $renameUser = $pdo->prepare(
            'UPDATE users SET username = :username WHERE id = :id'
        );
        foreach ($legacyUsers as $legacyUser) {
            $current = strtolower((string) $legacyUser['username']);
            if (preg_match('/^[a-z0-9._-]{3,50}$/', $current)) {
                continue;
            }

            $localPart = explode('@', $current, 2)[0];
            $base = trim((string) preg_replace('/[^a-z0-9._-]+/', '-', $localPart), '-._');
            $base = substr($base, 0, 40);
            if (strlen($base) < 3) {
                $base = 'user' . (int) $legacyUser['id'];
            }

            $candidate = $base;
            $suffix = 1;
            while (isset($usedUsernames[$candidate])) {
                $candidate = substr($base, 0, 40) . '-' . (int) $legacyUser['id'] . ($suffix > 1 ? '-' . $suffix : '');
                $suffix++;
            }

            $renameUser->execute([
                'id' => (int) $legacyUser['id'],
                'username' => $candidate,
            ]);
            $usedUsernames[$candidate] = true;
        }

        $pdo->exec('ALTER TABLE users MODIFY username VARCHAR(50) NOT NULL');
        echo "Migrated users.email to users.username.\n";
    }

    $pdo->beginTransaction();

    $permissionStatement = $pdo->prepare(
        'INSERT INTO permissions (name, slug)
         VALUES (:name, :slug)
         ON DUPLICATE KEY UPDATE name = VALUES(name)'
    );

    foreach ($permissions as $slug => $name) {
        $permissionStatement->execute(['name' => $name, 'slug' => $slug]);
    }

    $roleStatement = $pdo->prepare(
        'INSERT INTO roles (name, slug)
         VALUES (:name, :slug)
         ON DUPLICATE KEY UPDATE name = VALUES(name)'
    );
    $roleStatement->execute(['name' => 'Administrator', 'slug' => 'admin']);
    $roleStatement->execute(['name' => 'User', 'slug' => 'user']);

    $roleIds = $pdo->query(
        "SELECT slug, id FROM roles WHERE slug IN ('admin', 'user')"
    )->fetchAll(PDO::FETCH_KEY_PAIR);
    $permissionIds = $pdo->query(
        'SELECT slug, id FROM permissions'
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    $assignPermission = $pdo->prepare(
        'INSERT IGNORE INTO role_permissions (role_id, permission_id)
         VALUES (:role_id, :permission_id)'
    );
    $assignPermission->execute([
        'role_id' => (int) $roleIds['admin'],
        'permission_id' => (int) $permissionIds['sudo'],
    ]);

    foreach (['dashboard.view', 'settings.view', 'settings.update'] as $slug) {
        $assignPermission->execute([
            'role_id' => (int) $roleIds['user'],
            'permission_id' => (int) $permissionIds[$slug],
        ]);
    }

    $findAdmin = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $findAdmin->execute(['username' => $adminUsername]);
    $adminId = $findAdmin->fetchColumn();
    $adminCreated = $adminId === false;

    if ($adminCreated) {
        $createAdmin = $pdo->prepare(
            'INSERT INTO users (name, username, password, role_id)
             VALUES (:name, :username, :password, :role_id)'
        );
        $createAdmin->execute([
            'name' => $adminName,
            'username' => $adminUsername,
            'password' => password_hash($adminPassword, PASSWORD_DEFAULT),
            'role_id' => (int) $roleIds['admin'],
        ]);
    } else {
        $updateAdmin = $pdo->prepare(
            'UPDATE users SET name = :name, role_id = :role_id WHERE id = :id'
        );
        $updateAdmin->execute([
            'id' => (int) $adminId,
            'name' => $adminName,
            'role_id' => (int) $roleIds['admin'],
        ]);
    }

    $pdo->commit();

    echo "Database seeded successfully.\n";
    echo "Permissions: " . count($permissions) . "\n";
    echo "Admin username: {$adminUsername}\n";
    echo $adminCreated
        ? "Admin password: {$adminPassword}\nChange this password after your first login.\n"
        : "Admin account already existed; its password was not changed.\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Seeding failed: {$exception->getMessage()}\n");
    exit(1);
}
