# NovaSMM Phase 14.17 — Fresh Install Hardening + Authenticated Workflow QA

**Source HEAD:** `d619f8a`  
**Git commit/push:** **Not performed** (forbidden)

---

## 1. Pre-flight

| Check | Result |
|-------|--------|
| Source HEAD | `d619f8a` **MATCH** |
| Prior Phase 14.16 | Public login/register browser **PASS**; SQLite migrate **PASS** on temporary clone |
| This phase environment | **Unstable** — `/tmp` clones and PHP extensions reset between steps |

---

## 2. Fresh install directory root cause

| Finding | Evidence |
|---------|----------|
| Laravel expects empty dirs with `.gitignore` under `storage/framework/{views,sessions,cache}`, `storage/logs`, `storage/app` | Standard Laravel 11 layout |
| Tracked in Git **before** this phase | Only `bootstrap/cache/.gitignore` reliably listed by `git ls-files` |
| Effect on clean clone | Missing view cache path → HTTP **500** “Please provide a valid cache path” until `mkdir` |

### Proposed source fix (working tree only — **not committed**)

Created placeholder files on disk under source:

```
storage/app/.gitignore
storage/framework/.gitignore
storage/framework/cache/.gitignore
storage/framework/cache/data/.gitignore
storage/framework/sessions/.gitignore
storage/framework/views/.gitignore
storage/logs/.gitignore
bootstrap/cache/.gitignore
```

Content pattern: `*` + `!.gitignore` (Laravel convention).  
**Next approved phase should `git add` these files only and commit**, then retest **fresh clone without manual mkdir**.

Exact status after writing placeholders: `git status` shows `?? storage/` and modified `bootstrap/cache/.gitignore` (uncommitted).

---

## 3. Authenticated workflow QA

| Step | Status |
|------|--------|
| Rebuild isolated clone + composer this session | **BLOCKED** / interrupted (missing `vendor`, PHP modules intermittent, `/tmp/composer` lost) |
| Create customer + admin users (`role=member` / `role=admin`) | **NOT COMPLETED** |
| Customer login → dashboard / orders / wallet | **NOT TESTED** |
| Admin login → admin UI | **NOT TESTED** |
| Customer denied `/admin` (403) | **NOT TESTED** (logic in `Admin` middleware: `role !== 'admin'` → 403) |
| Browser screenshots auth flows | **NOT TESTED** this phase |

**Known from code (not runtime-proof):**

- `User::isAdmin()` / middleware: `role === 'admin'` and `status === 'active'`.
- Unauthenticated admin routes redirect to login (302 observed in 14.16).

---

## 4. Responsive / full browser matrix

| Viewport | Status |
|----------|--------|
| Desktop login/register (14.16) | **PASS** (screenshots) |
| 1440 / 1280 / 768 / 390 authenticated | **NOT TESTED** |

---

## 5. Security / regression (limited)

| Item | Status |
|------|--------|
| No production DB | **PASS** (policy) |
| No real payment / provider | **PASS** (policy) |
| CSRF / session full auth | **NOT TESTED** |
| Nova branding on login | **PASS** (14.16 evidence) |

---

## 6. Bugs / blockers

| ID | Sev | Description |
|----|-----|-------------|
| F1 | P1 | Storage framework placeholders **not in Git index** → fresh clone 500 until mkdir |
| F2 | Env | Sandbox PHP extensions / `/tmp` artifacts not durable across long sessions |
| F3 | QA | Authenticated workflows **not executed** this phase due to F2 |

---

## 7. Overall status

### **PARTIAL / BLOCKED**

| Area | Status |
|------|--------|
| Root-cause analysis for storage | **PASS** |
| Placeholder files prepared on disk | **PASS** (uncommitted) |
| Fresh clone proof **after** committed fix | **NOT TESTED** |
| Authenticated customer/admin QA | **NOT TESTED** |
| Production readiness | **No** |

### Recommended next phase

1. **Approve + commit** storage `.gitignore` placeholders only (`chore: ensure storage framework dirs in git`).  
2. Fresh clone retest: login **200 without manual mkdir**.  
3. Stable host: seed QA users; complete authenticated browser matrix.  
4. Then GitHub push of `d619f8a` + storage commit.

**Stop — await approval.**
