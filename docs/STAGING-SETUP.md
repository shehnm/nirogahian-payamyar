# راه‌اندازی استیجینگ + دسترسی از IP خارج

> **گام ۱ نقشه راه** — قبل از deploy v6 روی پروداکشن اجباری است.  
> مرجع: [ROADMAP.md](ROADMAP.md)

---

## چرا استیجینگ؟

| بدون استیجینگ | با استیجینگ |
|---------------|-------------|
| v5 روی پروداکشن → critical (D09) | تست امن قبل از live |
| rollback تحت فشار کارفرما | rollback فقط ساب‌دامین |
| اجنت/هوش مصنوعی نمی‌تواند تست کند | محیط قابل تکرار |

---

## بخش ۱ — ساخت استیجینگ روی همان هاست (پیشنهاد اول)

### پیش‌نیاز

- ورود به DirectAdmin: `https://cp1.netafraz.com:2223/` (اکانت `mosayebi`)
- بکاپ تازه DB + `wp-content` (کارفرما گرفته؛ قبل از clone دوباره بگیر)

### مراحل DirectAdmin

#### ۱. بکاپ

```
DirectAdmin → Account Manager → Create/Restore Backups
  یا
phpMyAdmin → Export دیتابیس nirogahian
File Manager → فشرده‌سازی public_html/wp-content (حداقل mu-plugins + plugins)
```

#### ۲. ساب‌دامین استیجینگ

```
Account Manager → Subdomain Management
  Subdomain: staging
  Domain: nirogahian.ir
  Document Root: public_html/staging  (یا public_html/staging.nirogahian.ir)
```

#### ۳. کپی فایل‌ها

از File Manager یا FTP:

```
public_html/                    →  public_html/staging/
  (همه فایل‌های وردپرس)
```

یا فقط `wp-content` + `wp-config.php` اگر می‌خواهید سبک‌تر باشد.

#### ۴. دیتابیس جدا (توصیه‌شده)

```
MySQL Management → Create Database
  نام: mosayebi_staging (مثال)
  Import از بکاپ پروداکشن
```

در `staging/wp-config.php`:

```php
define('DB_NAME', 'mosayebi_staging');
define('DB_USER', '...');
define('DB_PASSWORD', '...');
define('WP_HOME', 'https://staging.nirogahian.ir');
define('WP_SITEURL', 'https://staging.nirogahian.ir');
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

جستجو-جایگزین URL در DB (phpMyAdmin یا WP-CLI اگر دارید):

```
https://nirogahian.ir  →  https://staging.nirogahian.ir
```

#### ۵. پچ روی استیجینگ

```
wp-content/mu-plugins/
  فقط یک فایل فعال: payamyar-cap-v6-phase2.php  (از ریپو development/)
  STABLE و بقیه: .bak
```

#### ۶. تست

- `https://staging.nirogahian.ir/wp-admin`
- پیام‌یار: `admin.php?page=wp_payamyar_admin_page`
- لاگ: `wp-content/py-profiler.log` — باید `v6 terms stored count=...` (~۷۰۰) ببینید
- چک‌لیست: [TEST-CHECKLIST.md](TEST-CHECKLIST.md)

---

## بخش ۲ — استیجینگ لوکال (جایگزین / مکمل)

اگر ساب‌دامین روی هاست سخت است یا دسترسی از خارج مشکل دارد:

1. دانلود بکاپ DB + `wp-content` از File Manager (از ایران یا VPS ایران)
2. لوکال: Local WP، Docker، یا XAMPP
3. import DB + کپی wp-content
4. آپلود v6 و تست
5. نتایج را در `CHANGELOG.md` ثبت کنید

کارفرما موافقت کرده: «سایت را روی هاست دیگر / لوکال تست کنید».

---

## بخش ۳ — دسترسی از IP خارج (برای اجنت / هوش مصنوعی)

### واقعیت مهم

محدودیت فعلی **دو لایه** است:

| لایه | علامت | قابل حل در DirectAdmin؟ |
|------|--------|-------------------------|
| **FTP** | timeout از خارج | **بله — موقت تا ۷ روز** |
| **HTTP** (سایت + wp-admin) | timeout از خارج | **خیر — محدودیت شبکه ایران↔خارج** |
| **DirectAdmin پنل** | ممکن است timeout | گاهی با VPN ایران؛ Login Key محدود |

یعنی: **باز کردن FTP برای IP اجنت کافی نیست** — برای تست کامل wp-admin باید از IP ایران یا استیجینگ لوکال/VPS ایران استفاده شود.

### کارهایی که **می‌شود** کرد در DirectAdmin

#### الف) FTP Access Manager (موقت — تا ۷ روز)

مسیر:

```
DirectAdmin → دامنه nirogahian.ir → تنظیمات پیشرفته (Advanced Features)
  → مدیریت دسترسی FTP (FTP Access Manager)
  → دسترسی‌های موقت (Temporary Access)
  → اضافه کردن IP جدید
```

محدودیت‌های نت‌افراز:

- حداکثر **۱۰ IP**
- حداکثر **۷ روز**
- فقط برای **FTP** (نه HTTP)

منبع: [آموزش FTP نت‌افراز](https://www.netafraz.com/blog/how-to-create-an-ftp-account-in-directadmin/)

**برای اجنت Cursor Cloud:** IP خروجی اجنت را در این بخش whitelist کنید → آپلود فایل و خواندن `py-profiler.log` از طریق FTP ممکن می‌شود؛ **باز کردن سایت در مرورگر از همان IP همچنان ممکن است timeout بدهد.**

#### ب) FTP Access دائمی از خارج

برای IP خارج از لیست کشورهای مجاز → **تیکت پشتیبانی نت‌افراز** (دائمی فقط با تایید پشتیبانی).

#### ج) رفع بلاک IP

اگر بعد از تلاش‌های ناموفق login، IP بلاک شد:

```
پنل کاربری نت‌افراز → سرویس‌های من → رفع مسدودیت IP
```

منبع: [دلایل بلاک IP](https://www.netafraz.com/blog/why-an-ip-address-is-blocked-and-how-to-unblock-it/)

#### د) Login Keys (دسترسی محدود به پنل)

```
DirectAdmin → Advanced Features → Login Keys
```

می‌توان کلید با IP و تاریخ انقضا ساخت — برای File Manager بدون FTP. باز هم نیاز به **دسترسی شبکه‌ای به پنل** دارد (از خارج ممکن است timeout).

### کارهایی که **نمی‌شود** یا کم‌اثر است

| ایده | چرا |
|------|-----|
| فقط باز کردن firewall در DirectAdmin | HTTP از خارج اغلب در سطح ISP/بین‌الملل قطع است |
| Geo DNS نت‌افراز | نیاز به **هاست دوم** (اروپا) + هزینه — برای dev زیاد است |
| SSH tunnel | SSH روی این هاست اشتراکی **ندارد** |
| Cloudflare Tunnel | بدون SSH/root عملی نیست |

### بهترین راه‌حل‌ها برای تست با هوش مصنوعی

| رتبه | راه | مناسب برای |
|------|-----|------------|
| **۱** | **استیجینگ لوکال** + بکاپ DB در ریپو/فضای خصوصی | تست کامل wp-admin از هر IP |
| **۲** | **VPS ایران** (مثلاً ۵۰–۱۰۰ هزار تومان/ماه) + clone سایت | اجنت از خارج به VPS وصل شود؛ VPS به هاست ایران |
| **۳** | ساب‌دامین استیجینگ + **کارفرما از ایران** چک‌لیست را بزند | کم‌هزینه؛ اجنت فقط کد می‌زند |
| **۴** | FTP whitelist ۷ روزه برای IP اجنت | فقط deploy + خواندن log؛ بدون تست UI |
| **۵** | تیکت نت‌افراز برای whitelist دائمی FTP | deploy راحت‌تر؛ باز هم HTTP محدود |

### IP اجنت Cloud Cursor

IP خروجی اجنت ثابت نیست و ممکن است عوض شود. برای FTP موقت:

1. از داخل سشن اجنت: `curl -s ifconfig.me` → IP فعلی
2. همان IP را در FTP Access Manager اضافه کنید
3. یا VPS ایران به‌عنوان jump host با IP ثابت

---

## بخش ۴ — چک‌لیست بعد از استیجینگ

```
[ ] staging.nirogahian.ir بالا می‌آید
[ ] wp-admin با همان یوزر پروداکشن (یا admin جدا)
[ ] فقط یک payamyar*.php فعال
[ ] v6 deploy شده
[ ] py-profiler.log: v6 loaded + terms stored count~700
[ ] TEST-CHECKLIST همه آیتم‌ها
[ ] PROJECT-MEMORY + CHANGELOG به‌روز
```

---

## بخش ۵ — بعد از موفقیت استیجینگ

1. deploy v6 روی پروداکشن (یک فایل، بکاپ، STABLE → .bak)
2. rollback آماده: [ROLLBACK.md](ROLLBACK.md)
3. اعلام به کارفرما با [TEST-CHECKLIST.md](TEST-CHECKLIST.md)

---

## لینک‌ها

| سند | محتوا |
|-----|--------|
| [ROADMAP.md](ROADMAP.md) | گام‌های ۱–۷ |
| [DEPLOY.md](DEPLOY.md) | قوانین deploy |
| [ACCESS.template.md](ACCESS.template.md) | قالب رمزها |
