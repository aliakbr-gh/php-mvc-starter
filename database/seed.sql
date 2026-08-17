-- PHP MVC Starter complete database reset for phpMyAdmin/shared hosting.
-- Select the target database in phpMyAdmin before importing this file.
-- WARNING: This deletes all existing application tables and their data.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS api_clients;
DROP TABLE IF EXISTS rate_limit_entries;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
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
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    client_id VARCHAR(100) NOT NULL UNIQUE,
    client_secret_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    token_version BIGINT UNSIGNED NOT NULL DEFAULT 1,
    last_authenticated_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rate_limit_entries (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, name, slug) VALUES
    (1, 'Product Owner', 'product-owner'),
    (2, 'Admin', 'admin');

INSERT INTO permissions (id, slug, name) VALUES
    (1, 'sudo', 'Sudo'),
    (2, 'dashboard.view', 'View dashboard'),
    (3, 'users.view', 'View users'),
    (4, 'users.create', 'Create users'),
    (5, 'users.update', 'Update users'),
    (6, 'users.delete', 'Delete users'),
    (7, 'roles.view', 'View roles'),
    (8, 'roles.create', 'Create roles'),
    (9, 'roles.update', 'Update roles'),
    (10, 'roles.delete', 'Delete roles'),
    (11, 'permissions.view', 'View permissions'),
    (12, 'permissions.create', 'Create permissions'),
    (13, 'permissions.update', 'Update permissions'),
    (14, 'permissions.delete', 'Delete permissions'),
    (15, 'settings.view', 'View application settings'),
    (16, 'settings.update', 'Update application settings'),
    (17, 'logs.view', 'View request logs'),
    (18, 'rate-limits.view', 'View rate limits'),
    (19, 'rate-limits.update', 'Update rate limits'),
    (20, 'database-backup.view', 'View database backups'),
    (21, 'database-backup.download', 'Download database backups'),
    (22, 'activity-logs.view', 'View activity logs');

-- Product Owner receives sudo, which grants every system capability.
INSERT INTO role_permissions (role_id, permission_id) VALUES
    (1, 1);

-- Admin receives all non-system permissions and read-only permission access.
INSERT INTO role_permissions (role_id, permission_id) VALUES
    (2, 2),
    (2, 3),
    (2, 4),
    (2, 5),
    (2, 6),
    (2, 7),
    (2, 8),
    (2, 9),
    (2, 10),
    (2, 20),
    (2, 21),
    (2, 22);

INSERT INTO users (id, name, username, password, role_id, is_active, session_version) VALUES
    (
        1,
        'Muhammad Ali Akbar',
        'sudo',
        '$2y$05$TYHDm2hQvPpxTqk/Dfh31OqN1xczy0dtRVu7.5/mLtgCbi4n8MZgC',
        1,
        1,
        1
    ),
    (
        2,
        'Admin',
        'admin',
        '$2y$05$6OpiNPG6GQsV6yTILXHut.zcXPYcaeo7b8KBwi3FdlGGmH0qdT6su',
        2,
        1,
        1
    );
