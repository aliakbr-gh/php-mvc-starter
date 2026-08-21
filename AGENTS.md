# AGENTS.md

## Project purpose

This repository is a small, framework-free PHP 8 MVC administration starter. It provides session authentication, users, roles, permissions, application branding, request/activity logs, rate limiting, database backups, responsive navigation, pagination, and dark/light themes. Keep it dependency-free unless the user explicitly requests otherwise.

## Runtime and entry points

- Workspace root: `/Applications/MAMP/htdocs/php-mvc-starter`
- Web entry: `public/index.php`; root `index.php` forwards to it.
- Bootstrap: `bootstrap.php`
- Routes: `routes/web.php`
- Local database defaults: MySQL at `127.0.0.1:3305`, database `mvc_db`.
- Session idle timeout: `APP_SESSION_LIFETIME`, default 1800 seconds, minimum 60.
- PHP seeder: `php database/seed.php`.
- Migration CLI: `php migrate.php [migrate|status|rollback]`.
- Scaffold CLI: `php make [migration|controller|model] <name>`; multi-word names are normalized to lowercase underscores where applicable.
- Browser URL is normally `http://localhost/php-mvc-starter` under MAMP.

Do not run either seeder merely to validate a change. Both are destructive reset tools. A seed run requires clear user intent to reset data.

Production schema changes belong in timestamped files under `database/migrations`. Never edit or rename an applied migration; append a new one. Test both directions on a disposable database, and take a backup before production migration or rollback. Do not run a migration or rollback merely to validate code because those commands change the configured database.

## Architecture rules

- Controllers in `app/Controllers` orchestrate requests, validation, models/services, flash messages, and redirects.
- Models in `app/Models` own parameterized MySQL queries.
- Views in `app/Views` contain presentation logic only and render through `app/Views/layouts/app.php`.
- Framework services and global helpers live in `core`.
- Migration contracts and execution live in `core/Migration.php` and `core/MigrationRunner.php`; migration files contain schema changes only.
- All routes and middleware are declared in `routes/web.php`.
- Browser assets live in `public/assets`; uploads live in `public/uploads`.
- Mutable JSON/log state lives under `storage`, never in source files.
- Preserve `declare(strict_types=1);` in PHP classes and entry scripts.

## Request and security model

`public/index.php` bootstraps the application, registers request logging, applies rate limiting, loads routes, strips the configured base path, and dispatches through `Core\Router`.

- Every POST route is CSRF-checked centrally by `Core\Router`.
- Every state-changing form must include `<?= csrf_field() ?>`.
- Escape untrusted HTML output using `e()`.
- Use prepared statements for all user-controlled database values.
- Enforce access server-side with route middleware and/or controller guards. UI visibility is supplementary.
- Use `permission:slug` for assignable capabilities and `role:slug-one,slug-two` only for explicit built-in-role policies.
- `sudo` satisfies all permission checks through `Permission::userHasPermission()`.
- Bootstrap calls `enforce_session_security()` on browser requests. Preserve this check when changing authentication flow.
- Never expose database/configuration credentials or backup contents in logs or responses.
- Never trust query/form IDs without loading the corresponding record and enforcing protected-record rules.

## Authorization invariants

Built-in roles:

- `product-owner`: receives `sudo`, which grants every permission.
- `admin`: receives all permissions that are assignable to normal roles.

The Product Owner and Admin roles are immutable: do not allow editing, deletion, or permission reassignment. User accounts assigned to either role are also protected from editing and deletion in User Management. Preserve controller-level guards even if action links are hidden.

Every user record has `is_active` and `session_version`:

- Inactive users cannot log in and authenticated inactive users are logged out on their next request.
- Login stores the current `session_version` and `last_activity_at` in `$_SESSION['user']`.
- Every password update must increment `session_version` atomically with the password update.
- My Profile password changes must log the activity before incrementing the version, then end the current authenticated session and redirect to login.
- User Management password changes invalidate all sessions belonging to the affected user. If the editor changes their own password, end their session immediately.
- Idle session expiration is enforced against `config['session_lifetime']`; do not rely only on PHP garbage collection or cookie expiry.
- Use `end_authenticated_session()` for security-triggered logout so authentication data is cleared, the session ID rotates, and a flash explanation survives.

`App\Models\Permission::PRODUCT_OWNER_ONLY` is the protected/non-assignable permission registry. The Assign Permissions view obtains options from `Permission::allAssignable()`. Current protected slugs are:

- `sudo`
- `settings.view`, `settings.update`
- `email-settings.view`, `email-settings.update`
- `logs.view`
- `rate-limits.view`, `rate-limits.update`
- `permissions.view`, `permissions.create`, `permissions.update`, `permissions.delete`

Database backup permissions are assignable by design:

- `database-backup.view`: page/menu/dashboard visibility and page route.
- `database-backup.download`: all three download endpoints and controls.

Communication permissions:

- `email.view`: Email and SMS menu visibility and Send Email page route.
- `email.send`: Send Email submission.
- `sms.view`: Email and SMS menu visibility and Send SMS page route.
- `sms.send`: WhatsApp message redirect submission.
- `email-settings.view`, `email-settings.update`: protected email credential configuration, available only through `sudo`.

The Permissions index route is explicitly available to Product Owner and Admin. Permission mutation routes remain `sudo`-only.

When hiding table actions based on permission, role, protected-record status, or current-account status, render `—` with `aria-label="No actions available"` if no other action/status is displayed. This prevents unstable empty columns.

## Seed synchronization

`database/seed.php` and `database/seed.sql` must describe the same final database state:

- identical tables and constraints;
- identical permission slugs and display names;
- Product Owner assigned only `sudo`;
- Admin assigned every permission not in the protected registry;
- identical built-in users and known test passwords.
- identical `users.is_active` and `users.session_version` columns/defaults.

Whenever permissions change:

1. Update the `$permissions` map in `database/seed.php`.
2. Update `INSERT INTO permissions` in `database/seed.sql`.
3. Update the Admin assignments in `database/seed.sql` if the permission is assignable.
4. Update `$productOwnerOnlyPermissions` in `database/seed.php` and `Permission::PRODUCT_OWNER_ONLY` together when protection changes.
5. Update README authorization documentation.
6. Add a migration that inserts or updates the permission for existing installations, including the Admin assignment when it is assignable.

Do not renumber existing SQL seed permission IDs unnecessarily. Append new IDs to avoid confusing reviews and references.

## Data and storage

- Relational data: roles, permissions, role assignments, users, activity logs.
- Schema history: `migrations`, managed only by `Core\MigrationRunner`.
- Application branding: `storage/config/app-settings.json`.
- Rate-limit configuration: `storage/config/rate-limit.json`.
- Rate-limit state: `rate_limit_entries`, with one transactionally updated row per IP.
- Requests: `storage/logs/YYYY-MM-DD.log` as JSON lines.
- Exceptions: `storage/logs/errors-YYYY-MM-DD.log` as JSON lines.

Use `Core\JsonStore` for JSON state so locking/default behavior remains consistent. Do not manually edit mutable storage as part of unrelated changes.

## UI conventions

- Reuse `.page-heading`, `.subheading`, `.card`, `.filters`, `.table-wrap`, `.pagination-bar`, `.actions`, `.button`, and status badge styles.
- Use `Core\Paginator` and `app/Views/partials/pagination.php` for list pagination.
- Preserve relevant search/filter query parameters in pagination URLs.
- Tables must retain column count and show `—` where an action is unavailable.
- Use `data-native-select` only where the native select should not be enhanced.
- The shared page loader is reusable; it must not be removed after DOM ready.
- Do not trigger the loader for downloads, same-page hashes, new tabs, or UI-only buttons.
- The mobile sidebar uses `translate3d` on the x-axis; preserve reduced-motion support.
- Maintain both light and dark theme compatibility through tokens in `public/assets/theme.css`.
- Backup filenames and their preview must use `appFilenameSlug()`, which derives from the file-backed dynamic application name. Do not use the stable configured slug for download filenames.
- Google Drive backup credentials and refresh tokens live in `storage/config/google-drive-settings.json` through `Core\GoogleDriveSettings`. Configuration and OAuth connection routes are `sudo`-only; direct upload requires `database-backup.download`. Always delete temporary backup files after success or failure.

## Validation patterns

- Normalize IDs with `max(0, (int) ...)` and verify the record exists.
- Normalize usernames/slugs to lowercase where current controllers do so.
- Use `filter_var(..., FILTER_VALIDATE_IP)` for submitted IPs.
- Preserve flash messages and redirect-after-POST behavior.
- Log meaningful authenticated mutations with `Core\ActivityLogger`.
- File upload changes must use `Core\FileUploader` and clean up superseded/failed uploads safely.

## Verification

At minimum, run PHP syntax checks for every changed PHP file and `git diff --check`. For broad changes, run:

```bash
find app core config database routes -name '*.php' -print0 | xargs -0 -n1 php -l
git diff --check
```

There is no automated test suite. Perform proportionate manual checks, especially:

- authentication, logout, sessions, and CSRF;
- active/inactive login behavior and deactivation of an already logged-in account;
- password changes from Profile and User Management, including invalidation in another browser;
- idle session expiration using a short temporary `APP_SESSION_LIFETIME`;
- each middleware boundary using Product Owner, Admin, and a custom role;
- direct URLs for hidden/protected actions;
- protected built-in roles/accounts;
- search and pagination edge cases;
- loader behavior on links, forms, validation errors, back/forward navigation, and downloads;
- sidebar behavior on mobile widths;
- database, uploads, and full backup downloads;
- rate-limit pause/block/unblock behavior;
- production error display and log output.

## Change discipline

- Preserve user changes and unrelated worktree state.
- Prefer small, local edits over introducing abstractions that the project does not need.
- Do not add Composer, a framework, a build tool, or a frontend dependency without explicit approval.
- Do not perform destructive database resets, delete uploads/storage, or replace configuration without explicit user authorization.
- Update README and this file when architecture, permissions, seed behavior, or operational procedures change.
