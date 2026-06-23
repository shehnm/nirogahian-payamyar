# Changelog

فرمت: `YYYY-MM-DD` — توضیح کوتاه

---

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
