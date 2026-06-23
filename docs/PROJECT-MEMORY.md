# حافظه پروژه — nirogahian.ir / Payamyar

> **منبع حقیقت پروژه.** بعد از هر تغییر (پچ، تست، مکالمه کارفرما، rollback) این فایل و `CHANGELOG.md` را به‌روز کن.
>
> آخرین به‌روزرسانی: **۱۴۰۴/۰۴/۰۱** (۲۰۲۶-۰۶-۲۳)

---

## ۱. خلاصه قرارداد

| مورد | مقدار |
|------|--------|
| سایت | [nirogahian.ir](https://nirogahian.ir) |
| افزونه | Payamyar (`wp-content/plugins/wp-payamyar`) — ژاکت/zhaket |
| کارفرما | mosayebi (پونیشا) |
| فاز ۱ | ~۹۰۰٬۰۰۰ تومان — سایت بالا، ارسال دستی محدود |
| فاز ۲ | ۵٬۰۰۰٬۰۰۰ تومان — همه دسته‌ها + ارسال خودکار + پایداری |
| ریپو | https://github.com/shehnm/nirogahian-payamyar |
| مسیر لوکال | `C:\ponisha\nirogahian-payamyar` |

### تحویل مورد انتظار کارفرما

1. **همه** دسته‌بندی‌ها (~۷۰۰) در تنظیمات پیام‌یار (ایتا، بله، گپ، روبیکا، …)
2. تیک «اخبار» و دسته‌های لازم
3. **ارسال خودکار** موقع publish — بدون دکمه خاکستری
4. بدون خطای critical بعد از انتشار
5. سایت down نشود

### خارج از scope فعلی

- افزونه تلگرام جدا (روی سایت دیگر همین هاست کار می‌کند)
- بهینه‌سازی کلی سرعت سایت / ۴۸هزار پست
- باگ داخلی لایسنس zhaket

---

## ۲. وضعیت فعلی سرور (آخرین شناخت)

| مورد | وضعیت |
|------|--------|
| پچ فعال توصیه‌شده | `payamyar-surgical-cap-v4-STABLE-4624.php` فقط |
| v5 روی پروداکشن | **نزن** — critical error داده |
| دسته در UI (ایتا/بله/گپ) | ~۳ تا: اخبار آب، سدها، فاضلاب |
| روبیکا | ~۱۰۰ دسته |
| ارسال دستی | OK برای دسته‌های تیک‌خورده |
| ارسال خودکار | **کار نمی‌کند** (اخبار در لیست نیست + HARD_CUTOFF پست‌ها) |
| بکاپ | کارفرما گرفته (DB روی هاست + فلش) |
| استیجینگ | هنوز ساخته نشده — **اولویت بالا** |

جزئیات دسترسی: **`docs/ACCESS.md`** (لوکال — رمزها؛ برای اجنت: «از ACCESS بخوان»)  
درخواست کارفرما: **`docs/CLIENT-REQUEST.md`**  
شروع اجنت: **`docs/AGENT-START.md`**

---

## ۳. زیرساخت

### هاست

- شرکت: نت‌افراز
- DirectAdmin: `https://cp1.netafraz.com:2223/`
- اکانت اصلی: `mosayebi`
- IP سرور FTP: `195.28.169.78` — پورت `21` — Plain FTP
- SSH: ندارد (اشتراکی)
- دسترسی از IP خارج ایران: **timeout** (سایت و FTP)
- کار از ایران / VPS ایران / File Manager DirectAdmin

### مسیرهای مهم روی سرور

```
/home/mosayebi/domains/nirogahian.ir/public_html/
  wp-config.php
  wp-content/mu-plugins/          ← پچ‌های ما
  wp-content/plugins/wp-payamyar/ ← افزونه اصلی
  wp-content/py-profiler.log      ← لاگ پچ
  wp-content/debug.log            ← در صورت WP_DEBUG
```

### FTP اختصاصی پروژه

- کاربر: `shehneh@nirogahian.ir`
- مسیر: `/home/mosayebi/domains/nirogahian.ir/`
- رمز: در `docs/ACCESS.md`

### صفحات تست

- پیشخوان: `https://nirogahian.ir/wp-admin`
- پیام‌یار: `wp-admin/admin.php?page=wp_payamyar_admin_page`

---

## ۴. ریشه فنی مشکل

```
~۷۰۰ دسته + ~۴۸٬۰۰۰ پست
        ↓
Payamyar در admin بدون سقف get_terms و WP_Query
        ↓
timeout / fatal error
        ↓
پچ mu-plugin (surgical cap) → سایت پایدار ولی لیست دسته قطع می‌شود
```

### چرا stable فقط ۳ دسته نشان می‌دهد

فایل: `mu-plugins/production/payamyar-surgical-cap-v4-STABLE-4624.php`

- `get_terms_args`: `number` حداکثر ۱۰۰
- بعد از ۳ بار `get_terms` → `HARD_CUTOFF` → cache یا آرایه خالی
- `pre_get_posts`: بعد از ۳ بار → `posts_pre_query` برمی‌گرداند `array()` → **ارسال خودکار می‌شکند**

---

## ۵. تاریخچه تلاش‌ها

> جزئیات بن‌بست و مسیر مجاز: **[ROADMAP.md](ROADMAP.md)**

| نسخه | کار | نتیجه |
|------|-----|--------|
| پچ ~۴۳۸۲ | اولیه | سایت بالا، لیست دسته خالی |
| v4 ~۴۹۳۴ | | روبیکا بهتر |
| **STABLE ~۴۶۲۴** | محدودیت سخت | **بهترین حالت فعلی** — ۳ دسته، دستی OK |
| v4b/c/d | merge, rest_api, parent=0 | کرش / صفحه سیاه |
| cache بزرگ‌تر | | ایتا دسته بیشتر ولی صفحه غیرقابل استفاده — rollback |
| v5 | فایل جدا، terms_pre_query + get_terms | |
| v5 + v4 همزمان | | **Fatal: Cannot redeclare py_log()** |
| v5b/v5c | pad_counts، guard تداخل | هنوز critical روی پروداکشن — rollback |

---

## ۶. درس‌های قطعی (قوانین طلایی)

1. **فقط یک** `payamyar*.php` فعال در `mu-plugins`
2. همیشه بکاپ قبل از deploy
3. اول استیجینگ، بعد پروداکشن
4. آپلود کامل فایل (~۳.۵ KB برای v5c) — نه paste ناقص در ادیتور
5. `pad_counts => false` برای بارگذاری ۷۰۰ دسته بدون count پست
6. هرگز `posts_pre_query` خالی نکن روی publish
7. توابع global با پیشوند یکتا (`py_v5_`) اگر فایل جدا می‌زنی
8. از IP ایران تست کن

---

## ۷. مسیر پیشنهادی به راه‌حل (v6+)

### فاز A — آماده‌سازی

- [ ] استیجینگ: `staging.nirogahian.ir` یا لوکال (DB + wp-content)
- [ ] دانلود `wp-payamyar` به `plugin-ref/` (فقط مطالعه)
- [ ] دانلود `py-profiler.log` و `debug.log` بعد از هر تست

### فاز B — پچ بعدی (ایده)

بر پایه v4 STABLE **در همان فایل** یا فایل جدید با پیشوند یکتا:

1. `get_terms_args`: `number => 0`, `pad_counts => false`, `update_term_meta_cache => false`
2. **حذف** `HARD_CUTOFF` برای terms — یک بار load + transient ۱۲h
3. **حذف** `posts_pre_query => array()` — فقط cap نرم `posts_per_page=200`, `fields=ids`
4. اگر UI ۷۰۰ checkbox کند شد → AJAX جستجوی دسته (نیاز به خواندن کد افزونه)

### فاز C — پذیرش

چک‌لیست: `docs/TEST-CHECKLIST.md`

---

## ۸. درخواست کارفرما

**جزئیات کامل:** [docs/CLIENT-REQUEST.md](CLIENT-REQUEST.md)

خلاصه:
- «با ۵ م کلا مشکل برطرف میشه؟» → scope فاز ۲ بله؛ ۱۰۰٪ فقط بعد تست استیجینگ
- «سایت را ببرید روی هاست دیگه / لوکال» → موافقت — باید انجام شود
- افزونه تلگرام روی سایت دیگر همین هاست OK، روی نیروگاهیان نه → مشکل سایت‌محور
- توضیحات بیشتر در منوی فوتر سایت
- FTP ساخته شد ولی از خارج مشکل دسترسی

---

## ۹. نقشه فایل‌های ریپو

| فایل | نقش |
|------|-----|
| `docs/PROJECT-MEMORY.md` | **این فایل** — حافظه کامل |
| `docs/CHANGELOG.md` | لاگ تاریخی تغییرات |
| `docs/ROADMAP.md` | **اهداف کارفرما + بن‌بست‌ها + نقشه راه** |
| `docs/CLIENT-REQUEST.md` | درخواست کامل کارفرما (پونیشا) |
| `docs/AGENT-START.md` | شروع سریع اجنت |
| `docs/ACCESS.md` | رمزها (لوکال، gitignore) |
| `mu-plugins/production/` | پچ روی سرور / rollback |
| `mu-plugins/development/` | کاندیدای بعدی |
| `docs/DEPLOY.md` | نحوه deploy |
| `docs/ROLLBACK.md` | وقتی سایت پکید |

---

## ۱۰. چک سریع «مشکل حل شده؟»

| تست | OK | هنوز مشکل |
|-----|-----|-----------|
| پیام‌یار -> ایتا -> دسته‌ها | >۳ + اخبار | فقط ۳ |
| publish پست اخبار | خودکار برود | فقط دستی / خطا |
| `py-profiler.log` | `v6...` یا count~700 | `HARD_CUTOFF` / `CAP terms number=100` |

---

## نحوه به‌روزرسانی این سند

بعد از **هر** deploy، تست، یا تصمیم:

1. بخش «وضعیت فعلی سرور» را ویرایش کن
2. یک خط به `docs/CHANGELOG.md` اضافه کن
3. اگر پچ جدید: فایل را در `mu-plugins/` بگذار + `git commit` + `push`
