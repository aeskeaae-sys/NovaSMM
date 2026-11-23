# NovaSMM — Installation Guide

For a **new machine** from a Git clone that includes commit `24b1255` (storage placeholders) or later.

## 1. Clone

```bash
git clone <YOUR_REPO_URL> NovaSMM
cd NovaSMM
git checkout main
git rev-parse --short HEAD   # expect 24b1255 or newer once pushed
```

## 2. PHP & Composer

```bash
php -v          # >= 8.2
composer -V     # 2.x
composer install --no-interaction
```

Confirm:

```bash
test -f vendor/autoload.php && echo OK
php artisan --version
```

## 3. Environment file

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` (examples only — use your own values):

```env
APP_NAME=NovaSMM
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=novasmm
DB_USERNAME=novasmm
DB_PASSWORD=        # set locally; never commit

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

For **local SQLite QA only**:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

```bash
touch database/database.sqlite
```

## 4. Database

```bash
php artisan migrate
```

Use seeders only if provided by the project and only with **test** data.

## 5. Frontend build (required for UI)

Without this step, pages that call `@vite` fail with:

`ViteManifestNotFoundException` / missing `public/build/manifest.json`

```bash
npm install
npm run build
```

If your network blocks the default registry:

```bash
npm install --registry https://registry.npmjs.org/
npm run build
```

Dev mode (optional):

```bash
npm run dev
```

## 6. Storage directories

After commit **24b1255**, a fresh clone should already contain:

- `storage/app`
- `storage/framework/cache` (+ `data`)
- `storage/framework/sessions`
- `storage/framework/views`
- `storage/logs`
- `bootstrap/cache`

Ensure the web/PHP user can **write** these paths. Do **not** use `chmod 777` in production.

## 7. Run locally

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000/login` — expect **HTTP 200** after build.

## 8. Common errors

| Symptom | Likely cause | Action |
|---------|--------------|--------|
| `valid cache path` | Old tree without storage placeholders | Upgrade to ≥ `24b1255`; do not only `mkdir` as the long-term fix |
| `Vite manifest not found` | No `npm run build` | Run frontend build |
| Composer platform errors | Missing PHP extensions | Install extensions listed in ENVIRONMENT_REQUIREMENTS |
| 500 on DB queries | Missing `.env` DB / empty DB | Configure DB + migrate |

## 9. What not to do

- Do not commit `.env`  
- Do not use production DB for experiments  
- Do not call live payment/provider APIs from local QA  
