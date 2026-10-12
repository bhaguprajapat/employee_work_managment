# Employee Work Management

A Laravel application for daily work updates, task allocation, manager dashboards, and basic performance indicators.

- **Roles:** Admin, Manager, Employee
- **Stack:** PHP 8.2+, Laravel 12, Blade, Bootstrap 5, MySQL 8
- **Scope:** Only the assignment features (no payroll, leave, chat, or notifications)

---

## 1. Requirements

| Component | Version |
|-----------|---------|
| PHP | 8.2 or newer (8.3 recommended) |
| PHP extensions | `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml` |
| Composer | 2.x |
| MySQL | 8.0 or newer (MariaDB 10.6+ also works) |

## 2. Extract the project

Extract `employee-work-management.zip` to a folder, for example `C:\xampp\htdocs\employee-work-management` or `/var/www/employee-work-management`.

## 3. Install Composer dependencies

The ZIP does not include the `vendor/` folder or `composer.lock`. Install dependencies from the project root:

```bash
composer install
```

## 4. Configure the environment

```bash
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

Edit `.env` and set your database details:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=employee_work_management
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Keep `APP_DEBUG=false` on any server that is reachable by other people.

## 5. Create the database and schema

You need to do **one** of the following, not both.

### Option A: Import the SQL file (recommended for a quick start)

1. Create the database (skip if the SQL file creates it for you):
   ```sql
   CREATE DATABASE employee_work_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import `database/employee_work_management.sql`:
   - **phpMyAdmin:** select the database, open **Import**, choose the file, and click **Go**.
   - **Command line:** `mysql -u your_db_user -p < database/employee_work_management.sql`

The file contains the full schema and fills Laravel's `migrations` table, so `php artisan migrate` will report that nothing needs migrating.

> **Warning:** the SQL file drops and recreates the application tables. Do not import it into a database that already holds data you want to keep.

### Option B: Use Laravel migrations (fresh install)

```bash
php artisan migrate
```

Use this instead of Option A if you prefer Laravel to manage the schema. Do not import the SQL file as well.

## 6. Create the first administrator

No accounts are created automatically, so no default credentials exist. Create the first admin with:

```bash
php artisan app:create-admin
```

You will be asked for a name, email, and password. The password must be at least 8 characters and include upper and lower case letters and a number. To avoid typing it at the prompt, set `APP_ADMIN_PASSWORD` in your environment for that one command. Do not commit it to `.env`.

Managers are created by admins from **Employees → Add employee**, and employees are created by admins or managers.

## 7. Run the application

```bash
php artisan serve
```

Open `http://127.0.0.1:8000` and sign in with the admin account you created.

## 8. Run the automated tests

Tests use an in-memory SQLite database, configured in `phpunit.xml`, so they do not touch your MySQL data.

```bash
php artisan test
```

---

## Role summary

| Capability | Admin | Manager | Employee |
|------------|:-----:|:-------:|:--------:|
| Dashboard with team/company counts | ✔ | ✔ (team) | — (own tasks) |
| Create / edit employees | ✔ | ✔ (own team) | — |
| Assign employees to managers | ✔ | — | — |
| Activate / deactivate accounts | ✔ | ✔ (own team) | — |
| Allocate / edit tasks | ✔ | ✔ (own team) | — |
| Submit daily work updates | — | — | ✔ (own tasks) |
| Edit own work update | — | — | ✔ (same day only) |
| View performance indicators | ✔ | ✔ (own team) | ✔ (self) |

## Key business rules

- A task is **overdue** when its due date is before today and its status is not *Completed*. Completed tasks are never overdue.
- Employees can only submit updates for tasks assigned to them, and only for dates within the last 7 days, up to today.
- One work update per employee, task, and date.
- Performance indicators are factual counts and rates. The system does not make employment, pay, or disciplinary decisions.

## Project layout

```
app/Http/Controllers     Request handling (auth, dashboard, employees, tasks, work updates, performance)
app/Http/Requests        Server-side validation rules
app/Http/Middleware      Role gate and active-account check
app/Models               User, Task, DailyWorkUpdate
app/Policies             Record-level authorization
app/Services             WorkReportService: all dashboard and performance figures
app/Console/Commands     app:create-admin
database/migrations      Schema (matches the SQL file)
database/employee_work_management.sql   Schema import for fresh installs
resources/views          Blade templates
public/css/app.css       Custom styles on top of Bootstrap 5
tests/Feature            Access-control tests
```

## Troubleshooting

- **"No application encryption key has been specified"**: run `php artisan key:generate`.
- **SQLSTATE connection errors**: check `DB_*` values in `.env`, then run `php artisan config:clear`.
- **Blank page or 500 error**: set `APP_DEBUG=true` temporarily to see the message locally, then set it back to `false`. Logs are written to `storage/logs/laravel.log`.
- **Styles missing**: Bootstrap is loaded from a CDN, so the machine needs internet access.
