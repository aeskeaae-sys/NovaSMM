# NovaSMM — Known Issues

## Critical / Blockers for “claim production ready without staging”

| ID | Issue | Severity | Mitigation |
|----|-------|----------|------------|
| K1 | Live regression never executed in rebrand sandbox | Critical | Full staging QA checklist |
| K2 | Worktree is partial extract — not a runnable app alone | Critical | Merge into full ZIP/project |
| K3 | Financial flows not validated post-rebrand | Critical | Staging wallet/order tests |

## High

| ID | Issue | Severity | Mitigation |
|----|-------|----------|------------|
| H1 | `{!! !!}` admin notices / post HTML | High (conditional) | Restrict admin accounts; sanitize on save if possible |
| H2 | Cron HTTP endpoints exposure | High (ops) | IP allowlist / tokens |
| H3 | Demo mode may expose sample credentials | High | `PRJ_DEMO_MODE=false` |

## Medium

| ID | Issue | Severity | Mitigation |
|----|-------|----------|------------|
| M1 | No real notification backend — UI empty only | Medium | Future API |
| M2 | Global search is shortcut filter only | Medium | Optional search API later |
| M3 | Classic vs Vue order form visual parity incomplete | Medium | Optional polish |
| M4 | Ticket/Support module absent in source | Medium | N/A |
| M5 | Multiple UI libraries increase asset weight | Medium | Long-term consolidate |

## Low

| ID | Issue | Severity | Mitigation |
|----|-------|----------|------------|
| L1 | Some admin pages rely on global CSS overrides only | Low | Acceptable |
| L2 | Favicon still may point to old path until settings updated | Low | Admin settings → favicon |

## Rebrand-specific non-issues

- No evidence rebrand edited wallet deduction, payment callbacks, or provider API contracts in changed files.  
- Theme is additive CSS + Blade presentation.

