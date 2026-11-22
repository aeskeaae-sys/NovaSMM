# NovaSMM Phase 10 — Rollback Guide

1. Restore Blade/Vue from `*.pre-nova.bak`  
2. Remove cron middleware from schedule routes if needed  
3. Revert dashboard/login demo-mode UI from backups  
4. Restore `public/` from original ZIP if assets broken  
5. `php artisan view:clear && config:clear`  

Do not delete `*.pre-nova.bak` until production is stable.

