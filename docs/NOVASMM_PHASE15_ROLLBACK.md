# Rollback

```bash
git fetch && git checkout <previous_commit>
composer install --no-dev
php artisan config:cache && php artisan view:cache
```

Database schema rollback only with explicit backup restore plan.

