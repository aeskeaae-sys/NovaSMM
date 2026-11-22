# NovaSMM Phase 11 — Database Report

- Staging `.env` template uses **sqlite** file for local experiment only.
- **No production DB used.**
- **No migrate:fresh / wipe.**
- Real staging should use separate MySQL database and credentials.
- `php artisan migrate` only after review of migration impact — **not run** without approval.

Status: **NOT TESTED** (no dedicated staging MySQL)

