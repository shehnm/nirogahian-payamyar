# Deploy

## Before

1. Full backup: database + `wp-content` (minimum `mu-plugins` + `plugins/wp-payamyar`)
2. Staging copy recommended (`staging.nirogahian.ir`)

## Rules

- **One** active mu-plugin file matching `payamyar*.php`
- Rename old patch to `.bak` before uploading new one
- Upload full file (~3.5 KB for v5c); do not paste partial content in web editor
- Verify byte size on server after upload

## Steps (DirectAdmin / FTP) — v6 staging

```
1. Backup mu-plugins + DB
2. mu-plugins/payamyar-surgical-cap-v4-STABLE-4624.php  ->  .bak (or .off)
3. Upload payamyar-cap-v6-phase2.php from mu-plugins/development/
4. Confirm only ONE payamyar*.php without .bak/.off extension
5. Open Payamyar settings — all categories should load
6. Read wp-content/py-profiler.log — expect: terms stored count=700
7. Publish test post (category اخبار آب + featured image)
8. Check kw_published_in_eitaa meta or channel message
```

Rollback: restore v4 from `.bak`, remove or rename v6.

## Rollback

See [ROLLBACK.md](ROLLBACK.md).

## Acceptance

See [TEST-CHECKLIST.md](TEST-CHECKLIST.md).
