# NovaSMM Phase 6 — Performance Report

## Changes impact
| Asset | Impact |
|-------|--------|
| `nova-theme.css` growth | +~5–6KB CSS (Phase 6 section) |
| `nova-global-ui.blade.php` JS | ~40 lines vanilla JS, no library |
| Search overlay DOM | Single fixed overlay, idle when closed |

## Positive
- No new npm packages
- No extra Chart/DataTable libs
- Notification does not poll API (no backend to poll)
- `prefers-reduced-motion` reduces animation cost

## Recommendations for production (not applied here)
- Run Vite production build + purge unused CSS if toolchain supports it
- Lazy-load admin-only assets on admin routes
- Ensure original `styles.min.css` + `nova-theme.css` order: theme last (already)
- Compress SVG logos (already small)

## Not measured in sandbox
- Lighthouse scores
- TTFB / LCP
- DataTable large dataset timing

