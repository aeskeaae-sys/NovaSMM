# NovaSMM Phase 14.9 — Runtime Asset Tracking & GitHub Sync Preparation

**Mode:** READ-ONLY planning — **no** `git add` / commit / push / merge / reset / clean  
**Source:** `/home/workdir/artifacts/SMMPanelV1-full/SMMPanelV1-main`  
**Local HEAD:** `f15c8b24e2be1273e8d43f41f0bedc6a4980bd25` (`f15c8b2`) — **matches expected**  
**Remote:** `https://github.com/aeskeaae-sys/NovaSMM.git` (HEAD previously `e71c1f3` scaffolding)

---

## A. Verify Phase 14.8 recovery

| Check | Result | Status |
|-------|--------|--------|
| Report `docs/NOVASMM_PHASE14_8_ASSET_RECOVERY_REPORT.md` | Present | **PASS** |
| Git HEAD still `f15c8b2` | Yes | **PASS** |
| `pace.min.js` SHA256 | `82a77b61…eaa3d` | **PASS** |
| `flatpickr.min.css` SHA256 | `1b34a425…c754` | **PASS** |
| `faces/9.jpg` SHA256 | `5a2214cd…0511` | **PASS** |
| `desktop-white.png` SHA256 | `2a9ec2ae…fc44` | **PASS** |
| Nova icon preserved | Yes | **PASS** |
| Source ZIP used | `/home/workdir/attachments/SMMPanelV1-main.zip` | **PASS** |
| Backup path (14.8) | `/tmp/nova_asset_recovery_backup_20261122065458` | Recorded (may be ephemeral) |

---

## B. Runtime asset classification (on-disk counts)

| Path | File count (approx.) | Class |
|------|----------------------|--------|
| `public/assets/js/` | 67 | **Required runtime** (theme JS: main, custom, menus, charts helpers) |
| `public/assets/libs/` | 193 | **Required runtime** third-party: bootstrap, choices.js, flatpickr, node-waves, simplebar |
| `public/assets/css/` extras | icons.css, styles.css, maps | **Required** icons.css + maps **optional** |
| `public/assets/icon-fonts/` | 336 | **Required runtime** if icons.css depends on local fonts |
| `public/plugins/pace-js/` | 2 | **Required runtime** (referenced in layout) |
| `public/assets/images/faces/` | 26 | **Required/optional** by page (9.jpg was required) |
| `public/assets/images/brand-logos/` | 8 | **Required** (incl. Nova SVGs + desktop/toggle) |
| `public/assets/images/media/` | 19 | **Optional runtime** |
| `public/_assets/` | 408 (all `.md` docs in sample) | **Documentation / do not bulk-track** |
| `storage/` | runtime | **Sensitive/runtime — ignore** |
| CDN (DataTables, FA, Toastr) | — | **External — not in repo** |

**Tracked `public/*` today:** 8 files only  
**Untracked under `public/`:** ~1059 paths

---

## C. Dependency mapping (static)

Layout `head-css` depends on paths now present for the critical set recovered in 14.8 plus existing untracked libs (bootstrap, simplebar, waves, choices, main.js).

| Status | Notes |
|--------|--------|
| Critical recovered refs | Present on disk |
| Placeholder files | **None created** |
| Exhaustive every-Blade-path scan | **PARTIAL** — residual missing theme files possible |
| Browser runtime | **NOT TESTED** |

---

## D. License & security

| Item | Finding |
|------|---------|
| LICENSE files under `public/assets/libs` | **None found** in quick scan — residual compliance risk; libraries originate from original SMMPanel/Ynex ZIP |
| `.env` | Ignored — **do not track** |
| sqlite | Ignored |
| Secrets in public assets | Not expected; still exclude `.env`, keys, dumps |
| Git history secrets | No `.env` in index historically (prior audits) |

---

## E. Git tracking plan (proposed — not executed)

### Recommend TRACK (~file groups)

1. `public/assets/js/` (67)  
2. `public/assets/libs/` (193) — bootstrap, choices, flatpickr, node-waves, simplebar  
3. `public/assets/css/icons.css` (+ optionally non-min maps)  
4. `public/assets/icon-fonts/` (336) — if size acceptable  
5. `public/plugins/pace-js/` (2)  
6. `public/assets/images/faces/` (26)  
7. `public/assets/images/brand-logos/` (all non-secret logos; Nova already tracked)  
8. `public/assets/images/media/` (19) — optional but useful  
9. Phase docs `NOVASMM_PHASE14_5` … `14_9`  

**Rough track add size:** order of **~600–700** files if icon-fonts included; fewer if icon-fonts deferred.

### Recommend IGNORE / exclude

| Pattern | Reason |
|---------|--------|
| `public/_assets/` | Docs-heavy bootstrap-icons markdown; not required if `assets/` tree used |
| `*.map` under css | Source maps — optional |
| `.env`, `storage/**` data, `*.sqlite` | Sensitive / runtime |
| `vendor/`, `node_modules/`, `public/build` | Already ignored |
| `*.pre-nova.bak` | Local backups — user decision |

### `.gitignore` recommendations (for a future approved edit)

```
# optional additions after approval
/public/_assets/
*.css.map
*.js.map
```

Do **not** ignore `public/assets/js` or `public/assets/libs`.

### Files requiring user decision

- Track full `icon-fonts` (large) vs subset  
- Track `public/_assets` at all (default: **no**)  
- Include chart demo JS under `assets/js` (apexcharts-*) or slim to core scripts only  

---

## F. GitHub comparison & sync procedure (future)

| Item | Local | Remote |
|------|--------|--------|
| HEAD | `f15c8b2` | Scaffolding `e71c1f3` |
| Application | Full Laravel + assets on disk | Scaffold stubs |
| Histories | Divergent | Scaffolding only |

### Backup before any future sync

1. Note remote SHA `e71c1f3`  
2. `git branch backup/local-f15c8b2 f15c8b2`  
3. Optional archive of `public/` outside git  

### Proposed sequence (needs explicit user approval)

1. Adjust `.gitignore` if agreed  
2. `git add` only approved runtime paths + docs  
3. `git status` review (no `.env`)  
4. `git commit` with message e.g. `chore: track runtime public assets and recovery docs`  
5. Authenticated `git push origin main --force-with-lease` **only if** user confirms remote scaffolding may be replaced  

**This phase performs none of the above.**

---

## G. Restrictions compliance

No git mutating commands run. No file deletes. No business logic edits.

---

## H. Risks

| Risk | Level |
|------|--------|
| Push without tracking libs → broken UI on fresh clone | **High** |
| Tracking entire `_assets` | **Medium** (bloat) |
| Missing licenses in tree | **Medium** (compliance) |
| Force-with-lease without approval | Forbidden until approved |
| Runtime not browser-tested | **High** for production claims |

---

## Overall status

### **PARTIAL**

| Area | Status |
|------|--------|
| 14.8 recovery verification | **PASS** |
| Classification & track plan | **PASS** (plan only) |
| Full reference completeness | **PARTIAL** |
| License completeness | **PARTIAL** |
| Git operations | **NOT STARTED** |
| GitHub sync | **BLOCKED** pending approval + credentials |

**Stop.** Await user approval before any `git add` / commit / push.
