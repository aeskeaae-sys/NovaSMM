# NovaSMM Phase 11 — Asset Deployment

## Nova files protected

- `public/assets/css/nova-theme.css`
- `public/assets/images/brand-logos/nova-icon.svg`
- `public/assets/images/brand-logos/nova-logo.svg`
- `resources/views/layouts/partials/nova-global-ui.blade.php`

## Recovered this phase / prior

- Theme `styles.min.css`, `icons.min.css`, partial JS
- Bootstrap libs (from Phase 10 copy)

## Full public tree

Full ~260MB+ `public/` from ZIP is **not fully restored** in project due to prior I/O timeouts.  
On real staging:

```bash
unzip -o SMMPanelV1-main.zip "SMMPanelV1-main/public/*" -d /tmp/fullpub
rsync -a --exclude 'assets/css/nova-theme.css' \
  --exclude 'assets/images/brand-logos/nova-*.svg' \
  /tmp/fullpub/SMMPanelV1-main/public/ ./public/
```

Status: **PARTIAL**

