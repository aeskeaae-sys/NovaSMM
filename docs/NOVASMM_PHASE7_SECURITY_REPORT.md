# NovaSMM Phase 7 — Security Report

**Analysis type:** Static code review of worktree + known Laravel patterns  
**Live penetration testing:** NOT PERFORMED  
**Production systems:** NOT TARGETED

---

## 1. Authentication & Session (Static)

| Control | Observation | Status |
|---------|-------------|--------|
| Login form CSRF | `@csrf` on login form (Phase 4/6) | PASS (static) |
| Auth routes | Laravel UI / Sanctum patterns in routes | PASS (declared) |
| Password fields | `type="password"` on login/register/profile | PASS |
| Demo prefill | Login may prefill demo user when `PRJ_DEMO_MODE` | **Known** — disable in production |
| Session config | Not in extract | **NOT TESTED** |

**Recommendation:** Set `PRJ_DEMO_MODE=false` and never ship demo passwords in production `.env`.

---

## 2. CSRF / XSS / Injection

| Topic | Finding | Status |
|-------|---------|--------|
| CSRF tokens in forms | Widespread `@csrf` | PASS (static) |
| Blade escaping | Most `{{ }}` used | PASS |
| Unescaped HTML | `{!! Helper::getNotice() !!}`, post content, status HTML | **ACCEPT RISK** — admin-authored HTML; treat admin as trusted |
| SQL | No new queries in rebrand; helpers in extract show limited surface | **NOT FULLY AUDITED** (models missing) |
| HTMLPurifier | In composer.json | PASS (available) |

---

## 3. Secrets & Credentials

| Item | Finding | Status |
|------|---------|--------|
| API keys in views | Admin settings use form values (password type after Phase 6) | Improved |
| PayPal `client_secret` | Forced `type="password"` (Phase 6) | PASS |
| Captcha secret | `type="password"` (Phase 6) | PASS |
| Bank API tokens | password inputs | PASS (static) |
| Secrets in docs | Phase 7 docs use placeholders only | PASS |
| Console logging secrets | No active console.log of tokens found | PASS |
| `.env` in repo | Should never commit real secrets | **OPS** |

**Do not** commit production `.env`. Rotate any key that was ever in a shared ZIP.

---

## 4. Authorization

| Item | Status |
|------|--------|
| Admin routes under admin prefix (routes file) | PASS (static structure) |
| `isAdmin()` gate for Admin chip in search / header | PASS (static) |
| Full permission matrix | **NOT TESTED** live |

---

## 5. Financial / Payment Security

| Item | Status |
|------|--------|
| Payment callbacks on `/schedule/deposit/*` | Present in routes — **NOT TESTED** |
| Order store `/api/orders/store` | Auth middleware group — **NOT TESTED** |
| Rebrand impact on charge logic | Presentation only — **no wallet code edited** |

**Critical rule:** Never test with real money or real provider credentials without written approval.

---

## 6. Security Headers / HTTPS

Not configurable from this extract alone. Deployment guide requires HTTPS, secure cookies, and hardened `APP_DEBUG=false`.

---

## 7. Residual Risks

1. Admin HTML notices (`{!! !!}`) — XSS if admin account compromised  
2. Incomplete model/controller audit in sandbox  
3. Cron endpoints publicly routable by design — must be IP-restricted or secret-token protected in production (ops)  
4. Original theme may expose version/license info on admin dashboard (existing behavior)

**No Critical rebrand-introduced auth bypass was identified in static review of changed files.**

