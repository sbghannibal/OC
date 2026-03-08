# PHP 8 + MySQL MVC Web Application

A lightweight PHP 8 MVC application for managing OC events with a public access-code flow and a secure admin dashboard.

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Composer

## Installation

### 1. Install PHP dependencies

```bash
composer install
```

### 2. Create the environment file

Copy the example and fill in your values:

```bash
cp .env.example .env
```

Edit `.env` with your MySQL credentials and base path:

```dotenv
APP_ENV=production
APP_BASE_PATH=/OC/public   # leave empty for root installs

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=admin_oc
DB_USERNAME=admin_oc
DB_PASSWORD=your-db-password

ADMIN_USERNAME=admin
ADMIN_PASSWORD=your-secure-password

# QR-link signing key (generate with: php -r "echo bin2hex(random_bytes(32));")
APP_SIGNING_KEY=your-random-signing-key
```

> **Never commit `.env` to version control.** It is already listed in `.gitignore`.

### 3. Create the MySQL database

```sql
CREATE DATABASE admin_oc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'admin_oc'@'localhost' IDENTIFIED BY 'your-db-password';
GRANT ALL PRIVILEGES ON admin_oc.* TO 'admin_oc'@'localhost';
FLUSH PRIVILEGES;
```

The application runs its own schema migration on first request (tables are created with `CREATE TABLE IF NOT EXISTS`).

### 4. Create the first admin user

```bash
php bin/create-user.php
```

The script reads `ADMIN_USERNAME` / `ADMIN_PASSWORD` from `.env` as defaults (press Enter to accept) or prompts you interactively. After setup you may remove those lines from `.env`.

### 5. Point your web server to `public/`

Configure your virtual host document root to `public/`. Example Apache block:

```apache
DocumentRoot /path/to/OC/public
<Directory /path/to/OC/public>
    AllowOverride All
    Require all granted
</Directory>
```

#### Apache – pretty URLs (mod_rewrite)

The file `public/.htaccess` is included in the repository and handles URL rewriting so that
routes like `/admin` and `/admin/login` work without the `index.php` prefix.

**Requirements:**
- `mod_rewrite` must be enabled (`sudo a2enmod rewrite` on Debian/Ubuntu).
- The `<Directory>` block for `public/` must have `AllowOverride All` (or at least `AllowOverride FileInfo`) so Apache reads the `.htaccess`.

> **Fallback:** Without mod_rewrite you can still reach all routes via `index.php/…`
> (e.g. `/OC/public/index.php/admin/login`), but the clean URLs will not work.

## Admin features

| Feature | Path |
|---------|------|
| Dashboard | `/admin` |
| Events | `/admin/events` |
| Create event | `/admin/events/new` |
| Registrations | `/admin/inschrijvingen` |
| CSV export | `/admin/inschrijvingen.csv` |
| User management | `/admin/users` |
| Audit log | `/admin/audit-log` |

## Public event features

| Feature | Path |
|---------|------|
| Event list | `/events` |
| Event detail | `/events/{slug}` |
| Registration form | `/events/{slug}/deelnemen` |
| QR-link bypass | `/events/{slug}/qr?ts=...&sig=...` |

## QR-link bypass

Generate a signed QR URL that allows one-step access to the registration form — no access code required:

```php
$ts  = time();
$sig = hash_hmac('sha256', $slug . '.' . $ts, getenv('APP_SIGNING_KEY'));
$url = '/events/' . rawurlencode($slug) . '/qr?ts=' . $ts . '&sig=' . $sig;
```

The link is valid for **7 days**. Set `APP_SIGNING_KEY` to a strong random secret in your `.env`.

## Payment tracking

The admin registrations page (`/admin/inschrijvingen`) shows payment status for each participant. Click the pencil icon to set:

- **Betaalstatus**: `Onbekend`, `Betaald`, or `Niet betaald`
- **Betaald op**: exact datetime of payment
- **Betaalopmerking**: free-form note

All payment fields are included in the CSV export.

## Multi-user accounts & audit log

Admin access is managed through **named user accounts** stored in the database — not a single shared password. Every admin action (login, logout, event creation, user management) is automatically written to the **audit log**, so you always know who did what and when.

To add more users, log in and navigate to **Gebruikers → Nieuwe gebruiker**, or run `php bin/create-user.php` from the command line again.

