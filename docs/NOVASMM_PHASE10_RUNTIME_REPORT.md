# NovaSMM Phase 10B — Runtime Report

## Environment facts

| Component | Value |
|-----------|--------|
| PHP | 8.3.6 |
| Present extensions | ctype, fileinfo, json, openssl, PDO, tokenizer, … |
| **Missing extensions** | **curl, dom, mbstring, xml, bcmath, pdo_mysql, gd** |
| Composer | Available via `/tmp/composer` |
| Node | v24.15.0 / npm 11.12.1 |
| `vendor/autoload.php` | **MISSING** (install incomplete) |

## Composer

- `composer install --no-dev` **cannot honestly PASS** without required extensions.
- Prior Phase 9 attempt used `--ignore-platform-req` → **not acceptable** as “Runtime PASS” per Phase 10 rules.
- Status: **BLOCKED**

## Artisan

| Command | Status |
|---------|--------|
| `php artisan about` | **BLOCKED** |
| `php artisan route:list` | **BLOCKED** |
| Config/view cache | **BLOCKED** |

## Required on staging host

Install: `php8.3-curl php8.3-xml php8.3-mbstring php8.3-bcmath php8.3-mysql php8.3-gd`  
Then: `composer install --no-dev --optimize-autoloader` (no platform ignore).

