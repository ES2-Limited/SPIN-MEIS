# Deploying SPIN MEIS 2.0 on cPanel

## Prerequisites
- PHP **8.3+** (with `pdo_pgsql` or `pdo_mysql`, `mbstring`, `intl`, `zip`, `gd`, `opcache`)
- PostgreSQL 16 (or MySQL 8 if configured in `.env`)
- Node.js 20+ (to run `npm ci && npm run build` before or during deploy)

## Steps
1. **Clone / Git Version Control in cPanel:**
   - Point cPanel Git Version Control to `https://github.com/ES2-Limited/SPIN-MEIS.git` (`main` branch).
   - `.cpanel.yml` is included at the repo root for automated deployment tasks.
2. **Document Root:**
   - Point your domain/subdomain Document Root to `<app-dir>/public` (so `public/.htaccess` and `public/index.php` handle all requests).
3. **Environment (`.env`):**
   - Copy `.env.production.example` to `.env` and set `APP_KEY` (`php artisan key:generate`), `APP_URL`, `DB_*`, and `MAIL_*`.
4. **Database & Catalogue:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=RolesAndPermissionsSeeder --force
   php artisan spin:import-catalogue
   ```
5. **Cron Job (for scheduler & database queue):**
   ```cron
   * * * * * cd /home/<user>/spin-meis && php artisan schedule:run >> /dev/null 2>&1
   * * * * * cd /home/<user>/spin-meis && php artisan queue:work --stop-when-empty --tries=3 >> /dev/null 2>&1
   ```
