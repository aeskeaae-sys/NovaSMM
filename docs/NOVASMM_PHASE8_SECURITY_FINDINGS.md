# NovaSMM Phase 8 — Security Findings

## Known issues status after integration

| ID | Finding | Integration action | Status |
|----|---------|-------------------|--------|
| K1 | Live regression not tested | Documented; requires real staging | **OPEN** |
| K2 | Incomplete worktree | Mitigated by extracting fuller core + merge | **REDUCED** (still missing vendor/public assets) |
| K3 | Trusted admin HTML `{!! !!}` | Not changed (would alter product behavior) | **OPEN** — accepted risk |
| K4 | Cron HTTP endpoints | Not changed without ops design | **OPEN** — ops hardening required |
| K5 | PRJ_DEMO_MODE | Found defaults in login + admin dashboard | **OPEN** — must set `false` in staging/prod `.env` |

## Additional findings in full extract

| Finding | Severity | Notes |
|---------|----------|-------|
| Login prefills `admin`/`demo123` when demo mode true | High if demo left on | Set `PRJ_DEMO_MODE=false` |
| Admin middleware blocks mutating methods in demo mode | Info | Existing protection |
| Notification model is config-like (`name/type/value`), not user inbox | Info | Confirms empty notification UI is correct |
| Secrets must not live in git | High (ops) | Use env / secret manager |

## Changes made in Phase 8 for security

**None** beyond carrying forward Phase 6 password-type fixes already in merged settings blades.

No authentication or authorization logic was modified.

