# NovaSMM — Fresh Install Checklist

Use after cloning a commit that includes storage placeholders (`24b1255+`).

| # | Step | Command / check | Expected |
|---|------|-----------------|----------|
| 1 | Clone | `git clone … && cd …` | Repo present |
| 2 | HEAD | `git rev-parse --short HEAD` | `24b1255` or newer |
| 3 | Storage dirs | `ls storage/framework/views storage/logs` | Directories exist **without** manual mkdir |
| 4 | PHP version | `php -v` | ≥ 8.2 |
| 5 | Extensions | `php -m` | curl, dom, mbstring, … |
| 6 | Composer | `composer install --no-interaction` | `vendor/autoload.php` |
| 7 | Artisan | `php artisan --version` | Laravel 11.x |
| 8 | Env | `cp .env.example .env && php artisan key:generate` | Key set |
| 9 | Database | Configure DB + `php artisan migrate` | Migrations OK |
| 10 | npm | `npm install` | node_modules |
| 11 | Build | `npm run build` | `public/build/manifest.json` |
| 12 | Serve | `php artisan serve` | Listening |
| 13 | Login page | `curl -o /dev/null -w '%{http_code}' http://127.0.0.1:8000/login` | **200** |
| 14 | No cache-path error | Response body / logs | No “valid cache path” |

Mark each row PASS / FAIL / BLOCKED with evidence when running QA.
