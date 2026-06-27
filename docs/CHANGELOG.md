# Changelog

فرمت: `YYYY-MM-DD` — توضیح کوتاه

---

## 2026-06-27 (د)

- **ریشهٔ Fatal ارسال خودکار پیدا و رفع شد.** انتشار پست با چند روبات روشن، روی پروداکشن critical می‌داد: `array_intersect(): Argument #1 must be of type array, null given` در `kw_sending_eitaa_post`.
- بازتولید قطعی روی لوکال با PHP CLI (پچ ما در CLI غیرفعال بود -> باگ خود افزونه است، نه پچ). با فقط ایتا OK، با ۲+ روبات کرش.
- علت: تابع `kw_get_category_id_list` افزونه، `get_the_terms($post_id, ['category'])` را با **آرایه** به‌جای رشته صدا می‌زند. روبات اول کش را زیر کلید جعلی `Array_relationships` می‌ریزد؛ روبات دوم از کش `WP_Error` می‌گیرد -> `wp_list_pluck`=null -> `array_intersect(null,...)` -> Fatal (PHP 8.1).
- v6d: فیلتر `get_the_terms` در سطح بالا (همه‌جا) — وقتی taxonomy آرایه بود، با رشتهٔ درست بازخوانی می‌کند. تست لوکال: همه روبات‌ها روشن -> PUBLISHED OK، ایتا/گپ/بله = `kw_published`. سایز فایل ۳۳۲۷ بایت.

## 2026-06-27 (ج)

- تشخیص قطعی از لاگ پروداکشن: نه Fatal، بلکه timeout. صفحهٔ پیام‌یار get_terms(دسته) را هزاران بار صدا می‌زند؛ v6b هر بار ۷۰۰ دسته + لاگ + transient برمی‌گرداند → از ۳۰ ثانیه رد می‌شود.
- py-profiler.log فقط CACHE_HIT count=3 (الگوی v4، یعنی روی سرور v4 فعال بود)؛ debug.log بدون Fatal، حجم از 2GB با truncate خالی شد.
- v6c: بازنویسی مینیمال — فقط سبک‌کردن کوئری دسته (pad_counts=false, update_term_meta_cache=false). بدون لاگ، بدون transient، بدون short-circuit/HARD_CUTOFF. cap کوئری‌های سنگین پست به ۳۰۰ (هرگز خالی).
- **پروداکشن OK**: v6c آپلود شد (۲۷۵۹ بایت). صفحهٔ پیام‌یار بدون critical باز شد و همهٔ ~۷۰۰ دسته در همهٔ پیام‌رسان‌ها (ایتا/روبیکا/بله/گپ/روبینو) درخت‌واره و تیک‌خور نمایش داده شد. هدف اول فاز ۲ تمام.

## 2026-06-27 (ب)

- v6b: بازنویسی روی الگوی v4 (wp_cache، transient حذف، hooks داخل admin_init) — تست لوکال ۷۰۰ دسته OK
- پروداکشن با v6 قبلی critical error — آپلود v6b با سایز ۴۳۰۹ بایت

## 2026-06-27

- تست لوکال کامل: لایسنس=1، ارسال خودکار ایتا OK (پست ۱۸۳۴۵۵)
- با همه روبات‌ها: روبیکا روی لوکال زنجیره publish را می‌شکند
- `local/test-publish-eitaa-only.php` + اسکریپت‌های inspect

## 2026-06-22

- تست لوکال Laragon: v6 — ۷۰۰ دسته OK، حافظه ~۵۸MB، بدون critical در publish
- تست لوکال: ارسال دستی ایتا OK؛ ارسال خودکار هنوز تأیید نشد (RTL-CareUnit خالی، لایسنس غیرفعال)
- `local/RUN-PHASE2-TEST.bat` + بهبود `test-phase2.php` (kw_published_in_eitaa)
- FTP دانلود RTL-CareUnit از این محیط: protocol violation (مثل قبل)

## 2026-06-23 (ه)

- `payamyar-cap-v6-phase2.php` — پچ فاز ۲: همه دسته + بدون HARD_CUTOFF (تست staging)

## 2026-06-23 (د)

- `docs/WORKFLOW.md` — قانون اجباری ثبت همه تست/پچ/ترفند
- ROADMAP: بخش تجربه‌های موفق (T01–T04)
- تقویت `.cursor/rules/project-memory.mdc`

## 2026-06-23 (ج)

- `docs/ROADMAP.md` — اهداف کارفرما، جدول بن‌بست‌ها (D01–D13)، مسیر v6، درخت تصمیم

## 2026-06-23 (ب)

- `docs/CLIENT-REQUEST.md` — درخواست کامل کارفرما از مکالمه پونیشا
- `docs/AGENT-START.md` — راهنمای شروع برای اجنت
- `docs/ACCESS.md` گسترش یافت (رمزها — لوکال فقط)
- README و PROJECT-MEMORY لینک به CLIENT-REQUEST و AGENT-START

## 2026-06-23

- ایجاد ریپو GitHub: https://github.com/shehnm/nirogahian-payamyar
- اضافه شد: `PROJECT-MEMORY.md` (حافظه کامل پروژه)
- اضافه شد: پچ production `payamyar-surgical-cap-v4-STABLE-4624.php`
- اضافه شد: پچ development `payamyar-cap-v5-phase2.php` (v5c)
- مستندات: DEPLOY, ROLLBACK, TEST-CHECKLIST, ACCESS.template
- تست v5 روی پروداکشن: critical error — rollback به v4 لازم
- علت شناخته‌شده v5+v4: `Cannot redeclare py_log()`
- FTP `shehneh@nirogahian.ir` ساخته شد
- دسترسی از IP خارج: timeout (HTTP + FTP)

## 2026-06-13 (روی سرور — قبل از ریپو)

- پچ STABLE-4624 فعال — سایت پایدار، ۳ دسته ایتا/بله/گپ

## 2026-06-08 تا 2026-06-12

- تلاش‌های v4b/c/d، merge، cache بزرگ — rollback

## 2026-06-22 (شروع پروژه پونیشا)

- تشخیص: mu-plugin موقت + حجم بالای get_terms در Payamyar
- فازبندی فاز ۱ / فاز ۲ توافق شد
