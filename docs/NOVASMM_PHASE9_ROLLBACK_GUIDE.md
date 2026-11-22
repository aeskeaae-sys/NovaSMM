# NovaSMM Phase 9 — Rollback Guide

## UI files

Restore from `*.pre-nova.bak` (do not delete backups):

```bash
find . -name '*.pre-nova.bak' | while read b; do cp "$b" "${b%.pre-nova.bak}"; done
php artisan view:clear
```

## Cron middleware

1. Remove `->middleware('cron.secret')` from schedule group in `routes/web.php`
2. Revert `bootstrap/app.php` middleware alias block if desired
3. Delete `app/Http/Middleware/VerifyCronSecret.php`

## Demo mode UI

Restore login/dashboard from `.pre-nova.bak` if needed.

## Assets

Replace symlinked/copied `public` from original ZIP extract if theme breaks.

