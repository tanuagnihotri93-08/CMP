# Laravel CRM

An internal CRM for managing the lead-to-customer lifecycle, follow-up reminders, business activities, reports, and administrative access control.

## Features

- Authenticated dashboard with role-scoped CRM metrics and recent records.
- Lead and customer management, including conversion of won leads.
- Follow-up scheduling and completion.
- Lead/customer activity tracking.
- Filtered reports with Excel and PDF export; exports are audit logged.
- Append-only audit history for supported record, authentication, permission, and export events.
- Role-based permissions and record assignment.
- Responsive Blade UI and custom branded error pages.

## Requirements

- PHP 8.2 or newer, with Composer.
- Node.js and npm (versions compatible with the included lockfile).
- MySQL/MariaDB for production, or SQLite for isolated local/test environments.
- PHP extensions required by Laravel and the included packages: Ctype, cURL, DOM/XML, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PDO, PDO MySQL (for MySQL), Session, Tokenizer, and ZIP.
- A web server configured to serve the Laravel `public` directory, not the project root.

Check the installed PHP extensions against Composer's platform requirements with `composer check-platform-reqs`.

## Installation

1. Install dependencies and create a local environment file:

   ```bash
   composer install
   copy .env.example .env
   php artisan key:generate
   npm ci
   ```

2. Configure `.env` for the local database. For MySQL/MariaDB, set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. For SQLite, set `DB_CONNECTION=sqlite` and `DB_DATABASE` to the absolute SQLite file path.

3. Run migrations and, only for local development, the seeder:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

   The database seeder creates its sample account only when `APP_ENV=local`. It does not provision a production administrator. This project has no self-registration or user-creation screen; establish the first Super Admin through an approved, access-controlled provisioning process before production use. Do not use development/test credentials in production.

4. Build frontend assets and start the local server:

   ```bash
   npm run build
   php artisan serve
   ```

   Open the URL printed by `artisan serve`. During frontend development, `npm run dev` serves Vite assets.

5. If the application uses public uploads, create Laravel's public storage link:

   ```bash
   php artisan storage:link
   ```

## Architecture

- **Routes:** `routes/web.php` defines login/logout and authenticated CRM routes. Resource routes are protected by the `permission` middleware; administrative, audit, report, and export routes have dedicated permission gates.
- **Controllers:** `app/Http/Controllers` contains authentication, dashboard, leads, customers, follow-ups, activities, reports, audit logs, and administration controllers.
- **Models:** `app/Models` contains the Eloquent records and their relationships. `User` resolves role permissions and applies assigned-record access scopes.
- **Middleware:** `EnsurePermission` maps route actions to the corresponding permission and checks access to individual assigned records.
- **Authorization:** roles and permissions are stored in `roles`, `permissions`, and `permission_role`. The assignment fields on CRM records are `assigned_to_user_id`.
- **Audit:** `AuditLog` records supported actions; application behavior treats audit entries as append-only. Lead and customer detail pages show a permission- and assignment-scoped activity timeline with the recorded actor and timestamp.
- **Notifications:** Laravel database notifications are scoped to their recipient. The authenticated navbar dropdown displays unread status and allows users to mark one or all of their notifications as read.
- **Migrations:** `database/migrations` owns schema and indexes. Apply all pending migrations as part of deployment; do not edit production data/schema manually.
- **Views/assets:** Blade templates are under `resources/views`; frontend entry points are `resources/css/app.css` and `resources/js/app.js`, built with Vite.

### Main database records

- `leads`: name, company, email, phone, source, status, notes, assignment, and timestamps.
- `customers`: optional source `lead_id`, name, company, email, phone, address, status, assignment, and timestamps.
- `follow_ups`: lead, date, type, remark, status, assignment, and timestamps.
- `activities`: optional lead/customer links, type, subject, description, activity date, status, assignment, and timestamps.
- `users`, `roles`, `permissions`, `permission_role`, and `audit_logs` support authentication, authorization, and audit history.

## Roles

The built-in roles are **Super Admin**, **Manager**, and **Employee**. Actual access is controlled by the role-permission assignments in the database.

- **Super Admin:** full CRM and administrative permissions, including record deletion, reports and exports, audit logs, user access management, and role/permission management.
- **Manager:** company-wide record visibility and assignment; lead/customer create and update; follow-up and activity management; report viewing and report export. Manager does not have lead/customer deletion, audit-log, user-management, or role-management permission by default.
- **Employee:** assigned-record access; lead create/update, customer view, follow-up and activity create/update/complete, and dashboard access. Employee does not have customer creation, delete, reports, exports, audit logs, or administrative access by default.

## Modules

- **Leads:** manage prospects and pipeline status; a won lead can be converted into a customer.
- **Customers:** manage customer accounts and their originating lead relationship.
- **Follow Ups:** schedule lead reminders, monitor overdue items, and complete follow-ups.
- **Activities:** record calls, meetings, emails, notes, and tasks related to a lead or customer.
- **Activity Timeline:** review recent changes to a lead or customer, including visible related follow-ups and activities, with the user who performed each action.
- **Reports:** date/status/owner-filtered CRM summaries with Excel and PDF export. Excel exports use query chunks; PDF generation is limited to 1,000 matching records to protect application memory.
- **Audit Logs:** review recorded CRM, authentication, permission, and export events. Access is restricted by role.
- **Notifications:** a user is notified when a CRM record is assigned or reassigned to them; notification contents and read status are private to that user.

## CRM workflow

The normal sales workflow is **Leads → Customers → Follow Ups → Activities → Reports**:

1. Track a prospect and progress its lead status.
2. Convert a won lead into a customer, or manage a customer record according to the user's permissions.
3. Schedule follow-ups for lead communications and mark them complete when handled.
4. Record calls, meetings, emails, notes, or tasks against an accessible lead or customer.
5. Review filtered reports and export permitted data. Supported changes and exports are recorded in the audit history.

List screens use database filtering and 25-record pagination. Form relationship pickers load selected columns in 500-record chunks to reduce PHP memory use. For extremely large installations, replace those full option pickers with permission-aware asynchronous search endpoints before expecting fast form rendering in every browser.

## Testing

Run the Laravel feature and unit tests:

```bash
php artisan test
```

Build frontend assets:

```bash
npm run build
```

The latest project verification result is recorded after the final readiness pass: **56 tests passed (397 assertions)**; `npm run build` completed successfully.

## Production deployment checklist

Complete this checklist for each production environment. `.env.example` is a local-development template, not a production configuration; do not copy its `APP_DEBUG=true`, SQLite, or local URL settings into production.

### 1. Server and environment

- Use PHP 8.2 or newer and install the PHP extensions required by Composer and the configured database driver. Verify with `composer check-platform-reqs`.
- Configure Apache/Nginx to use the application's `public` directory as its document root. Never expose the project root, `.env`, logs, or private storage over HTTP.
- Configure valid TLS/HTTPS for the whole application and ensure the hosting proxy forwards HTTPS information correctly to Laravel.
- Create the production `.env` from a secret-managed deployment process and set at least:

  ```dotenv
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://<crm-host>
  APP_KEY=<unique-key-generated-for-this-environment>

  DB_CONNECTION=mysql
  DB_HOST=<database-host>
  DB_PORT=3306
  DB_DATABASE=<database-name>
  DB_USERNAME=<least-privilege-database-user>
  DB_PASSWORD=<secret-from-your-secret-manager>

  SESSION_DRIVER=database
  SESSION_SECURE_COOKIE=true
  SESSION_HTTP_ONLY=true
  SESSION_SAME_SITE=lax
  CACHE_STORE=database
  QUEUE_CONNECTION=database
  ```

- Generate `APP_KEY` once when provisioning a new environment (`php artisan key:generate`). Keep it secret and stable across deployments; changing it can make existing encrypted application data unreadable and invalidate sessions.
- Use an isolated, least-privilege database account and review the database's network/TLS policy. Configure production logging, alerting, mail delivery, and trusted proxy settings for the actual hosting environment.
- Keep `.env` and deployment secrets out of source control and backups accessible to untrusted users. Do not expose `phpinfo()`, debug pages, stack traces, or configuration dumps publicly.

### 2. Authentication and session security

- Keep `APP_DEBUG=false` in production; `config/app.php` otherwise enables detailed exception output when debug is enabled.
- The application uses Laravel's session-based `web` authentication. Login is rate-limited, session IDs are regenerated after successful login, and logout invalidates the session and regenerates the CSRF token.
- CRM and administration routes require authentication and their corresponding permission middleware. Keep those route protections in place; hiding a navigation item is not authorization.
- Keep Laravel CSRF protection enabled for the `web` routes and retain `@csrf` in state-changing forms. Do not add broad CSRF exclusions.
- Set the session cookie flags explicitly: `SESSION_SECURE_COOKIE=true` (HTTPS only), `SESSION_HTTP_ONLY=true` (not readable by browser JavaScript), and `SESSION_SAME_SITE=lax`. The application configuration defaults to HttpOnly and Lax and enables the secure flag in production, but production should set it explicitly.
- Use the database session driver for a single or multi-instance deployment with the database session table migrated; select a shared Redis/database backend for multiple app instances. `SESSION_ENCRYPT=true` may be enabled if required by the deployment's data-at-rest policy.

### 3. Database, backups, and migrations

- Before each release, identify the target database and review `php artisan migrate:status` and `php artisan migrate --pretend`. Check the reviewed migration plan against the release and maintenance window.
- Take a consistent, access-controlled database backup and back up any persistent uploaded files or other private/public storage used by the deployment. Store backups separately from the application host and protect them as sensitive CRM data.
- Verify backup completion and periodically test restoring into a separate environment. A backup that has not been restore-tested is not a verified recovery plan.
- Apply schema changes with `php artisan migrate --force` during the approved release procedure. Never use `migrate:fresh`, `db:wipe`, or destructive reset commands against production data.
- Confirm the migration status and application health after migration. The CRM listing-index migration must be applied for the optimized listing/report query paths. Coordinate rollback with a tested database recovery plan; do not assume every schema change is safely reversible.

### Database, cache, queue, and storage

- Select and provision the cache store deliberately (database or Redis are configured). Database cache requires its table to exist; a multi-instance deployment needs a shared cache backend.
- The current default queue connection is database. If queued work is used, provision its tables and run a monitored, supervised `php artisan queue:work` process with a restart policy. If no worker is deployed, queued jobs will not be processed.
- Grant the PHP/web-server identity write access only to the required Laravel runtime directories: `storage` (including logs, sessions/cache if file-backed, and app files) and `bootstrap/cache`. On Unix-like hosts, use deployment-specific ownership and restrictive group permissions (commonly directories `775`, files `664`); do not make the project world-writable. On Windows, set equivalent NTFS ACLs for the service identity.
- Keep private storage outside the web root. Create `public/storage` with `php artisan storage:link` only when public uploads are actually used, and verify the linked target contains no private data.

### 4. Build and release

Back up first and use a deployment/release procedure appropriate to the hosting setup. Do not reset or recreate the production database. A typical release sequence is:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate:status
php artisan migrate --pretend
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run `php artisan storage:link` only if the application needs public uploads. Rebuild configuration/view/route caches after changing deployment configuration; cached configuration is used at runtime.

After release, verify the health endpoint, login/logout, role-based access, CRM create/update flows, report and export permissions, application logs, and queue health (when enabled). Confirm no debug output or sensitive configuration is reachable. Roll back only through the deployment's tested application and database recovery procedure.

## Current deployment audit notes

The inspected workspace uses development-oriented settings in `.env.example` (including `APP_DEBUG=true`, a local URL, and SQLite); these are not production settings. The actual production `.env`, TLS, database access, backups, queue supervision, and host filesystem permissions must be verified on the deployment host. Local Windows directory presence does not verify production filesystem ownership or ACLs.
========================================================================================================================================================================
=========================================================================|{SECURITY LAYERS}|============================================================================

# Layer 1 — Authentication

- Login required
- Password hashing
- Session-based authentication
- Login ke baad session regenerate
- Logout par session invalidate

# Layer 2 — Authorization

- Roles (Super Admin, Manager, Employee)
- Permissions
- Middleware routes ko protect karta hai
- Unauthorized user ko access nahi milta

# Layer 3 — Route Protection

- Sensitive routes auth middleware ke andar
- Permission middleware se access control

# Layer 4 — CSRF Protection

- Forms me CSRF token
- Cross-Site Request Forgery attacks se protection

# Layer 5 — Session Security

- Secure session handling
- Login/logout par proper session management
- Production me Secure/HttpOnly/SameSite cookies use karne ki guidance

# Layer 6 — Password Security

- Passwords plain text me store nahi hote
- Hash form me database me save hote hain

# Layer 7 — Environment Security

- .env source control me commit nahi hoti
- Production me APP_DEBUG=false rakhna
- Secret keys code se alag

# Layer 8 — Input Validation

- Laravel request validation
- Invalid data database me jaane se pehle reject

# Layer 9 — Database Integrity

- Foreign keys
- Constraints
- Indexes (pending migration ke baad aur better performance)

# Layer 10 — Auditability

- Audit logs (agar enabled module use ho raha hai)
- Kis user ne kya action kiya, uska record
========================================================================================================================================================================
---------------------------------------------------------------------------|{USER email AND PASSWORDS...}|--------------------------------------------------------------
========================================================================================================================================================================
Role	                      Email pattern  	                              Password |   
__________________________________________________________________________________ |
Super Admin	                   admin@crm.com                              admin123 |
__________________________________________________________________________________ |
Manager	                    manager@crm.com  	                           manager123|
__________________________________________________________________________________ |
Employee	                 	employee@crm.com                           employee123 |
-----------------------------------------------------------------------------------+
                    CRM DATA LAYER
                         |
     -----------------------------------------
     |              |             |            |
   Leads          Tasks       Follow Ups   Activities
     |              |             |            |
     -----------------------------------------
                         |
                         ↓
            PERFORMANCE INTELLIGENCE ENGINE
                         |
        -------------------------------------
        |                 |                 |
   Score Engine      Trend Analysis     Insights
        |                 |                 |
        ↓                 ↓                 ↓
 Automatic Score     Analytics Data    Recommendations
        |
        |
  --------------------------------
  |              |               |
Lead Handling  Follow-up     Conversion
Task Completion Completion    Rate
                         |
                         ↓
              PERFORMANCE MANAGEMENT
                         |
        --------------------------------
        |              |              |
     Reviews       Feedback        Goals
        |              |              |
     Rating      Comments      Improvement
                         |
                         ↓
              EMPLOYEE DASHBOARD
                         |
                         ↓
              Notifications + History
------------------------------------------------------------------------------------------------------------------------------------------------------------------------
              
