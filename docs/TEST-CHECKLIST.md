# Test checklist (client acceptance)

## Categories

- [x] Local (v6): get_terms = 700 categories
- [x] Local: Payamyar settings page opens, all categories visible (Eitaa/Rubika)
- [x] Payamyar -> Eitaa on **production** with v6d: all categories visible + tree
- [x] Same for Bale, Gap, Rubika, Rubino on production (user confirmed)
- [x] Settings page opens without critical error (production v6d)

## Auto-publish

- [x] Tick required categories in messenger settings, save (production)
- [x] Publish test post in category "اخبار آب" (production, all bots on)
- [x] `kw_published_in_eitaa` set + delivered to @nirogahian (production)
- [x] No critical error after publish — all bots on (production v6d)
- [x] Root cause fixed: get_the_terms array-taxonomy bug (multi-bot Fatal)

## Manual send (regression)

- [x] Gray button send still works (local Eitaa)

## Stability

- [ ] Front site loads (theme missing locally)
- [x] wp-admin loads (local)

## Telegram (pending)

- [ ] Categories visible in Telegram tab (external messengers)
- [ ] Auto-publish to @nirogahian on Telegram
- [ ] Check api.telegram.org from server if send fails

## Cleanup (pending)

- [ ] WP_DEBUG off on production
- [ ] Remove nirogahian-diag.php from site root
- [ ] Delete test posts from admin + Eitaa channel
- [ ] Remove staging.nirogahian.ir subdomain (client request)
