# NovaSMM Phase 14.5 — Multi-Workspace Source Discovery

**Mode:** Read-only discovery  
**No restore / merge / commit / push performed**

Base path inspected: `/home/workdir/artifacts`

---

## 1. Folder accessibility

| Folder | Absolute path | Accessible |
|--------|---------------|------------|
| NovaSMM | `/home/workdir/artifacts/NovaSMM` | Yes |
| NovaSMM-Deliverables | `/home/workdir/artifacts/NovaSMM-Deliverables` | Yes |
| SMMPanelV1 | `/home/workdir/artifacts/SMMPanelV1` | Yes |
| SMMPanelV1-full | `/home/workdir/artifacts/SMMPanelV1-full` | Yes |
| SMMPanelV1-work | `/home/workdir/artifacts/SMMPanelV1-work` | Yes |

Nested application roots also checked:

- `/home/workdir/artifacts/SMMPanelV1/SMMPanelV1-main`
- `/home/workdir/artifacts/SMMPanelV1-full/SMMPanelV1-main`
- `/home/workdir/artifacts/SMMPanelV1-work/SMMPanelV1-main`

---

## 2. Comparison matrix

| Criterion | NovaSMM | NovaSMM-Deliverables | SMMPanelV1 (root) | SMMPanelV1/SMMPanelV1-main | **SMMPanelV1-full/SMMPanelV1-main** | SMMPanelV1-work/SMMPanelV1-main |
|-----------|---------|----------------------|-------------------|----------------------------|-------------------------------------|--------------------------------|
| Approx. file count | 77 | 8 | 419 (mostly nested public) | 413 | **1784** | 141 |
| Git repository | No | No | No | No | **Yes** (`main`) | No |
| HEAD | — | — | — | — | **`f15c8b2`** | — |
| Remote | — | — | — | — | **origin → aeskeaae-sys/NovaSMM** | — |
| Tracked files | — | — | — | — | **357** | — |
| `artisan` real Laravel | No | No | No | No | **Yes** (ArgvInput + LARAVEL_START) | No |
| `composer.json` | No | No | Yes (root only) | No | **Yes** | Yes |
| `composer.lock` | No | No | No | No | **Yes (~319 KB)** | No |
| Controllers (PHP) | 0 | 0 | 0 | 0 | **61** | 7 |
| Models | 0 | 0 | 0 | 0 | **28** | 0 |
| Migrations | 0 | 0 | 0 | 0 | **38** | 0 |
| Blade views | partial under resources | few partials | 0 | 0 | **85** | 85 |
| `resources/js` files | — | — | — | — | **13** | 12 |
| Routes files | 0 | 0 | 0 | 0 | **3** | present |
| Config PHP | 0 | 0 | 0 | 0 | **11** | — |
| `tests/` files | 0 | 0 | 0 | 0 | **0** | 0 |
| Nova theme CSS | — | Yes (deliverable copy) | — | — | **Yes full ~25 KB** | Yes ~25 KB |
| Order / Wallet app code | No | No | No | No | **Yes** | Partial (Order controller only) |
| Role | Fragment assets | **Deliverables only** | Incomplete wrapper | Public assets only | **Most complete application tree** | Rebrand partial worktree |

---

## 3. Per-folder findings

### NovaSMM
- Only `public/` and `resources/` fragments (~77 files).
- Not a runnable Laravel application.

### NovaSMM-Deliverables
- **8 files**: rebrand report, partial Blade, nova CSS/SVG logos.
- Documentation and UI deliverables — **not** full source.

### SMMPanelV1
- Root has loose `composer.json`, `package.json`, `vite.config.js`, `.env`, and nested `SMMPanelV1-main` that is **almost only `public/`** (~413 files).
- No real `artisan`, no controllers/models/migrations in nested tree.

### SMMPanelV1-work / SMMPanelV1-main
- Rebrand worktree: views (85 blades), partial controllers (7), Nova CSS, routes.
- **Missing** artisan, composer.lock, models, migrations, most backend.
- No `.git`.

### SMMPanelV1-full / SMMPanelV1-main  ★ recommended Source of Truth candidate
- Full Laravel layout: `app`, `bootstrap`, `config`, `database`, `routes`, `resources`, `public`, `storage`, `vendor`.
- Real `artisan`, `composer.lock`, 61 controllers, 28 models, 38 migrations, 85 blades.
- Nova branding present.
- Local Git history + remote configured to private `NovaSMM` repo.
- Business-related paths present (Order controllers/models, WalletLog).
- `tests/` directory empty / absent of test files (**gap**).
- Some public theme libs may still be incomplete vs original ZIP (known from earlier phases); application PHP/Blade core is the strongest among the five.

---

## 4. Ranking (completeness)

1. **`/home/workdir/artifacts/SMMPanelV1-full/SMMPanelV1-main`** — only complete Laravel app + Git + Nova  
2. `SMMPanelV1-work/SMMPanelV1-main` — UI/rebrand partial  
3. `SMMPanelV1` (+ nested public) — assets fragment  
4. `NovaSMM` — assets fragment  
5. `NovaSMM-Deliverables` — docs/UI package only  

---

## 5. Recommendation

**Proposed Source of Truth (pending user approval):**  
`/home/workdir/artifacts/SMMPanelV1-full/SMMPanelV1-main`

**Stop before restore.** No merge, commit, or push was executed in this phase.

Optional next steps after approval:

- Diff selected paths against original ZIP if full asset recovery is required  
- Authenticated push of this tree to GitHub (Phase 14.2 continuation)  
- Do **not** treat Deliverables or worktree as full backend source  

---

## 6. Phase status

**PASS** (discovery complete; evidence-based comparison delivered)

Restore/sync actions: **NOT STARTED** (awaiting user approval)
