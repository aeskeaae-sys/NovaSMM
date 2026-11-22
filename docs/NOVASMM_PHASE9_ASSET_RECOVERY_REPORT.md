# NovaSMM Phase 9 — Asset Recovery Report

## Source
- ZIP: `/home/workdir/attachments/SMMPanelV1-main.zip`
- Extracted public tree: `/tmp/nova_public_extract/SMMPanelV1-main/public` (~346MB)
- Integrated project: `/home/workdir/artifacts/SMMPanelV1-full/SMMPanelV1-main`

## What was recovered

| Asset | Method | Status |
|-------|--------|--------|
| Full `public/` from ZIP to `/tmp/nova_public_extract` | unzip | **PASS** |
| `nova-theme.css` / Nova SVGs in project | real files (protected) | **PASS** |
| Theme `styles.min.css`, `main.js`, `_assets`, `libs` | symlink to extract (partial) | **PARTIAL** |
| Full recursive copy into project `public/` | cp of ~300MB dirs | **BLOCKED/SLOW** in sandbox (timeouts) |

## Nova protection

Nova theme and logos are real files under:

- `public/assets/css/nova-theme.css`
- `public/assets/images/brand-logos/nova-icon.svg`
- `public/assets/images/brand-logos/nova-logo.svg`

They must not be overwritten by original theme packages.

## Staging host recommendation

On a real server with sufficient I/O:

```bash
cd /path/to/SMMPanelV1-main
# After backup:
unzip -o /path/to/SMMPanelV1-main.zip "SMMPanelV1-main/public/*" -d /tmp/fullpub
# Merge without clobbering nova:
rsync -a --exclude 'assets/css/nova-theme.css' \
  --exclude 'assets/images/brand-logos/nova-*.svg' \
  /tmp/fullpub/SMMPanelV1-main/public/ ./public/
# Re-copy nova files if needed
```

## Pre-nova backups

`*.pre-nova.bak` files **were not deleted** (Phase 9 rule).

