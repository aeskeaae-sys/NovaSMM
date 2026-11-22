# NovaSMM Phase 14.7 — Asset Audit & Safe GitHub Sync Preparation

**Mode:** READ-ONLY / PREPARATION ONLY  
**No** git add, commit, push, force-push, restore, merge, or file deletion

**Source:** `/home/workdir/artifacts/SMMPanelV1-full/SMMPanelV1-main`  
**Local HEAD:** `f15c8b2`  
**Remote HEAD:** `e71c1f3` (scaffolding)

---

## A. Untracked assets inventory

### Working tree highlights (`git status`)

| Path | Approx. files | Classification |
|------|----------------|----------------|
| `public/_assets/` | ~408 | Mostly bootstrap-icons **docs** + icon sources; largely **dev/docs**, duplicate risk vs `public/assets/icon-fonts` |
| `public/assets/js/` | ~67 | **Required runtime** theme JS (main.js, custom.js, charts helpers, etc.) |
| `public/assets/libs/` | ~20 top-level packages | **Required runtime** third-party (bootstrap, simplebar, node-waves, choices.js; others partial) |
| `public/assets/icon-fonts/` | ~336 | Icon fonts for theme UI — **required** if icons.css used |
| `public/assets/images/media/` | ~19 | Media assets — **optional/required** depending on pages |
| `public/assets/css/icons.css`, `styles.css` (+ maps) | few | **Required** companions to minified CSS (icons.css referenced in layout) |
| `storage/` | runtime | **Do not track** contents (already gitignored patterns) |
| `docs/NOVASMM_PHASE14_5*.md`, `14_6*.md` | docs | Safe to track later |
| `.github/workflows/ci.yml` | modified | Review before commit |

**Untracked under `public/`:** ~852 paths (includes icon doc noise under `_assets`).

### Categories

| Category | Examples | Recommendation |
|----------|----------|----------------|
| **Required runtime** | `assets/js/main.js`, `assets/css/styles.min.css`, `icons.css`, `libs/bootstrap`, `simplebar`, `node-waves`, `choices.js`, `nova-theme.css` (tracked), brand SVGs (tracked) | Track before production deploy from Git |
| **Optional** | apexcharts demo JS, extra chart samples, some media | Track selectively if pages need them |
| **Development-only** | `*.map`, bootstrap-icons markdown docs under `_assets/**/docs` | Prefer **not** track |
| **Third-party libraries** | bootstrap, choices.js, simplebar, waves | Track minified build artifacts used by Blade; keep licenses if present |
| **Generated** | `public/build`, Vite output | Remain gitignored |
| **Sensitive** | `.env`, `database/*.sqlite` | **Never** track (already ignored) |
| **Unknown / low value** | Duplicate `_assets` tree overlapping icon-fonts | Inspect before bulk add |

---

## B. Asset integrity (static)

### Blade / layout references (sample)

From `head-css` and views, critical paths include:

| Reference | On disk | Tracked in Git |
|-----------|---------|----------------|
| `/assets/css/nova-theme.css` | Yes | **Yes** |
| `/assets/css/styles.min.css` | Yes | **Yes** |
| `/assets/css/icons.css` | Yes | **No** |
| `/assets/js/main.js` | Yes | **No** |
| `/assets/libs/bootstrap/css/bootstrap.min.css` | Yes | **No** |
| `/assets/libs/simplebar/*` | Yes | **No** |
| `/assets/libs/node-waves/*` | Yes | **No** |
| `/assets/libs/choices.js/...` | Yes | **No** |
| `/assets/libs/flatpickr/flatpickr.min.css` | **Missing** | No |
| `/plugins/pace-js/pace.min.js` | **Missing** (`public/plugins` absent) | No |
| `/assets/images/faces/9.jpg` | **Missing** | No |
| `/assets/images/brand-logos/desktop-white.png` | **Missing** | No |
| DataTables / Font Awesome / Toastr (CDN) | N/A (CDN) | N/A |

**Status:** Asset integrity **PARTIAL**. Several layout references are **missing on disk**. Runtime UI **NOT TESTED**.

### Licenses

No `LICENSE*` files found under `public/assets/libs` or `public/_assets` in this scan. Third-party compliance should be verified from original Ynex/theme distribution before redistribution claims.

---

## C. Git tracking audit

| Item | Status |
|------|--------|
| Tracked `public/*` count | **8** only (htaccess, index, favicon, nova CSS/SVG, icons.min.css, styles.min.css) |
| Core Laravel tracked | `artisan`, `composer.json`, `composer.lock`, controllers, routes — **Yes** |
| `.gitignore` | Ignores `.env`, `vendor/`, `node_modules/`, storage runtime, `public/build` — **reasonable** |
| Gap | Most Ynex runtime JS/CSS/libs **untracked** → GitHub deploy would miss UI dependencies |
| Should not track | `.env`, sqlite, `storage/*` data, `.DS_Store`, `*.pre-nova.bak` (optional keep local only), `_assets/**/docs` |

---

## D. Security

| Check | Result |
|-------|--------|
| `.env` ignored | **PASS** |
| sqlite ignored | **PASS** |
| Secrets in proposed asset add set | Unlikely (static CSS/JS) |
| Do not add | `.env`, credentials, dumps, private keys |

No secret values disclosed in this report.

---

## E. Local vs GitHub sync plan (not executed)

| Side | State |
|------|--------|
| Local HEAD | `f15c8b2` (+ dirty working tree) |
| Remote HEAD | `e71c1f3` scaffolding |
| Remote-only | Scaffold stubs (disposable if user confirms) |
| Local-only | Full app + untracked assets |

### Backup plan (before any future sync)

1. Record remote SHA `e71c1f3` and URL of Actions run history.  
2. Optional: `git branch backup/pre-sync-f15c8b2 f15c8b2`.  
3. Do not delete remote until user confirms local SoT.  
4. Prefer `--force-with-lease` only after explicit approval (not in this phase).

### Proposed future git operations (await approval)

1. Update `.gitignore` if needed: ignore `public/_assets/**/docs`, `*.map` optional, keep ignoring `.env`.  
2. `git add` **required runtime** paths only, e.g.:  
   - `public/assets/js/` (or at least `main.js`, `custom.js`, menu scripts)  
   - `public/assets/libs/bootstrap`, `simplebar`, `node-waves`, `choices.js`  
   - `public/assets/css/icons.css`  
   - `public/assets/icon-fonts/` (if required by icons.css)  
   - Phase docs `NOVASMM_PHASE14_5/6/7`  
3. Resolve **missing** assets from original ZIP before claiming UI complete.  
4. Commit with clear message; push only with user credentials + approval.  
5. Replace CI scaffolding on remote after full tree is on GitHub.

**This phase did not run any of the above.**

---

## F. Remaining risks

| Risk | Level |
|------|--------|
| Deploy from Git without untracked libs → broken CSS/JS | **High** |
| Missing flatpickr, pace-js, face images, some logos | **Medium** |
| `_assets` doc bloat if added blindly | **Medium** |
| Force-push without approval | Forbidden here |
| Runtime not browser-tested | **High** for production claims |

---

## Overall status

### **PARTIAL**

| Area | Status |
|------|--------|
| Untracked inventory | **PASS** (documented) |
| Required vs optional classification | **PASS** (best-effort) |
| All Blade refs present on disk | **FAIL** / **PARTIAL** |
| Runtime verification | **NOT TESTED** |
| Security (ignore rules) | **PASS** |
| Sync executed | **NOT STARTED** (by design) |

**Stop.** Await user approval before any `git add` / commit / push / asset restore from ZIP.
