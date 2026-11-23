# NovaSMM — Environment Requirements

## Runtime

| Component | Requirement | Notes |
|-----------|-------------|--------|
| PHP | **8.2+** (tested **8.3.6**) | Matches `composer.json` `^8.2` |
| Composer | 2.x | Lockfile: Laravel **11.44.0** |
| Node.js | 18+ recommended (tested **24.x**) | Vite 5 frontend build |
| npm | 9+ (tested **11.x**) | Or compatible with project lock if present |
| Database | MySQL/MariaDB **or** SQLite (local/QA only) | Production typically MySQL |

## Required PHP extensions

Install according to your OS. On Ubuntu 24.04 / PHP 8.3 example:

```bash
sudo apt-get update
sudo apt-get install -y \
  php8.3-cli php8.3-curl php8.3-xml php8.3-mbstring \
  php8.3-bcmath php8.3-gd php8.3-mysql php8.3-sqlite3
```

| Extension | Why |
|-----------|-----|
| curl | HTTP clients, PayPal SDK |
| dom / xml | Laravel / css-to-inline-styles |
| mbstring | String handling |
| bcmath | Numeric |
| gd | Images |
| pdo_mysql / mysql | Production DB |
| pdo_sqlite / sqlite3 | Optional local QA |
| openssl, tokenizer, fileinfo, ctype, json | Core Laravel |

**Do not** use `composer install --ignore-platform-reqs` to hide missing extensions in production.

## Optional / hosting

- Web server: Nginx or Apache pointing `public/` as document root  
- HTTPS in staging/production  
- Write permissions on `storage/` and `bootstrap/cache/` for the PHP user  

## Explicitly out of scope for this document

- Real payment gateway credentials  
- Production database passwords  
- OAuth / SMTP secrets  

Use `.env` on the server only; never commit secrets.
