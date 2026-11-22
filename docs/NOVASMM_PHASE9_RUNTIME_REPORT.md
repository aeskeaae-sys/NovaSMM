# NovaSMM Phase 9 — Runtime Report

## PHP / Composer

| Check | Status |
|-------|--------|
| PHP CLI | 8.3.6 present |
| Composer phar | Installed to `/tmp/composer` |
| `composer install --no-dev` | **PARTIAL** — started; required `--ignore-platform-req` for missing `ext-curl`, `ext-dom`, etc. |
| Full `vendor/` | **INCOMPLETE** in sandbox |
| `php artisan about` | **BLOCKED** (incomplete vendor / missing extensions) |
| `php artisan route:list` | **BLOCKED** |

## NPM / Vite

| Check | Status |
|-------|--------|
| Node | v24 present |
| `npm ci` / `npm run build` | **NOT TESTED** (not run to completion in Phase 9) |

## Required PHP extensions for real staging

`curl`, `dom`, `xml`, `mbstring`, `fileinfo`, `pdo_mysql`, `openssl`, `tokenizer`, `ctype`, `json`, `bcmath`, `gd` (recommended)

## Artisan

File present and PHP syntax OK (Phase 8). Runtime commands need complete vendor + extensions.

