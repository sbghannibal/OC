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
| Inschrijvingen | `/admin/inschrijvingen` |
| Inschrijvingen CSV export | `/admin/inschrijvingen.csv` |
| User management | `/admin/users` |
| Audit log | `/admin/audit-log` |

## Public event pages & registration

| Feature | Path |
|---------|------|
| Event overview | `/events` |
| Event detail | `/events/{slug}` |
| Registration form | `/events/{slug}/deelnemen` |
| QR signed registration link | `/events/{slug}/qr?ts={unix_ts}&sig={hmac}` |

### Access-code gate

Visitors must enter the event access code (via `/toegang`) before they can register. After entering the correct code the session flag `access_ok_{slug}` is set and they are redirected to the registration form.

If a visitor arrives via a **signed QR-link** they bypass the manual access-code step: the link is verified and the session flag is set automatically.

### QR registration links

Signed QR links let you share a direct registration URL (e.g. printed on a flyer) that grants access to the form for a specific event without requiring a manual access-code entry.

**Setup:**

1. Add `APP_SIGNING_KEY` to your `.env` file (minimum 32 random hex characters):

   ```bash
   # Generate a secure key
   php -r "echo bin2hex(random_bytes(32));"
   ```

   ```dotenv
   APP_SIGNING_KEY=your-generated-key-here
   ```

2. Log in to the admin dashboard and navigate to **Inschrijvingen**. A fresh signed QR link (valid for 7 days) is displayed at the top of the page. Copy the link or encode it as a QR code.

**Link format:**

```
/events/{slug}/qr?ts={unix_timestamp}&sig={hmac_sha256}
```

- `ts` – Unix timestamp of when the link was generated
- `sig` – HMAC-SHA256 of `{slug}|{ts}` using `APP_SIGNING_KEY`
- Links expire after **7 days**
- Signature verification uses constant-time comparison (`hash_equals`)

> **Security:** Keep `APP_SIGNING_KEY` secret. Rotate the key to invalidate all existing links.

### Payment status tracking

The **Inschrijvingen** admin page shows all registrations for the current event. Each registration can be marked as:

- **Onbekend** (default) — payment status not yet checked
- **Betaald** — payment confirmed
- **Niet betaald** — payment outstanding

Changes are saved immediately via a per-row form. The CSV export includes the `payment_status` column.

## Multi-user accounts & audit log

Admin access is managed through **named user accounts** stored in the database — not a single shared password. Every admin action (login, logout, event creation, user management) is automatically written to the **audit log**, so you always know who did what and when.

To add more users, log in and navigate to **Gebruikers → Nieuwe gebruiker**, or run `php bin/create-user.php` from the command line again.

