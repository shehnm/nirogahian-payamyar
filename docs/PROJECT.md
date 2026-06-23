# Project notes

## Client scope (phase 2)

- All ~700 categories in Payamyar UI (Eitaa, Bale, Gap, Rubika, ...)
- Auto-publish on post publish (including "اخبار")
- No critical error after publish
- Site must stay up

## Root cause

Payamyar loads categories/posts without limits on a large site.

## Production patch (v4 STABLE 4624)

- Caps queries; prevents crash
- Side effect: ~3 categories for some messengers, auto-publish broken for unticked categories

## Next work (v6+)

1. Staging environment
2. Download `wp-payamyar` plugin source for hook analysis
3. `pad_counts => false` on all category loads
4. Remove HARD_CUTOFF that returns empty posts (breaks publish)
5. Consider editing v4 in-place vs separate file — never two active `.php` files

## Failed attempts log

| Version | Result |
|---------|--------|
| v4b/c/d merge, rest_api | Page crash |
| Bigger cache only | Slow / unusable |
| v5 + v4 together | Fatal: Cannot redeclare py_log() |
| v5 alone | Critical error (likely load + missing pad_counts) |
