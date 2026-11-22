# NovaSMM Phase 9 — Security Hardening

## K5 — PRJ_DEMO_MODE (**PASS** for code changes)

- `admin/dashboard.blade.php`: default `env('PRJ_DEMO_MODE', true)` → `false`
- `auth/login.blade.php`: removed `demo123` password prefill
- `.env.example`: `PRJ_DEMO_MODE=false`

## K4 — Cron HTTP (**PASS** optional hardening)

- New: `app/Http/Middleware/VerifyCronSecret.php`
- Registered alias `cron.secret` in `bootstrap/app.php`
- Applied to `Route::prefix('/schedule')->middleware('cron.secret')`
- Empty `CRON_SECRET` = backward compatible allow
- Set `CRON_SECRET` in production and pass via query or `X-Cron-Secret`

## K3 — Admin `{!! !!}` (**ANALYZED**, not global-replaced)

Primary sources: `Helper::getNotice`, admin post content, status HTML helpers.  
Global escaping would break product. Residual: compromised-admin XSS. Mitigate with admin access control + CSP.

## K1 / K2

Remain process/environment issues — not closed by Phase 9 alone.

## Files modified

- `app/Http/Middleware/VerifyCronSecret.php` (new)
- `bootstrap/app.php`
- `routes/web.php` (middleware only on schedule group)
- `resources/views/admin/dashboard.blade.php`
- `resources/views/auth/login.blade.php`
- `.env.example`

