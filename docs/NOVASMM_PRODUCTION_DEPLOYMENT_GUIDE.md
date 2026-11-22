# NovaSMM — Production Deployment Guide

**Important:** Merge rebrand files into the **full** original SMMPanel codebase first. This worktree is UI-focused and incomplete alone.

---

## 1. Server Requirements

- PHP **8.2+** with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `gd` or `imagick`
- Composer 2.x  
- Node.js **18+** (build only; not required at runtime if assets pre-built)  
- MySQL 8 / MariaDB 10.6+ (or existing project DB)  
- Nginx or Apache  
- Redis recommended for cache/queue/session  
- SSL certificate (HTTPS required)

---

## 2. Pre-Deploy Checklist

1. Full project backup (code + database)  
2. Staging environment with copy of DB (anonymized if required)  
3. `APP_DEBUG=false`, `APP_ENV=production`  
4. `PRJ_DEMO_MODE=false`  
5. Unique `APP_KEY`  
6. Strong DB password; never commit `.env`  
7. Payment/provider keys only in env/settings, not in git  

---

## 3. Merge Rebrand Files

Copy from worktree into full project (preserve paths):

```
public/assets/css/nova-theme.css
public/assets/images/brand-logos/nova-icon.svg
public/assets/images/brand-logos/nova-logo.svg
resources/views/** (all modified Blade from Phase 4–6)
resources/js/modules/services/form/views/Index.vue
docs/NOVASMM_PHASE*.md
```

Do **not** delete original `public/assets/libs` or `_assets` theme files.

---

## 4. Install & Build

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env   # if new; else use existing production env
# Edit .env: APP_URL, DB_*, CACHE_DRIVER, QUEUE_CONNECTION, SESSION_*
php artisan key:generate   # only if new key needed

npm ci
npm run build

php artisan migrate --force   # only if pending migrations exist and approved
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Permissions:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

---

## 5. Scheduler & Queue

Crontab:

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Ensure original cron HTTP routes still match your process:

- Order place/update  
- Deposit check/callbacks  

Protect callback URLs (firewall, secret query token, or provider IP allowlist) per your ops policy.

Queue worker (if used):

```
php artisan queue:work --sleep=3 --tries=3
```

Use Supervisor in production.

---

## 6. Web Server

- Document root: `public/`  
- Force HTTPS  
- Deny access to `.env`, `storage` except via symlink, and source maps if sensitive  

---

## 7. Post-Deploy Smoke Test (Staging then Prod)

1. Login / logout  
2. Open Statistics, New Order, submit **test** order on sandbox provider only  
3. Order history loads  
4. Deposit page renders (no real payment)  
5. Admin dashboard numbers load  
6. Admin users/orders tables load  
7. Ctrl+K search overlay  
8. Mobile width 375px navigation  

---

## 8. Rollback

1. Restore previous code release (git tag / previous artifact)  
2. Restore DB backup if schema changed (rebrand should not require schema change)  
3. `php artisan config:clear && php artisan view:clear`  
4. Reload PHP-FPM  

---

## 9. Secrets

Never place real API keys, DB passwords, or payment secrets in markdown or git. Use server env or a secrets manager.

