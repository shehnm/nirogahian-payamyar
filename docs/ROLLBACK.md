# Rollback (site down / critical error)

## Immediate

1. File Manager -> `domains/nirogahian.ir/public_html/wp-content/mu-plugins/`
2. Rename **all** `payamyar*.php` to `.bak` (including v5)
3. Restore only stable:

```
payamyar-surgical-cap-v4-STABLE-4624.php.bak
  -> payamyar-surgical-cap-v4-STABLE-4624.php
```

4. Hard refresh `wp-admin`

## If still broken

- Disable **all** `payamyar*.php` (everything `.bak`)
- Site and wp-admin should load; Payamyar may crash until stable is restored

## Enable debug log

In `wp-config.php` (temporary):

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Read: `wp-content/debug.log` (last lines).

## Known failures

| Symptom | Cause |
|---------|--------|
| Critical error on Payamyar page | Two mu-plugins active OR corrupt upload OR v5 + v4 conflict |
| Cannot redeclare py_log() | v4 and v5 both `.php` |
| Only 3 categories | stable v4 active (expected until phase 2 done) |
