<p align="center">
  <a href="./README.md">English</a> · <strong>فارسی</strong>
</p>

# SDK رسمی Telegram Core برای Laravel

<p align="center">
  پکیج رسمی اتصال امن پروژه‌های Laravel به <strong>TGCore</strong> بدون نیاز به نگهداری Telegram Bot Token در پروژه Consumer.
</p>

<p align="center">
  <img alt="Version" src="https://img.shields.io/badge/version-1.0.0-6f42c1">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-8%20→%2013-FF2D20?logo=laravel&logoColor=white">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-7.3%20→%208.x-777BB4?logo=php&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/license-MIT-2ea44f">
</p>

---

## این SDK چه کاری انجام می‌دهد؟

پروژه Consumer نباید Bot Token تلگرام را ذخیره کند یا امضای TGCore، Webhook Verification، Replay Protection، Idempotency، فایل‌ها و قرارداد API را دوباره از صفر پیاده‌سازی کند.

این SDK لایه Laravel-native برای ارتباط با TGCore است و امکانات زیر را فراهم می‌کند:

- درخواست‌های HMAC-authenticated به TGCore
- فراخوانی Telegram Bot API از طریق Gateway مرکزی
- اعتبارسنجی Updateهای امضاشده ورودی
- جلوگیری از پردازش تکراری Updateها
- File Upload / Staged Upload / Download
- Capability Discovery
- Commandهای تشخیصی Artisan
- Auto-discovery استاندارد Laravel

SDK مستقیماً با `api.telegram.org` کار نمی‌کند؛ تمام عملیات از TGCore عبور می‌کند.

---

## سازگاری

نسخه **1.0.0** برای Laravel **8 تا 13** طراحی و روی هر نسخه جداگانه تست شده است.

| Laravel | Testbench | حداقل PHP آن نسخه Laravel | نتیجه تست SDK |
|---|---:|---:|---|
| 8 | 6.x | 7.3 | ✅ 8 تست / 27 assertion |
| 9 | 7.x | 8.0.2 | ✅ 8 تست / 27 assertion |
| 10 | 8.x | 8.1 | ✅ 8 تست / 27 assertion |
| 11 | 9.x | 8.2 | ✅ 8 تست / 27 assertion |
| 12 | 10.x | 8.2 | ✅ 8 تست / 27 assertion |
| 13 | 11.x | 8.3 | ✅ 8 تست / 27 assertion |

کف PHP خود پکیج `^7.3|^8.0` است؛ در عمل نسخه Laravel پروژه تعیین می‌کند حداقل PHP نهایی چه باشد.

> Laravel 13 در حال حاضر به PHP 8.3 یا بالاتر نیاز دارد.

---

## نصب

```bash
composer require amirkateb/telegram-core-client:^1.0
```

Laravel از طریق Package Auto-discovery سرویس‌پروایدر و Facade را به‌صورت خودکار ثبت می‌کند.

سپس:

```bash
php artisan tgcore:install
```

---

## تنظیمات

در `.env` قرار دهید:

```dotenv
TGCORE_URL=https://tg.example.com
TGCORE_BOT_UUID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
TGCORE_CONSUMER_SECRET=your-consumer-secret
TGCORE_CONSUMER_PATH=/tgcore/webhook

TGCORE_TIMEOUT=30
TGCORE_CONNECT_TIMEOUT=7
TGCORE_SIGNATURE_TOLERANCE=300
```

`TGCORE_URL` باید HTTPS باشد.

پروژه Consumer فقط **Bot UUID** و **Consumer Secret** را از مدیر TGCore دریافت می‌کند و نیازی به Telegram Bot Token ندارد.

---

## شروع سریع

```php
use Illuminate\Support\Str;
use KatebSaber\TelegramCore\Facades\TelegramCore;

TelegramCore::sendMessage(
    123456789,
    'سلام از پروژه Laravel 👋',
    [],
    (string) Str::uuid(),
);
```

SDK هدرهای HMAC لازم را خودکار می‌سازد.

---

## فراخوانی Generic Telegram API

```php
$result = TelegramCore::call('sendPoll', [
    'chat_id' => 123456789,
    'question' => 'انتخاب شما؟',
    'options' => [
        ['text' => 'گزینه اول'],
        ['text' => 'گزینه دوم'],
    ],
], (string) Str::uuid());
```

TGCore در سمت Core تعیین می‌کند چه Methodهایی برای Consumer مجاز هستند.

---

## استفاده از Bot دیگر

```php
$result = TelegramCore::callForBot(
    'another-bot-uuid',
    'sendMessage',
    [
        'chat_id' => 123456789,
        'text' => 'پیام از Bot دیگر',
    ],
    (string) Str::uuid(),
);
```

Authorization همچنان در TGCore انجام می‌شود.

---

## دریافت Capabilityها

```php
$capabilities = TelegramCore::capabilities();
```

برای Feature Detection بین نسخه‌های مختلف Core بسیار مفید است.

---

## دریافت Updateهای تلگرام

SDK Route ورودی Consumer را ثبت می‌کند.

مسیر پیش‌فرض:

```text
POST /tgcore/webhook
```

Middleware SDK این موارد را بررسی می‌کند:

- Timestamp tolerance
- HMAC signature
- Bot UUID
- یکپارچگی Raw Body

پس از Verify، Event زیر Dispatch می‌شود:

```php
KatebSaber\TelegramCore\Events\TelegramUpdateReceived
```

نمونه Listener:

```php
use KatebSaber\TelegramCore\Events\TelegramUpdateReceived;

class HandleTelegramUpdate
{
    public function handle(TelegramUpdateReceived $event): void
    {
        $botUuid = $event->botUuid();
        $updateId = $event->updateId();
        $payload = $event->payload;

        // Business Logic پروژه شما
    }
}
```

Delivery تکراری قبل از اجرای دوباره منطق پروژه suppress می‌شود.

---

## فایل و Upload

### Staged Upload

```php
$upload = TelegramCore::stageUpload(
    storage_path('app/report.pdf'),
    null,
    (string) Str::uuid(),
);
```

### Download فایل تلگرام از Core

```php
$binary = TelegramCore::downloadFile($fileId);

file_put_contents(storage_path('app/downloads/file.bin'), $binary);
```

Bot Token و Telegram file URLهای حساس وارد پروژه Consumer نمی‌شوند.

---

## Idempotency

```php
$idempotencyKey = (string) Str::uuid();

TelegramCore::sendMessage(
    $chatId,
    'این عملیات با Retry امن است.',
    [],
    $idempotencyKey,
);
```

TGCore قرارداد Idempotency را در سمت Core enforce می‌کند.

---

## هدرهای Authentication

SDK به‌صورت خودکار هدرهای لازم را تولید می‌کند:

```text
X-TGCore-Bot-UUID
X-TGCore-Timestamp
X-TGCore-Signature
X-TGCore-Idempotency-Key
```

در پروژه Laravel امضای TGCore را دستی پیاده‌سازی نکنید مگر اینکه واقعاً Client غیر-Laravel خاصی می‌سازید.

---

## دستورات Artisan

```bash
php artisan tgcore:install
php artisan tgcore:status
php artisan tgcore:doctor
```

`tgcore:doctor` برای بررسی Connection و خطاهای رایج Integration طراحی شده است.

---

## مدیریت خطا

```php
use KatebSaber\TelegramCore\Exceptions\AuthenticationException;
use KatebSaber\TelegramCore\Exceptions\TelegramCoreException;
use KatebSaber\TelegramCore\Exceptions\UpstreamException;

try {
    TelegramCore::sendMessage($chatId, 'سلام');
} catch (AuthenticationException $e) {
    // خطای Signature / Authentication
} catch (UpstreamException $e) {
    $status = $e->status;
    $response = $e->response;
} catch (TelegramCoreException $e) {
    // خطای SDK / Config / Contract
}
```

---

## مدل امنیتی

- Consumer به Bot Token نیاز ندارد.
- TGCore URL فقط HTTPS است.
- Consumer Secret فقط سمت Server می‌ماند.
- Delivery ورودی قبل از Dispatch Verify می‌شود.
- Timestamp window جلوی Replayهای قدیمی را می‌گیرد.
- Update تکراری Suppress می‌شود.
- Idempotency Key برای Retry امن استفاده می‌شود.
- مالکیت Webhook/Token/Control Plane در Core باقی می‌ماند.

`TGCORE_CONSUMER_SECRET` را هرگز وارد Frontend، Mobile App، Log یا Repository عمومی نکنید.

---

## Auto-discovery

Composer به‌صورت خودکار این Provider را ثبت می‌کند:

```php
KatebSaber\TelegramCore\TelegramCoreServiceProvider::class
```

و Alias زیر نیز در دسترس است:

```php
TelegramCore
```

---

## تست Integration

```bash
php artisan tgcore:status
php artisan tgcore:doctor
```

سپس بررسی کنید:

1. Consumer URL از اینترنت و با HTTPS قابل دسترسی باشد.
2. Bot UUID درست باشد.
3. Consumer Secret دو سمت یکی باشد.
4. مسیر `POST /tgcore/webhook` در دسترس باشد.
5. Queue/Cache مورد استفاده خود پروژه سالم باشد.

---

## Matrix واقعی تست نسخه 1.0.0

```text
Laravel  8.83.x  + Testbench  6.x  ✅
Laravel  9.52.x  + Testbench  7.x  ✅
Laravel 10.50.x  + Testbench  8.x  ✅
Laravel 11.56.x  + Testbench  9.x  ✅
Laravel 12.69.x  + Testbench 10.x  ✅
Laravel 13.33.x  + Testbench 11.x  ✅
```

روی هر شش محیط همان suite با نتیجه **8 تست / 27 assertion** پاس شده است.

---

## ساختار پکیج

```text
config/
  tgcore.php

routes/
  tgcore.php

src/
  Commands/
  Contracts/
  Events/
  Exceptions/
  Facades/
  Http/
  Support/
  TelegramCoreServiceProvider.php

tests/
  Feature/
  Unit/
```

---

## نسخه

نسخه فعلی:

```text
1.0.0
```

Release Noteها در [`CHANGELOG.md`](./CHANGELOG.md) و سیاست سازگاری در [`docs/COMPATIBILITY.md`](./docs/COMPATIBILITY.md) قرار دارد.

---

## مستندات بیشتر

- [Installation](./docs/INSTALLATION.md)
- [Authentication](./docs/AUTHENTICATION.md)
- [Outbound API](./docs/OUTBOUND-API.md)
- [Inbound Updates](./docs/INBOUND-UPDATES.md)
- [Files](./docs/FILES.md)
- [Compatibility](./docs/COMPATIBILITY.md)
- [Security](./SECURITY.md)
- [Contributing](./CONTRIBUTING.md)

---

## License

MIT

---

<p align="center">
  <a href="./README.md">← Read the English README</a>
</p>
