# NovaSMM Phase 5 — Test Report

**Environment:** Sandbox cannot run full Laravel stack (no DB, incomplete `public/_assets` vendor tree, no PHP-FPM/Vite server).

## Static verification performed

| Item | Result |
|------|--------|
| Classic order form `#form-buy` action `/api/orders/store` | Preserved |
| Classic order form scripts section | Not rewritten |
| Deposit form actions (e.g. `/api/users/send-card`) | Preserved |
| Admin DataTable AJAX URLs | Untouched in scripts |
| Profile variables (`$user->balance`, rank, deposits) | Real Blade variables kept |
| No Controller/Model/Route edits | Confirmed |
| `nova-theme.css` syntax | Valid CSS appended |
| Phase 4 files still present | Yes |

## Required live tests on staging (Not Tested here)

### Customer
- [ ] Classic New Order path (`order_form_type` ≠ form_csr): select service, price calc, submit
- [ ] Vue New Order path still works after theme CSS
- [ ] Order History filter + table
- [ ] Profile stats display correct balance/rank
- [ ] Each deposit method (card, bank, crypto, paypal, perfect money)
- [ ] Login / Register glass UI + auth success

### Admin
- [ ] Users list DataTable + search + open user detail + update
- [ ] Orders list filters + status badges + bulk delete (if permitted)
- [ ] Services / Categories CRUD modals
- [ ] Providers list + import services
- [ ] Transactions / bank logs / cards
- [ ] Settings pages load
- [ ] Dashboard stats still match DB

### Cross-cutting
- [ ] No JS console errors
- [ ] Mobile: tables scroll, forms usable, sidebar drawer
- [ ] CSRF still on all POST forms
- [ ] Admin middleware / permissions unchanged
- [ ] API provider order placement (cron) still works

## Known residual gaps
- Ticket/Support module not in source — nothing to style
- Some admin pages may still have light-oriented inline styles; global `nova-theme.css` overrides most
- Classic order form balance sidebar (right column) not fully redesigned to match Vue client version (optional follow-up)
- Full visual QA needs real browser + production assets

**Do not mark production-ready until the checklist is completed on a real environment.**
