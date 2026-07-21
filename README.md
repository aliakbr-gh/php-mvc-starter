# Core MVC

A small dependency-free MVC foundation for PHP 8.1+ with authentication, roles,
global rate limiting, daily logs, CSRF protection, flash toasts, and clean URLs.

## Run with MAMP

1. Ensure Apache's `mod_rewrite` is enabled and `AllowOverride All` applies to the document root.
2. Put this directory inside MAMP's `htdocs` (it already is in this workspace).
3. Import `database/schema.sql` into MySQL.
4. Check the credentials in `config/database.php`.
5. Visit `http://localhost/coremvc/` (include MAMP's Apache port if it is not port 80).

No `.php` or `/public` appears in application URLs. Add routes in `routes/web.php`, controllers in `app/Controllers`, models in `app/Models`, and views in `app/Views`.

## Authentication and roles

Registration creates a `user` account. Promote an account with:

```sql
UPDATE users
SET role_id = (SELECT id FROM roles WHERE slug = 'admin')
WHERE email = 'admin@example.com';
```

Protect routes with `['auth']`, `['guest']`, `['role:admin']`, or
`['permission:users.view']` as shown in `routes/web.php`. Use
`Auth::can('users.create')` to conditionally show interface elements.

The Users, Roles, and Permissions modules include CRUD operations, database-side
search, pagination, and 10/25/50 page-size controls. A role receives permissions
through `role_permissions`; every user belongs to one role.

For an existing installation created with the old role column, run
`database/rbac_migration.sql` once. For a fresh installation, import
`database/schema.sql` instead.

## Configuration

Edit `config/config.php` to manage the application-wide request count, rate window,
two-minute block duration, timezone, and date-wise log filenames. Logs are written to
`storage/logs/app-YYYY-MM-DD.log`; limiter state is stored under
`storage/cache/rate_limits`.

Controller activity is stored in the `activities` database table through
`ActivityLogger::log('Activity text', $userId)`. Regular users see their own recent
activity on the dashboard, while administrators see activity from all users.

Use `dd($value)` anywhere after the bootstrap has loaded to dump one or more values
and stop execution.

For production, set `APP_DEBUG=false`, use environment variables for database
credentials, and point the virtual host directly at `public/` where possible.
