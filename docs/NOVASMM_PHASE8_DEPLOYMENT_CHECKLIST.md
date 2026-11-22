# NovaSMM Phase 8 — Deployment Checklist

## A. Merge onto real full codebase (if not using this extract)

- [ ] Backup production/staging code + DB  
- [ ] Copy `public/assets/css/nova-theme.css`  
- [ ] Copy `public/assets/images/brand-logos/nova-*.svg`  
- [ ] Copy all merged Blade/Vue files from integration map  
- [ ] Ensure original `public/assets/libs` and `_assets` remain  

## B. Environment

- [ ] `APP_ENV=staging` or `production`  
- [ ] `APP_DEBUG=false`  
- [ ] `PRJ_DEMO_MODE=false`  
- [ ] Strong `APP_KEY`  
- [ ] Staging DB only (not production)  
- [ ] Payment/provider **sandbox** keys only  

## C. Build

- [ ] `composer install --no-dev --optimize-autoloader`  
- [ ] `npm ci && npm run build`  
- [ ] `php artisan storage:link`  
- [ ] `php artisan config:cache && route:cache && view:cache`  
- [ ] Permissions on `storage` and `bootstrap/cache`  

## D. Smoke tests (staging)

- [ ] Login / logout  
- [ ] Statistics page loads with Nova theme  
- [ ] New Order (Vue and/or classic) calculates price  
- [ ] Order history DataTable  
- [ ] Deposit pages render  
- [ ] Admin dashboard + users/orders tables  
- [ ] Ctrl+K search overlay  
- [ ] Notification empty state  
- [ ] Mobile 375px sidebar  

## E. Financial sandbox (staging only)

- [ ] Balance display  
- [ ] Test deposit pending/success path  
- [ ] Test order charge once  
- [ ] No double deduction  

## F. Go-live gate

- [ ] All Critical known issues accepted or closed  
- [ ] Rollback plan documented  
- [ ] Production approval by owner  

**Do not check “Production approved” until E and F are evidenced.**

