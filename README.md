<p align="center">
  <strong>English</strong> · <a href="./README.fa.md">فارسی</a>
</p>

# Telegram Core Client SDK for Laravel

<p align="center">
  Official Laravel SDK for securely connecting Consumer applications to <strong>TGCore</strong> without exposing Telegram Bot Tokens.
</p>

<p align="center">
  <img alt="Version" src="https://img.shields.io/badge/version-1.0.0-6f42c1">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-8%20→%2013-FF2D20?logo=laravel&logoColor=white">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-7.3%20→%208.x-777BB4?logo=php&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/license-MIT-2ea44f">
</p>

---

## Why this SDK?

Consumer applications should not store Telegram Bot Tokens or reimplement TGCore authentication, webhook verification, replay protection, idempotency, file transport, or API contracts.

This package provides the Laravel-native client layer for:

- HMAC-authenticated requests to TGCore
- Telegram Bot API calls through the Core gateway
- incoming signed update verification
- duplicate update suppression
- direct and staged file workflows
- capability discovery
- diagnostic Artisan commands
- Laravel auto-discovery

The SDK talks to **TGCore**, not directly to `api.telegram.org`.

---

## Compatibility

Version **1.0.0** is designed and tested for Laravel **8 through 13**.

| Laravel | Testbench | Minimum PHP for that Laravel line | SDK test result |
|---|---:|---:|---|
| 8 | 6.x | 7.3 | ✅ 8 tests / 27 assertions |
| 9 | 7.x | 8.0.2 | ✅ 8 tests / 27 assertions |
| 10 | 8.x | 8.1 | ✅ 8 tests / 27 assertions |
| 11 | 9.x | 8.2 | ✅ 8 tests / 27 assertions |
| 12 | 10.x | 8.2 | ✅ 8 tests / 27 assertions |
| 13 | 11.x | 8.3 | ✅ 8 tests / 27 assertions |

The package's own Composer floor is PHP `^7.3|^8.0`; the effective PHP requirement is naturally raised by the Laravel version installed in your application.

> Laravel 13 currently requires PHP 8.3 or newer.

---

## Installation

```bash
composer require amirkateb/telegram-core-client:^1.0
```

Laravel package auto-discovery registers the service provider and facade automatically.

Then run:

```bash
php artisan tgcore:install
```

The installer publishes `config/tgcore.php` and prepares the expected environment keys.

---

## Configuration

Add these values to your `.env`:

```dotenv
TGCORE_URL=https://tg.example.com
TGCORE_BOT_UUID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
TGCORE_CONSUMER_SECRET=your-consumer-secret
TGCORE_CONSUMER_PATH=/tgcore/webhook

TGCORE_TIMEOUT=30
TGCORE_CONNECT_TIMEOUT=7
TGCORE_SIGNATURE_TOLERANCE=300
```

### Important

`TGCORE_URL` must be HTTPS.

The Consumer application receives:

- **Bot UUID**
- **Consumer Secret**

from the TGCore administrator. It does **not** need the Telegram Bot Token.

---

## Quick start

```php
use Illuminate\Support\Str;
use KatebSaber\TelegramCore\Facades\TelegramCore;

TelegramCore::sendMessage(
    123456789,
    'Hello from my Laravel application 👋',
    [],
    (string) Str::uuid(),
);
```

The request is sent through TGCore with the required HMAC headers.

---

## Generic Telegram Bot API calls

You are not limited to convenience helpers.

```php
$result = TelegramCore::call('sendPoll', [
    'chat_id' => 123456789,
    'question' => 'Choose one',
    'options' => [
        ['text' => 'First'],
        ['text' => 'Second'],
    ],
], (string) Str::uuid());
```

TGCore decides whether the requested Telegram method is allowed for Consumers.

---

## Use another Bot UUID

If one Consumer application works with more than one authorized bot:

```php
$result = TelegramCore::callForBot(
    'another-bot-uuid',
    'sendMessage',
    [
        'chat_id' => 123456789,
        'text' => 'Sent through another TGCore bot',
    ],
    (string) Str::uuid(),
);
```

Authorization still happens inside TGCore.

---

## Capability discovery

Ask Core what the current contract supports:

```php
$capabilities = TelegramCore::capabilities();
```

This is useful for feature detection when Core evolves independently from a Consumer application.

---

## Receiving Telegram updates

The SDK registers the Consumer webhook endpoint automatically.

Default path:

```text
POST /tgcore/webhook
```

TGCore signs every delivery. The SDK middleware validates:

- timestamp tolerance
- HMAC signature
- Bot UUID context
- request body integrity

A valid delivery dispatches:

```php
KatebSaber\TelegramCore\Events\TelegramUpdateReceived
```

Example listener:

```php
use KatebSaber\TelegramCore\Events\TelegramUpdateReceived;

class HandleTelegramUpdate
{
    public function handle(TelegramUpdateReceived $event): void
    {
        $botUuid = $event->botUuid();
        $updateId = $event->updateId();
        $payload = $event->payload;

        // Your business logic belongs here.
    }
}
```

Duplicate deliveries are suppressed by the SDK before your application processes them again.

---

## Files and uploads

### Stage a local file

```php
$upload = TelegramCore::stageUpload(
    storage_path('app/report.pdf'),
    null,
    (string) Str::uuid(),
);
```

The returned staged reference can then be used in a TGCore Telegram method according to the Core contract.

### Download a Telegram file through Core

```php
$binary = TelegramCore::downloadFile($fileId);

file_put_contents(storage_path('app/downloads/file.bin'), $binary);
```

This keeps tokenized Telegram file URLs and Bot Tokens out of the Consumer application.

---

## Idempotency

For retryable write operations, send an idempotency key:

```php
$idempotencyKey = (string) Str::uuid();

TelegramCore::sendMessage(
    $chatId,
    'This request may safely be retried.',
    [],
    $idempotencyKey,
);
```

TGCore signs and enforces the idempotency contract server-side.

---

## Authentication headers

Outbound requests use the TGCore HMAC contract and may contain:

```text
X-TGCore-Bot-UUID
X-TGCore-Timestamp
X-TGCore-Signature
X-TGCore-Idempotency-Key
```

The SDK generates these automatically. Do not hand-build signatures unless you are implementing a non-Laravel client intentionally.

---

## Artisan commands

### Install

```bash
php artisan tgcore:install
```

Publishes SDK configuration and prepares the package integration.

### Status

```bash
php artisan tgcore:status
```

Shows local TGCore SDK configuration state.

### Doctor

```bash
php artisan tgcore:doctor
```

Runs connectivity/configuration diagnostics and helps identify integration problems.

---

## Error handling

TGCore errors are raised as SDK exceptions.

```php
use KatebSaber\TelegramCore\Exceptions\AuthenticationException;
use KatebSaber\TelegramCore\Exceptions\TelegramCoreException;
use KatebSaber\TelegramCore\Exceptions\UpstreamException;

try {
    TelegramCore::sendMessage($chatId, 'Hello');
} catch (AuthenticationException $e) {
    // Signature / authentication issue.
} catch (UpstreamException $e) {
    $status = $e->status;
    $response = $e->response;
} catch (TelegramCoreException $e) {
    // SDK / configuration / Core contract error.
}
```

---

## Security model

The SDK is intentionally built around these rules:

- Consumer apps never need Telegram Bot Tokens.
- TGCore URL must use HTTPS.
- Consumer Secret stays server-side.
- inbound deliveries are signature-verified before dispatch.
- timestamp tolerance limits replay windows.
- duplicate deliveries are suppressed.
- idempotency keys protect retryable write operations.
- Telegram control-plane ownership remains in Core.

Never expose `TGCORE_CONSUMER_SECRET` to frontend JavaScript, mobile apps, logs, or public repositories.

---

## Laravel auto-discovery

Composer auto-discovers:

```php
KatebSaber\TelegramCore\TelegramCoreServiceProvider::class
```

and registers the facade alias:

```php
TelegramCore
```

Manual provider registration is generally unnecessary.

---

## Testing your integration

A useful application-level smoke test is:

```bash
php artisan tgcore:status
php artisan tgcore:doctor
```

Then verify:

1. the Consumer URL is publicly reachable over HTTPS;
2. the Bot UUID matches TGCore;
3. the Consumer Secret matches TGCore;
4. your application can receive `POST /tgcore/webhook`;
5. queue/cache infrastructure required by your own application is healthy.

---

## SDK test matrix

For release `1.0.0`, the package suite was resolved and executed independently against:

```text
Laravel  8.83.x  + Testbench  6.x  ✅
Laravel  9.52.x  + Testbench  7.x  ✅
Laravel 10.50.x  + Testbench  8.x  ✅
Laravel 11.56.x  + Testbench  9.x  ✅
Laravel 12.69.x  + Testbench 10.x  ✅
Laravel 13.33.x  + Testbench 11.x  ✅
```

Each environment passed the same package suite: **8 tests / 27 assertions**.

---

## Package structure

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

## Versioning

The SDK follows Semantic Versioning.

Current release:

```text
1.0.0
```

See [`CHANGELOG.md`](./CHANGELOG.md) for release notes and [`docs/COMPATIBILITY.md`](./docs/COMPATIBILITY.md) for the compatibility policy.

---

## Additional documentation

- [Installation](./docs/INSTALLATION.md)
- [Authentication](./docs/AUTHENTICATION.md)
- [Outbound API](./docs/OUTBOUND-API.md)
- [Inbound updates](./docs/INBOUND-UPDATES.md)
- [Files](./docs/FILES.md)
- [Compatibility](./docs/COMPATIBILITY.md)
- [Security policy](./SECURITY.md)
- [Contributing](./CONTRIBUTING.md)

---

## License

MIT

---

<p align="center">
  <a href="./README.fa.md">مطالعه راهنمای فارسی →</a>
</p>
