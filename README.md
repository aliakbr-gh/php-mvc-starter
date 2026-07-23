# Core MVC

Core MVC is a small, dependency-free MVC application foundation built with PHP 8.1+
and MySQL. It demonstrates clean URLs, routing, controllers, views, PDO models,
authentication, role-based access control, database activity history, global rate
limiting, daily file logs, CSRF protection, toast notifications, and searchable CRUD
modules.

The project does not use Composer or a backend framework. The interface uses
Bootstrap 5 components with a small local CSS and JavaScript layer.

For a focused explanation of the route → controller → model → view development
workflow, read `Project.md`.

## Features

- PHP 8.1+ with strict types
- No external packages
- MVC-style project structure
- `.php`-free URLs
- Front controller and custom autoloader
- GET and POST routing with dynamic parameters
- Reusable layouts and views
- PDO database connection with native prepared statements
- Registration, login, logout, and sessions
- Secure password hashing and verification
- CSRF protection on every POST route
- Normalized roles and permissions
- Backend permission middleware
- Permission-aware sidebar and action buttons
- Users, Roles, and Permissions CRUD modules
- Server-side search and pagination
- 10, 25, or 50 records per page
- Database-backed user activity history
- Global IP-based rate limiting
- Date-wise application logs
- Custom 403, 404, 419, 429, and 500 pages
- Flash messages displayed as toast notifications
- Lightweight top loader and page fade
- Global Laravel-style `dd()` helper

## Requirements

- PHP 8.1 or newer
- MySQL 5.7+ or MySQL 8+
- PDO MySQL PHP extension
- Apache with `mod_rewrite`
- Apache `AllowOverride All` for the project directory

This project is configured for MAMP, but it can run on any Apache/PHP/MySQL stack.

## Installation with MAMP

1. Place the project inside MAMP's document root:

   ```text
   /Applications/MAMP/htdocs/coremvc
   ```

2. Start Apache and MySQL from MAMP.

3. Create the database by importing:

   ```text
   database/schema.sql
   ```

   You can import it using phpMyAdmin, Adminer, MySQL Workbench, or the MySQL CLI.

4. Review the database configuration in `config/database.php`.

   The current fallback values are:

   ```text
   Host:     127.0.0.1
   Port:     3305
   Database: coremvc
   Username: root
   Password: root
   ```

5. Visit the project URL. Depending on the Apache port configured in MAMP, this may
   be:

   ```text
   http://localhost/coremvc/
   ```

   or:

   ```text
   http://localhost:8888/coremvc/
   ```

6. Register a normal account at `/register`.

7. Promote the first account to Administrator:

   ```sql
   UPDATE users
   SET role_id = (SELECT id FROM roles WHERE slug = 'admin')
   WHERE email = 'admin@example.com';
   ```

8. Log out and log in again so the new role and permissions are loaded into the
   session request.

> `database/schema.sql` is the fresh-install schema. Back up an existing database
> before changing its tables or importing schema changes.

## Environment configuration

The project reads configuration from server environment variables when available.
It falls back to the values in the configuration files.

Supported application variables:

```text
APP_NAME
APP_DEBUG
APP_TIMEZONE
```

Application name and branding are configured once in `config/config.php`:

```php
'name' => getenv('APP_NAME') ?: 'Core MVC',
'branding' => [
    'logo_path' => 'assets/images/logo.svg',
    'logo_alt' => getenv('APP_NAME') ?: 'Core MVC',
],
```

Page titles use `Page - APP_NAME`, for example `Login - Core MVC`. Set
`branding.logo_path` to a path relative to `public/`, or set it to `null` to hide
the logo.

Supported database variables:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

Example Apache environment configuration:

```apache
SetEnv APP_DEBUG false
SetEnv APP_TIMEZONE UTC
SetEnv DB_HOST 127.0.0.1
SetEnv DB_PORT 3306
SetEnv DB_DATABASE coremvc
SetEnv DB_USERNAME coremvc_user
SetEnv DB_PASSWORD strong-password
```

Never commit real production credentials into `config/database.php`.

## Project structure

```text
coremvc/
├── app/
│   ├── Controllers/        Request handlers
│   ├── Core/               Framework and application services
│   ├── Models/             PDO database models
│   └── Views/              Views, layouts, partials, and error pages
├── bootstrap/
│   └── app.php             Session and application bootstrap
├── config/
│   ├── config.php          App, rate limiter, log, and session settings
│   └── database.php        PDO connection settings
├── database/
│   └── schema.sql          Tables, default roles, and permissions
├── public/
│   ├── assets/             Local CSS and JavaScript
│   └── index.php           HTTP front controller
├── routes/
│   └── web.php             Application routes
├── storage/
│   ├── cache/              File-based rate limiter state
│   └── logs/               Daily application logs
├── .htaccess               Root rewrite and directory protection
└── README.md
```

## How a request works

For a request such as:

```text
GET /coremvc/admin/users?search=ali&per_page=25&page=2
```

the application lifecycle is:

1. Root `.htaccess` sends the request into the `public` directory without exposing
   `/public` in the browser URL.
2. `public/.htaccess` serves real assets directly and sends other requests to
   `public/index.php`.
3. `public/index.php` defines `BASE_PATH` and loads `bootstrap/app.php`.
4. The bootstrap registers the autoloader and global helpers, loads configuration,
   sets the timezone, configures the session, and configures daily logging.
5. `routes/web.php` registers all routes.
6. `Application` checks the global IP rate limit and writes the request log.
7. `Router` matches the HTTP method and URL.
8. The router checks CSRF and route middleware such as `auth` or
   `permission:users.view`.
9. The selected controller calls a model.
10. The model runs a prepared PDO query.
11. The controller returns a `Response` containing a rendered view or redirect.
12. The response sends its HTTP status, headers, and body.

## Clean URLs

Application routes do not contain `.php`:

```text
/login
/register
/dashboard
/admin/users
/admin/roles
/admin/permissions
```

Application source directories are blocked from public HTTP access. The browser
cannot directly request `app`, `bootstrap`, `config`, `database`, `routes`, or
`storage`.

For production, the recommended Apache virtual-host document root is the `public`
directory itself.

## Routing

Routes are defined in `routes/web.php`.

Basic GET route:

```php
$router->get('/reports', [ReportController::class, 'index']);
```

POST route:

```php
$router->post('/reports', [ReportController::class, 'store'], ['auth']);
```

Dynamic parameter:

```php
$router->get('/reports/{id}', [ReportController::class, 'show']);
```

The controller receives dynamic parameters in route order:

```php
public function show(string $id): Response
{
    // $id came from /reports/{id}
}
```

Available middleware rules:

```php
['auth']
['guest']
['role:admin']
['permission:users.view']
['auth', 'permission:users.create']
```

Every POST route is automatically checked against the session CSRF token.

## Controllers

Controllers live in `app/Controllers` and extend the base controller.

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

final class ReportController extends Controller
{
    public function index(): Response
    {
        return $this->view('reports/index', [
            'title' => 'Reports',
            'reports' => [],
        ]);
    }
}
```

Controller methods may return:

```php
return $this->view('reports/index', $data);
return Response::redirect(url('reports'));
return Response::json(['success' => true]);
return Response::html('<h1>Hello</h1>');
```

## Models and database queries

Models extend `App\Core\Model`. The base model creates one shared PDO connection
for the request.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Report extends Model
{
    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, title FROM reports WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }
}
```

PDO is configured with:

- Exception error mode
- Associative array fetch mode
- Native prepared statements
- UTF-8 MB4 connection encoding

Always bind user-supplied values. Do not concatenate request input into SQL.

## Views and layouts

Views live in `app/Views` and are normal PHP templates.

```php
return $this->view('reports/index', [
    'title' => 'Reports',
    'reports' => $reports,
]);
```

This loads:

```text
app/Views/reports/index.php
```

The default public layout is:

```text
app/Views/layouts/main.php
```

The authenticated dashboard layout is:

```text
app/Views/layouts/dashboard.php
```

Use the dashboard layout explicitly:

```php
return $this->view(
    'reports/index',
    ['title' => 'Reports', 'user' => Auth::user()],
    'layouts/dashboard'
);
```

Escape output that may contain user-controlled data:

```php
<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>
```

## Forms and CSRF protection

All POST forms must include the CSRF field:

```php
<form method="post" action="<?= htmlspecialchars(url('reports'), ENT_QUOTES, 'UTF-8') ?>">
    <?= csrf_field() ?>
    <input name="title" required>
    <button type="submit">Save</button>
</form>
```

If the token is missing or invalid, the router returns HTTP `419`.

## Authentication

Authentication is handled by `App\Core\Auth`.

Useful methods:

```php
Auth::check();
Auth::guest();
Auth::user();
Auth::attempt($email, $password);
Auth::login($userId);
Auth::logout();
Auth::hasRole('admin');
Auth::can('users.update');
```

Passwords are created with `password_hash()` and checked using
`password_verify()`. The session ID is regenerated on login and logout.

Registration always assigns the role whose slug is `user`.

## Role-based access control

RBAC uses four database relationships:

```text
users.role_id
      ↓
roles.id
      ↓
role_permissions.role_id + role_permissions.permission_id
      ↓
permissions.id
```

Each user has one role. Each role can have many permissions. A permission can belong
to many roles.

The schema seeds these roles:

```text
Administrator (admin)
User (user)
```

It also seeds CRUD permissions for the three administration modules:

```text
users.view           roles.view           permissions.view
users.create         roles.create         permissions.create
users.update         roles.update         permissions.update
users.delete         roles.delete         permissions.delete
```

The Administrator role initially receives all seeded permissions. The User role
receives no administration permissions.

### Protect backend routes

```php
$router->get(
    '/admin/reports',
    [ReportController::class, 'index'],
    ['auth', 'permission:reports.view']
);
```

### Hide unauthorized interface elements

```php
<?php if (Auth::can('reports.create')): ?>
    <a href="<?= htmlspecialchars(url('admin/reports/create'), ENT_QUOTES, 'UTF-8') ?>">
        Add report
    </a>
<?php endif; ?>
```

Hiding an interface element is not security by itself. Always protect the matching
route with permission middleware as well.

### Create a new permission

Permissions use a lowercase `module.action` slug:

```text
reports.view
reports.create
reports.update
reports.delete
```

Create permissions from `/admin/permissions`, then edit a role at `/admin/roles` and
select the permissions that role should receive.

## Administration modules

### Users

URL: `/admin/users`

The module can:

- Search by name, email, or role
- Create accounts
- Change names and email addresses
- Assign roles
- Optionally replace a password
- Delete accounts
- Prevent the current user from deleting their own account

### Roles

URL: `/admin/roles`

The module can:

- Search by role name or slug
- Create roles
- Assign multiple permissions
- Update role details and permission assignments
- Display permission and user counts
- Delete unused custom roles
- Protect the seeded `admin` and `user` roles from deletion

A role cannot be deleted while users are assigned to it because the database uses a
restricting foreign key.

### Permissions

URL: `/admin/permissions`

The module can:

- Search by permission name or slug
- Create permissions
- Update permissions
- Display the number of assigned roles
- Delete permissions

Deleting a permission automatically removes its role assignments through a cascading
foreign key.

## Server-side search and pagination

All three administration modules perform search and pagination in SQL rather than
loading the entire table into PHP.

Example query string:

```text
/admin/users?search=ali&per_page=25&page=2
```

Supported page sizes are:

```text
10
25
50
```

Invalid page sizes fall back to 10. Page numbers are normalized to a minimum of 1.
Search and page-size values are preserved in pagination links.

## Activity logging in MySQL

Business activity is stored in the `activities` table:

```text
id
activity
user_id
created_at
```

Examples include:

```text
Ali has been logged in from 127.0.0.1
Ali created user person@example.com from 127.0.0.1
Ali updated role Editor from 127.0.0.1
```

Write activity from any controller:

```php
use App\Core\ActivityLogger;

ActivityLogger::log(
    Auth::user()['name'] . ' exported a report from ' . Request::capture()->ip(),
    (int) Auth::user()['id']
);
```

The second argument is optional. When it is omitted, `ActivityLogger` uses the
currently authenticated user when possible.

Activity logging errors are written to the daily application log instead of breaking
the user request.

Regular users see only their own recent activity. Administrators see recent activity
from all users.

## Date-wise file logs

Technical request and error logs are separate from database activity history.

Logs are written to:

```text
storage/logs/app-YYYY-MM-DD.log
```

Example:

```text
[2026-07-21 19:30:10] INFO: Request {"method":"GET","path":"/dashboard","ip":"127.0.0.1"}
```

Configure logging in `config/config.php`:

```php
'logging' => [
    'enabled' => true,
    'path' => BASE_PATH . '/storage/logs',
    'filename' => 'app-{date}.log',
    'date_format' => 'Y-m-d',
],
```

Write a custom technical log:

```php
Logger::info('Report generated', ['report_id' => 10]);
Logger::warning('Invalid export request');
Logger::error('Export failed', ['exception' => $exception->getMessage()]);
```

## Global rate limiter

Every dynamic request passes through the global IP-based rate limiter before routing.
Static assets served directly by Apache do not enter PHP and therefore are not
counted.

Default configuration:

```php
'rate_limiter' => [
    'enabled' => true,
    'max_requests' => 60,
    'window_seconds' => 60,
    'block_seconds' => 120,
    'storage_path' => BASE_PATH . '/storage/cache/rate_limits',
],
```

With the default values, an IP may make 60 dynamic requests in 60 seconds. Additional
requests block that IP for 120 seconds. A blocked request receives HTTP `429` and a
`Retry-After` response header.

Limiter files use file locking so concurrent requests cannot overwrite each other's
counter updates.

To clear one development block, remove the corresponding JSON file from
`storage/cache/rate_limits`. Avoid clearing limiter state indiscriminately in
production.

## Flash messages and toasts

Set a message before returning a redirect:

```php
flash('success', 'Report created successfully.');
flash('error', 'The report could not be saved.');
flash('warning', 'Please log in to continue.');
```

The layout reads flash messages from the session and renders them as auto-dismissing
toast notifications. Flash messages are removed from the session after display.

## Debugging with `dd()`

The global `dd()` helper accepts one or more values, dumps them, and immediately stops
the request:

```php
dd($user);
dd($request->all(), Auth::user());
```

Do not leave `dd()` calls in production request paths.

## Error handling

The application provides these error responses:

| Status | Meaning |
|---|---|
| 403 | Authenticated but missing the required role or permission |
| 404 | No route matched the request |
| 419 | Missing or invalid CSRF token |
| 429 | Global rate limit exceeded |
| 500 | Unhandled application exception |

When `APP_DEBUG=true`, unhandled exceptions are displayed with a stack trace. When
debug mode is disabled, the visitor sees the generic 500 page and details are written
to the daily log.

## Security included in this starter

- Password hashing using PHP's current default algorithm
- Session ID regeneration during authentication changes
- HTTP-only, `SameSite=Lax` session cookies
- Secure cookies when HTTPS is active
- CSRF verification for POST requests
- Native PDO prepared statements
- Output escaping in views
- Backend permission enforcement
- Protected source and storage directories
- Directory listing disabled
- Rate limiting with atomic file locks
- Database foreign keys for RBAC integrity

This is a lightweight educational/application starter. Before handling sensitive or
high-value production data, add automated tests, stronger validation rules, account
recovery, email verification, audit retention policies, HTTPS enforcement, secure
secret management, database backups, and deployment monitoring.

## Creating another permission-controlled CRUD module

For a new `reports` module:

1. Create the table in a reviewed database migration or SQL file.
2. Add `app/Models/Report.php`.
3. Add `app/Controllers/ReportController.php`.
4. Add views under `app/Views/admin/reports`.
5. Create `reports.view`, `reports.create`, `reports.update`, and `reports.delete` in
   the Permissions module.
6. Assign those permissions to the appropriate roles.
7. Add routes with matching permission middleware.
8. Use `Auth::can()` around sidebar links and action buttons.
9. Add `ActivityLogger::log()` after successful create, update, delete, or other
   meaningful actions.
10. Use prepared SQL queries and include `csrf_field()` in POST forms.

## Production checklist

- Set `APP_DEBUG=false`
- Set production database credentials through server environment variables
- Serve the application over HTTPS
- Point the virtual-host document root to `public/`
- Restrict filesystem permissions for configuration and storage
- Ensure the PHP process can write only to required storage directories
- Configure log rotation and retention
- Back up MySQL regularly
- Use a dedicated database user with minimum required privileges
- Review rate-limit settings for expected traffic
- Add account recovery and email verification if required
- Add automated tests before deployment

## License

This project is provided as a starter structure. Add the license appropriate for your
application before redistribution.
