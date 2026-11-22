# NovaSMM Phase 7 — Regression Report

## Method

| Layer | Result |
|-------|--------|
| Static analysis of rebranded Blade/Vue/CSS | Performed |
| Automated PHPUnit / Dusk | **NOT TESTED** |
| Live browser | **NOT TESTED** |
| Real wallet / payment / provider calls | **NOT TESTED** (forbidden without approval) |

---

## Financial Regression Plan (Execute on Staging)

### Wallet
- [ ] Display balance matches DB after login  
- [ ] After order: balance decreases by charge only once  
- [ ] Failed order does not deduct (or refunds per original rules)  
- [ ] Concurrent orders do not double-spend (original locking)

### Deposits
- [ ] Create pending deposit  
- [ ] Callback success credits once  
- [ ] Failed/pending states display correctly  
- [ ] Card / bank / crypto / PayPal / PM paths still submit to original endpoints

### Orders
- [ ] Vue form (`form_csr`) price calc matches pre-rebrand  
- [ ] Classic form price calc matches  
- [ ] Status badges map to same enum values  
- [ ] History filters/pagination still hit original DataTable API  

**Static claim:** Rebrand did not edit order store controllers or wallet models in this worktree.

---

## Frontend Regression Matrix

| Page | Theme applied | Live functional |
|------|---------------|-----------------|
| Login / Register | Yes | NOT TESTED |
| Statistics / Dashboard | Yes | NOT TESTED |
| New Order (Vue) | Yes | NOT TESTED |
| New Order (Classic) | Yes | NOT TESTED |
| Order History | Yes | NOT TESTED |
| Profile / Deposits | Yes | NOT TESTED |
| Admin Dashboard | Yes | NOT TESTED |
| Admin Users/Orders/Services/… | CSS + cards | NOT TESTED |
| Global Search Ctrl+K | Yes | NOT TESTED |
| Notification empty UI | Yes | NOT TESTED |

---

## API Provider Regression Plan

- [ ] Provider list loads  
- [ ] Sync services (staging only)  
- [ ] No credentials in browser console  
- [ ] Place-order cron on staging mock/sandbox only  

**Status:** NOT TESTED in this environment.

---

## Verdict

| Statement | Result |
|-----------|--------|
| Rebrand files appear non-invasive to business logic (static) | PASS |
| System proven regression-free in production | **FAIL** (not proven) |
| Ready after staging checklist | CONDITIONAL |

