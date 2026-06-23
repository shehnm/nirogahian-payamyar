# Test checklist (client acceptance)

## Categories

- [ ] Payamyar -> Eitaa: more than 3 categories, includes "اخبار"
- [ ] Same for Bale, Gap, Rubika
- [ ] Settings page opens in < 30s, no critical error

## Auto-publish

- [ ] Tick required categories in messenger settings, save
- [ ] Publish test post in category "اخبار" or "اخبار آب"
- [ ] Message sent automatically (no gray button)
- [ ] No critical error after publish

## Manual send (regression)

- [ ] Gray button send still works

## Stability

- [ ] Front site loads
- [ ] wp-admin loads

## Logs

- [ ] `py-profiler.log` shows `v6 terms stored count=...` (~700)
