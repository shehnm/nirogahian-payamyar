# Handoff — ادامه کار با Cursor Agent

> آخرین به‌روزرسانی: **۱۴۰۵/۰۴/۰۸** (۲۰۲۶-۰۶-۲۹)

## وضعیت کلی

| مورد | وضعیت |
|------|--------|
| فاز ۲ — همه دسته‌ها در UI | **تمام** (پروداکشن، v6d) |
| فاز ۲ — ارسال خودکار (ایتا/گپ/بله/روبیکا) | **تمام** (پروداکشن تأیید شد) |
| پچ فعال پروداکشن | `mu-plugins/payamyar-cap-v6-phase2.php` (v6d، **۳۳۲۷ بایت**) |
| آخرین commit | `0db83bc` — v6d |

## کارهای باز (اولویت)

### ۱. تلگرام (درخواست جدید کارفرما)

- تلگرام **داخل پیام‌یار** است: تب **پیام‌رسان‌های خارجی** (نه افزونه جدا).
- پچ v6d از نظر دسته + Fatal چند‌روباته، تلگرام را هم پوشش می‌دهد.
- کارفرما گفته: روی سایت دیگر همین هاست تلگرام OK، روی nirogahian.ir نه.
- کانال: `@nirogahian`
- احتمال‌ها: تنظیمات خاموش / توکن / `api.telegram.org` بلاک روی سرور ایران.
- گام‌ها: تنظیمات تلگرام در پیام‌یار -> تست publish -> اگر نشد `debug.log` + تست `curl api.telegram.org` از سرور.

### ۲. حذف staging (درخواست کارفرما)

- کارفرما: «ساب‌دامین staging را حذف کنید».
- DirectAdmin: Subdomain Management -> حذف `staging.nirogahian.ir` + پوشه + DB جدا (اگر بود).

### ۳. جمع‌بندی پایانی پروداکشن

- [ ] `WP_DEBUG` را در `wp-config.php` خاموش کن (debug.log پر نشود).
- [ ] `nirogahian-diag.php` را از ریشه سایت پاک کن (ابزار موقت).
- [ ] پست‌های تستی (`repro...`، «تست ارسال خودکار») را از کانال ایتا و پیشخوان حذف کن.

## فایل‌های کلیدی

| فایل | نقش |
|------|-----|
| `mu-plugins/development/payamyar-cap-v6-phase2.php` | پچ v6d — منبع deploy |
| `mu-plugins/production/payamyar-surgical-cap-v4-STABLE-4624.php` | rollback قدیمی |
| `tools/nirogahian-diag.php` | diag موقت — آپلود ریشه سایت |
| `docs/PROJECT-MEMORY.md` | حافظه فنی |
| `docs/CHANGELOG.md` | تاریخچه |
| `docs/ACCESS.md` | رمزها (لوکال، gitignore) |

## محیط لوکال

- Laragon: `C:\laragon\www\nirogahian` — `http://nirogahian.test`
- ریپو: `C:\ponisha\nirogahian-payamyar`
- پچ لوکال: `wp-content/mu-plugins/payamyar-cap-v6-phase2.php`
- پوشه `local/` در gitignore — اسکریپت‌های تست فقط لوکال

## باگ ریشه‌ای v6d (برای مرجع)

تابع `kw_get_category_id_list` در افزونه ionCube، `get_the_terms($id, ['category'])` را با taxonomy آرایه‌ای صدا می‌زند. روبات دوم `WP_Error` از کش می‌گیرد -> `array_intersect(null,...)` Fatal در PHP 8.1.

فیکس v6d: فیلتر `get_the_terms` priority 1 — اگر taxonomy آرایه بود، با رشته بازخوانی.

## دستور شروع برای Agent

```
پروژه nirogahian-payamyar — docs/HANDOFF.md و docs/ACCESS.md را بخوان.
کار بعدی: [تلگرام / حذف staging / ...]
```

## Deploy سریع v6d

```
1. بکاپ mu-plugins
2. فقط یک payamyar*.php فعال
3. آپلود payamyar-cap-v6-phase2.php -> wp-content/mu-plugins/
4. سایز سرور = 3327 بایت
```
