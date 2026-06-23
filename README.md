# nirogahian-payamyar

WordPress mu-plugin patches for **Payamyar** (`wp-payamyar`) on [nirogahian.ir](https://nirogahian.ir).

**حافظه کامل پروژه:** [docs/PROJECT-MEMORY.md](docs/PROJECT-MEMORY.md)  
**درخواست کارفرما:** [docs/CLIENT-REQUEST.md](docs/CLIENT-REQUEST.md)  
**شروع اجنت (دسترسی‌ها):** [docs/AGENT-START.md](docs/AGENT-START.md) — رمزها در `docs/ACCESS.md` (لوکال)  
**تاریخچه:** [docs/CHANGELOG.md](docs/CHANGELOG.md)

## Problem

- ~700 categories, ~48k posts
- Payamyar admin runs heavy `get_terms` / `WP_Query` -> timeout or fatal error
- Phase 1 stable cap keeps site up but shows only ~3 categories (Eitaa/Bale/Gap) and breaks auto-publish

## Phase 2 goals

1. All categories visible in Payamyar settings (all messengers)
2. Auto-publish on post publish (no critical error)
3. Site stays stable

## Repo layout

```
mu-plugins/
  production/     # known-good or current live patch (deploy from here)
  development/    # next patch candidates (test on staging first)
docs/             # deploy, rollback, test checklist
```

## Quick deploy

1. Backup DB + `wp-content` on server
2. In `public_html/wp-content/mu-plugins/` keep **only one** active `.php` patch
3. Upload file from `mu-plugins/production/` or `development/`
4. Test: `wp-admin/admin.php?page=wp_payamyar_admin_page`
5. Check `wp-content/py-profiler.log`

See [docs/DEPLOY.md](docs/DEPLOY.md) and [docs/ROLLBACK.md](docs/ROLLBACK.md).

## Server paths

| Item | Path |
|------|------|
| mu-plugins | `domains/nirogahian.ir/public_html/wp-content/mu-plugins/` |
| Payamyar plugin | `wp-content/plugins/wp-payamyar/` |
| Log | `wp-content/py-profiler.log` |

## Important

- Never run two `payamyar*.php` mu-plugins at once (duplicate `py_log` = fatal)
- Test on staging before production
- Do not commit passwords; use `docs/ACCESS.template.md`

## Status

| Patch | Role |
|-------|------|
| `payamyar-surgical-cap-v4-STABLE-4624.php` | Production stable — 3 categories, manual send OK |
| `payamyar-cap-v5-phase2.php` | Phase 2 WIP — not production-ready |
