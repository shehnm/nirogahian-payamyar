# Deploy

## Before

1. Full backup: database + `wp-content` (minimum `mu-plugins` + `plugins/wp-payamyar`)
2. Staging copy recommended (`staging.nirogahian.ir`)

## Rules

- **One** active mu-plugin file matching `payamyar*.php`
- Rename old patch to `.bak` before uploading new one
- Upload full file (~3.5 KB for v5c); do not paste partial content in web editor
- Verify byte size on server after upload

## Steps (DirectAdmin / FTP)

```
1. mu-plugins/payamyar-surgical-cap-v4-STABLE-4624.php  ->  .bak
2. Upload new patch from this repo
3. Open Payamyar settings page
4. Read wp-content/py-profiler.log
```

## Rollback

See [ROLLBACK.md](ROLLBACK.md).

## Acceptance

See [TEST-CHECKLIST.md](TEST-CHECKLIST.md).
