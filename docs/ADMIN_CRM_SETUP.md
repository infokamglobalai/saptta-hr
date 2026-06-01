# KAM Global HR — Admin CRM Setup

PHP + MySQL admin panel for managing website leads and newsletter subscribers.

## Requirements

- PHP 8.1+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.4+
- Apache/Nginx (or `php -S` for local dev)

## Quick start (local)

1. **Copy environment file**

   ```bash
   copy config\.env.example config\.env
   ```

   Edit `config/.env` with your MySQL credentials.

2. **Create database**

   Import `database/schema.sql` in phpMyAdmin, or run:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Run installer**

   Open in browser:

   ```
   http://localhost:8080/install.php
   ```

   Click **Run installation**. This creates tables and a default admin user.

4. **Start PHP server** (from project root)

   ```bash
   php -S localhost:8080
   ```

5. **Sign in to admin**

   ```
   http://localhost:8080/admin/login.php
   ```

   Default credentials (change in `config/.env` before install):

   - Email: `admin@kamglobalhr.com`
   - Password: `ChangeMe123!`

6. **Delete `install.php`** after setup on production.

## What gets captured

| Source | Stored in |
|--------|-----------|
| Contact form (`contact.html`) | `leads` table |
| Insights newsletter | `newsletter_subscribers` table |

Website forms POST to:

- `api/submit-lead.php`
- `api/newsletter.php`

If the CRM API is unavailable, forms fall back to FormSubmit (`info@kamgroups.com`).

## Admin features

- **Dashboard** — lead counts and recent inquiries
- **Leads** — search, filter by pipeline status, pagination
- **Lead detail** — view message, update status (new → contacted → qualified → proposal → won/lost), add internal notes
- **Newsletter** — list active subscribers

## Production deployment

1. Upload entire project including `admin/`, `api/`, `includes/`, `config/`.
2. Set `config/.env` on the server (never commit it).
3. Point your domain document root to this folder.
4. Ensure PHP can connect to MySQL.
5. Run `install.php` once, then remove it.
6. Use HTTPS and strong admin password.

## Troubleshooting: `Unsafe attempt to load URL ... from frame chrome-error://`

This Chrome message means the **parent page failed to load** (DNS, SSL, or server down), and something tried to open `/admin/` inside a frame or embedded preview.

**Fix:**

1. Open the admin in a **full browser tab** (not Cursor/VS Code preview or hosting iframe preview):
   ```
   https://kamglobalhr.com/admin/login.php
   ```
2. Confirm the main site loads first: `https://kamglobalhr.com/` — if you see a Chrome error page, fix hosting/DNS/SSL before admin will work.
3. **PHP is required** — static-only hosts (GitHub Pages, Netlify without PHP) cannot run `/admin/` or `/api/`. Use cPanel, VPS, XAMPP, or similar with PHP + MySQL.
4. Upload `config/.env`, run `install.php` once on the server, then delete `install.php`.
5. Test locally: `php -S localhost:8080` then visit `http://localhost:8080/admin/login.php`.

## Security notes

- Admin uses PHP sessions and `password_hash()` for credentials.
- CSRF tokens protect lead update forms in admin.
- API uses prepared statements (PDO).
- Block public access to `config/` via web server if possible.

## File structure

```
admin/           Admin UI (login, dashboard, CRM)
api/             Public JSON endpoints for forms
config/          .env (not in git)
database/        schema.sql
includes/        PHP bootstrap, auth, repositories
install.php      One-time setup (remove after use)
```
