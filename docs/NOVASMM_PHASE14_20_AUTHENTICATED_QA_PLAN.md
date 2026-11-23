# NovaSMM — Phase 14.20 Authenticated QA Plan

**Status of execution in this phase:** preparation only — **authenticated flows NOT TESTED** here (no approved test accounts E-01…E-10 in environment).

## Prerequisites

- [ ] Fresh install checklist all PASS  
- [ ] Isolated database (never production)  
- [ ] Test customer user (`role` ≠ admin, `status=active`)  
- [ ] Test admin user (`role=admin`, `status=active`) per `App\Http\Middleware\Admin`  
- [ ] Credentials stored only in secure QA vault — **not in Git or this doc**

## Auth model notes (from source)

- Admin gate: `Auth::user()->role === 'admin'` (middleware `Admin`)  
- Unauthenticated access to `/admin` → redirect login (302 observed in prior phases)  
- Non-admin hitting admin → **403**

## Test cases (to execute in a later phase)

| ID | Area | Steps | Expected |
|----|------|-------|----------|
| A1 | Login | POST valid customer credentials | Session established; redirect home/dashboard |
| A2 | Logout | Logout action | Session cleared; cannot open protected pages |
| A3 | Session | Refresh protected page | Still authenticated |
| A4 | CSRF | POST without token | 419 or rejected |
| A5 | Customer dashboard | Open `/home` or app dashboard route | 200; Nova UI |
| A6 | Services | Open services index | 200 or app-specific empty state |
| A7 | Orders UI | Open orders list | 200; **no** real provider submit |
| A8 | Wallet / deposits UI | Open wallet/deposit pages | 200; **no** real payment |
| A9 | Profile | View/update safe fields on test user | Validation OK |
| A10 | Admin login | Admin credentials | Admin dashboard 200 |
| A11 | Admin authz | Customer opens `/admin` | 403 |
| A12 | Guest authz | Logged-out opens `/admin` | 302 → login |

## Explicit prohibitions

- No real money, live payment gateways, or production provider APIs  
- No production user data  
- No claiming PASS without browser/HTTP evidence  

## Blockers if missing

| Missing | Status |
|---------|--------|
| Seeded test users E-01–E-10 | **BLOCKED** for authenticated QA execution |
| Staging MySQL (if SQLite insufficient) | Environment decision |
