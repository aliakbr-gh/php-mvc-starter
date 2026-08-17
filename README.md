# PHP MVC Starter

A framework-free PHP 8 MVC administration starter with authentication, role-based access control, activity and request logging, configurable rate limiting, application branding, and database/upload backups. It runs without Composer dependencies and supports MAMP, standard Apache hosting, and subfolder installations.

## Requirements

- PHP 8.1 or newer
- MySQL 8 or MariaDB with `mysqli`
- Apache with `mod_rewrite`
- PHP extensions: `mysqli`, `json`, and `zip`
- Write access to `storage/` and `public/uploads/`

## Quick start with MAMP

1. Place the project in MAMP's `htdocs` directory.
2. Configure the application in `config/app.php` and the database in `config/database.php`, or provide the environment variables described below.
3. Start Apache and MySQL.
4. From the project root, run:

   ```bash
   php database/seed.php
   ```

5. Open the detected project URL, typically `http://localhost/lab360`.
6. Sign in with one of the seeded accounts and change its password immediately:

   | Role | Username | Password |
   |---|---|---|
   | Product Owner | `sudo` | `sudo123` |
   | Admin | `admin` | `admin123` |

`database/seed.php` is destructive: it drops and recreates the configured database. For phpMyAdmin or hosting without shell access, select the target database and import `database/seed.sql`; that file drops and recreates all application tables.

## Configuration

`config/app.php` controls application identity and runtime behavior:

- `name`: fallback display name before file-backed settings load.
- `slug`: stable session-name prefix and browser theme-storage key.
- `base_url`: read from `APP_URL`, otherwise detected from the document root.
- `session_lifetime`: idle session timeout in seconds, read from `APP_SESSION_LIFETIME`; defaults to 1800 seconds (30 minutes) and has a 60-second minimum.
- `environment`: read from `APP_ENV`, defaulting to `development`.
- `debug`: read from `APP_DEBUG`; development defaults to enabled.
- `timezone`: read from `APP_TIMEZONE`, defaulting to `Asia/Karachi`.
- `api`: JWT issuer, audience, signing secret, and access-token lifetime.
- `request_logging`: enables the JSON-lines request logger.
- `bcrypt_cost`: password hashing cost.

`config/database.php` accepts these environment variables:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

Local defaults target MAMP on macOS (`127.0.0.1:3305`, database `lab360_db`, username/password `root`). Do not commit production credentials.

API environment variables:

```text
APP_JWT_SECRET       Required random secret of at least 32 characters
APP_API_ISSUER       Token issuer; defaults to the application slug
APP_API_AUDIENCE     Token audience; defaults to <application-slug>-api
APP_API_TOKEN_LIFETIME Access-token lifetime in seconds; defaults to 900
```

Generate a production signing secret with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"` and keep it outside source control. Applications consuming the API receive only their client credentials; they never receive the signing secret.

## Request lifecycle

1. Apache routes requests through the root or public `.htaccess` file to `public/index.php`.
2. `bootstrap.php` loads configuration, helpers, PSR-4-style autoloading, secure session settings, exception handling, and file-backed application settings.
3. For an authenticated browser session, bootstrap verifies idle expiry, active account status, and the database-backed session version.
4. `RequestLogger` registers a shutdown logger and `RateLimiter` checks the client IP.
5. `routes/web.php` and `routes/api.php` register browser and versioned API routes.
6. `Core\Router` normalizes the path, verifies CSRF on browser POST requests, runs middleware, and invokes the controller. Stateless API routes authenticate with bearer tokens instead of browser CSRF tokens.
7. Controllers validate input, call models/services, set flash messages, and render a view through the shared layout.
8. The layout supplies navigation, the reusable page loader, toast messages, responsive styling, and theme behavior.

## Architecture

```text
app/
  Controllers/       HTTP orchestration, validation, redirects, activity events
  Models/            MySQL queries for users, roles, permissions, activity logs
  Views/             PHP templates, layouts, partials, and error pages
config/
  app.php             Runtime/application configuration
  database.php        MySQL connection configuration
core/
  Router.php          Route dispatch and middleware execution
  Controller.php      Shared view rendering and redirects
  Database.php        Shared mysqli connection
  functions.php       URL, escaping, CSRF, auth, role, and permission helpers
  AppSettings.php     File-backed branding settings
  RateLimiter.php     File-backed per-IP throttling
  RequestLogger.php   JSON-lines HTTP request logs
  ActivityLogger.php  Database-backed user activity audit log
  DatabaseBackup.php  SQL and ZIP backup generation
  JsonStore.php       Locked JSON persistence helper
  Paginator.php       Shared search/page request and response shape
  ExceptionHandler.php Production-safe error handling and logging
database/
  schema.sql          Tables only; no roles, permissions, or users
  seed.php            CLI full database reset using current configuration
  seed.sql            phpMyAdmin/shared-hosting full table reset
public/
  index.php            Web entry point
  assets/              CSS and JavaScript
  uploads/             Uploaded branding files
routes/
  web.php              Complete route and middleware map
  api.php              Versioned JSON API routes
storage/
  config/              Application settings and rate-limit settings
  cache/               Mutable rate-limit state
  logs/                Request and exception logs
```

The root `index.php` forwards to `public/index.php`, allowing the repository root to act as the document root. On production hosting, prefer `public/` as the document root when the host permits it; otherwise retain the included root `.htaccess` protections.

## Authentication and authorization

Authentication is session-based. Passwords use PHP bcrypt, POST requests require a CSRF token, and authorization is always enforced by route middleware or controller guards—not only by hiding UI controls.

Each user has two security fields:

- `is_active`: inactive accounts cannot sign in; an already authenticated inactive user is signed out on the next request.
- `session_version`: copied into the session at login and incremented whenever the password changes. A mismatch signs out every existing session for that account, including sessions in other browsers.

Authenticated sessions also store their last activity time. After `APP_SESSION_LIFETIME` seconds without a request, bootstrap clears authentication and requires a new login. Password changes through either My Profile or User Management immediately sign out the affected account; a user changing their own password is redirected to login after the successful update.

Middleware forms used in `routes/web.php`:

- `auth` and `guest`
- `permission:permission.slug`
- `role:role-one,role-two`

`sudo` satisfies every permission check. The seeded Product Owner has only `sudo`; the seeded Admin receives every assignable permission. The Permissions list and Database Backup rules also use explicit role/permission middleware where required.

Protected system permissions do not appear in Assign Permissions:

- `sudo`
- `settings.view`, `settings.update`
- `logs.view`
- `rate-limits.view`, `rate-limits.update`
- `permissions.view`, `permissions.create`, `permissions.update`, `permissions.delete`

Database backup access is intentionally assignable:

- `database-backup.view` opens the backup page.
- `database-backup.download` authorizes each SQL/ZIP download endpoint.

The Product Owner and Admin roles cannot be edited, deleted, or have permissions reassigned through the UI. Accounts currently assigned to either built-in role cannot be edited or deleted from User Management.

## Modules

- **Dashboard:** displays modules allowed for the current account.
- **Users:** searchable, paginated account management with role assignment, active/inactive status, and password-triggered session invalidation.
- **Roles:** searchable, paginated custom-role management and permission assignment.
- **Permissions:** Admin/Product Owner read access; mutation requires `sudo`.
- **Application Settings:** branding name, logo, and favicon stored in JSON/uploads.
- **Request Logs:** daily JSON-lines request inspection with pagination.
- **Activity Logs:** database-backed audit trail of authenticated actions.
- **Rate Limits:** JSON-backed configuration plus database-backed, searchable tracked-IP state.
- **Database Backup:** database SQL, uploads ZIP, or full ZIP downloads.
- **Health:** public server/database health response.
- **Profile:** current-account details and password change.
- **API:** versioned JSON requests/responses, machine clients, and native HS256 JWT authentication.

## Versioned JSON API

API routes live under `/api/v1/`; future versions can use separate controllers and `/api/v2/` routes without changing v1 consumers. Every response contains only `success`, `message`, and `data`. HTTP status codes are sent through the HTTP response and are not duplicated in JSON. Failed responses always return `null` for `data`.

For an existing database, add the API table without resetting any data:

```bash
php database/migrate-api.php
```

Create a machine client and save the displayed secret immediately. Generated client secrets have high entropy and are stored as SHA-256 hashes; the plaintext secret cannot be retrieved:

```bash
php database/create-api-client.php "PMC Website"
```

Databases created by an earlier API-layer revision may retain an unused `scopes` column. Client creation remains compatible with that column, so no destructive schema change is required.

Clients created by an earlier revision with bcrypt secret hashes are upgraded transparently to SHA-256 after their next successful authentication. That first request retains the old bcrypt verification cost; later token requests use the faster hash comparison.

Request an access token:

```bash
curl -X POST http://localhost/php-mvc-starter/api/v1/auth/token \
  -H 'Content-Type: application/json' \
  -d '{"client_id":"CLIENT_ID","client_secret":"CLIENT_SECRET"}'
```

Call a protected endpoint:

```bash
curl http://localhost/php-mvc-starter/api/v1/example \
  -H 'Authorization: Bearer ACCESS_TOKEN' \
  -H 'Accept: application/json'
```

Define endpoints in `routes/api.php` with both API middleware entries:

```php
$router->get(
    '/api/v1/doctors/{id}',
    [DoctorController::class, 'show'],
    ['api-auth']
);
```

API controllers use `Core\API\Request` for JSON, query strings, headers, bearer tokens, client IPs, and route parameters. They use `Core\API\Response` for consistent success, created, validation, authentication, authorization, not-found, and error responses. API POST routes do not use browser sessions or CSRF; HTTPS is mandatory in production.

## Database and seeds

The relational schema contains:

- `roles`
- `permissions`
- `role_permissions`
- `users`, including active status and a password-session version
- `activity_logs`
- `api_clients`, including hashed client secrets, active state, and token version

Use `database/schema.sql` only when you want empty tables. Use one complete seed method—not both—for a fresh test environment:

```bash
php database/seed.php
```

or import `database/seed.sql` into an already selected database. Both seed paths create the same permissions, built-in roles, role assignments, and default accounts. When adding a permission, update both seed files and the protected-permission registry in `app/Models/Permission.php` when applicable.

## File-backed state

- `storage/config/app-settings.json`: display name, logo, favicon.
- `storage/config/rate-limit.json`: enabled flag and throttle thresholds.
- `storage/cache/rate-limit-state.json`: IP counters, violations, pauses, blocks.
- `storage/logs/YYYY-MM-DD.log`: one request JSON object per line.
- `storage/logs/errors-YYYY-MM-DD.log`: exception details.

These paths must be writable by PHP and inaccessible from the web. Apache denial rules are included. Do not treat these files as source-controlled fixtures during normal development.

## Frontend behavior

`public/assets/theme.css` defines light/dark design tokens. `style.css` contains layout and component styles. `theme.js` handles theme persistence, responsive sidebar state, navigation groups, and the user menu. The shared loader appears during initial DOM loading, form submissions, and real page navigation, but skips downloads and UI-only controls. Searchable selects are progressively enhanced by `searchable-select.js`.

The responsive sidebar uses an x-axis `translate3d` transition. Empty permission-dependent table action cells render an em dash so table layout remains stable.

Backup filenames use a safe lowercase slug generated from the current dynamic application name. For example, changing the Application Settings name to `Lab 360 Portal` produces filenames beginning with `lab-360-portal-`. The stable configured application slug continues to identify sessions and browser theme storage.

## Security notes

- Keep `APP_DEBUG=0` in production.
- Set a unique `APP_JWT_SECRET` of at least 32 random characters and rotate client credentials if exposed.
- Change seeded passwords before exposing the application.
- Use HTTPS so the session cookie receives the Secure flag.
- Set `APP_SESSION_LIFETIME` to the required idle timeout in seconds; 1800 is the default.
- Keep `storage/`, configuration, and database files outside public access.
- Grant database backup permissions sparingly; backups contain sensitive data.
- The application trusts `REMOTE_ADDR` and does not parse proxy forwarding headers.
- Backups are generated in temporary files and streamed to the authorized user.
- All state-changing forms must include `csrf_field()`.
- Escape untrusted output with `e()`.

## Shared hosting deployment

Upload the project to the domain document root, retaining `.htaccess`. Configure `DB_*`, `APP_ENV=production`, `APP_DEBUG=0`, `APP_TIMEZONE`, and optionally `APP_URL`. Ensure Apache rewrite support and PHP write permission for `storage/` and `public/uploads/`. Select the hosting database in phpMyAdmin and import `database/seed.sql` once. Hosting database names and usernames are commonly account-prefixed; use the exact values from the hosting control panel.

## Verification checklist

There is currently no automated test suite. For every change:

```bash
find app core config database routes -name '*.php' -print0 | xargs -0 -n1 php -l
git diff --check
```

Then manually test login/logout, inactive-user rejection, deactivation of an existing session, password changes from both modules, cross-browser session invalidation, idle expiry, CSRF failure behavior, role and permission boundaries, direct protected URLs, pagination/search query preservation, mobile navigation, light/dark themes, file uploads, rate limiting, logs, and all three backup downloads.
