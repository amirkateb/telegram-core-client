# Telegram Core Laravel Client

Official Laravel SDK for connecting an application to **Telegram Core** without exposing Telegram Bot Tokens to the application.

## What it provides

- Telegram Core contract v2 authentication (HMAC SHA-256 + timestamp + replay-safe idempotency)
- Generic access to every consumer-allowed Telegram Bot API method exposed by Core
- Convenience helpers for common sends such as text, photo and location
- Staged upload support for large/multi-file Telegram requests
- Private Telegram file download through Core
- Signed Core-to-client update endpoint with duplicate suppression
- Laravel event `TelegramUpdateReceived`
- `tgcore:install`, `tgcore:status`, and `tgcore:doctor`
- Runtime capability discovery, so clients can compare their assumptions with the connected Core

The SDK **never calls `api.telegram.org` directly** and does not require a Telegram Bot Token.

## Requirements

PHP 8.1+ and Laravel 10/11/12. A Telegram Core administrator must provide a Bot UUID and Consumer Secret and configure this application's public HTTPS consumer URL on the bot.

## Install from Packagist / GitHub

After the package is published to Packagist:

```bash
composer require amirkateb/telegram-core-client
php artisan tgcore:install
```

Before Packagist publication, Composer can install directly from the public GitHub repository by adding it as a VCS repository, then requiring the same package name/version. Do not use a production secret in `composer.json`.

Configure `.env`:

```dotenv
TGCORE_URL=${YOUR_TGCORE_URL}
TGCORE_BOT_UUID=00000000-0000-0000-0000-000000000000
TGCORE_CONSUMER_SECRET=replace-with-the-secret-issued-by-core
TGCORE_CONSUMER_PATH=/tgcore/webhook
TGCORE_TIMEOUT=30
TGCORE_CONNECT_TIMEOUT=7
TGCORE_SIGNATURE_TOLERANCE=300
```

Then verify:

```bash
php artisan tgcore:doctor
php artisan tgcore:status
```

Set the bot's Consumer URL in Telegram Core to the absolute HTTPS URL of `TGCORE_CONSUMER_PATH`, using your application `APP_URL` plus `TGCORE_CONSUMER_PATH`.

## Send Telegram operations

```php
use KatebSaber\TelegramCore\Facades\TelegramCore;

TelegramCore::sendMessage(123456789, 'سلام از کلاینت', [], idempotencyKey: (string) \Illuminate\Support\Str::uuid());
TelegramCore::sendLocation(123456789, 35.6892, 51.3890);
TelegramCore::sendPhoto(123456789, config('app.url').'/photo.jpg', [
    'caption' => 'تصویر',
]);
```

For **all** consumer-allowed Telegram methods, use the generic call. This is the forward-compatible path and does not require the SDK to add one PHP method for each Bot API method:

```php
$result = TelegramCore::call('sendPoll', [
    'chat_id' => 123456789,
    'question' => 'انتخاب شما؟',
    'options' => [
        ['text' => 'اول'],
        ['text' => 'دوم'],
    ],
], idempotencyKey: (string) \Illuminate\Support\Str::uuid());
```

Core rejects control-plane methods such as webhook lifecycle, polling/session ownership, and managed-bot token methods. Bot Tokens stay in Core.

## Receive updates

The package registers the configured `TGCORE_CONSUMER_PATH`. Before dispatching an event it verifies timestamp, HMAC signature, Bot UUID and a stable update identity, then suppresses duplicates using the cache.

Listen to:

```php
use KatebSaber\TelegramCore\Events\TelegramUpdateReceived;

Event::listen(TelegramUpdateReceived::class, function (TelegramUpdateReceived $event) {
    $event->type();
    $event->updateDbId(); // preferred deduplication identity
    $event->updateId();
    $event->payload;      // raw Telegram update payload preserved by Core
});
```

Core delivery is **at-least-once**. The SDK performs a first duplicate guard, but business side effects should still be designed idempotently.

## Capabilities and compatibility

```php
$capabilities = TelegramCore::capabilities();
```

Use this to discover the connected Core's contract version, Telegram Bot API snapshot, method allowlist and file/upload limits. Do not assume the server has the same Bot API snapshot as a newer SDK release.

## Files

```php
$bytes = app(\KatebSaber\TelegramCore\Http\TelegramCoreClient::class)
    ->downloadFile($fileId);
```

The Telegram Bot Token and tokenized Telegram file URL never reach the client.

For staged uploads:

```php
$upload = TelegramCore::stageUpload(storage_path('app/private/video.mp4'));
```

Use the returned opaque token in `_tgcore_uploads` according to the Core API contract. See `docs/FILES.md`.

## Documentation

- `docs/INSTALLATION.md` — installation, configuration, deployment
- `docs/AUTHENTICATION.md` — exact signing contract
- `docs/INBOUND-UPDATES.md` — webhook/event handling and deduplication
- `docs/OUTBOUND-API.md` — generic Telegram operations and errors
- `docs/FILES.md` — direct/staged uploads and private downloads
- `docs/COMPATIBILITY.md` — SDK/Core version and capabilities policy
- `docs/RELEASING.md` — GitHub/Packagist public release workflow
- `SECURITY.md` — secret handling and vulnerability reporting

## Public distribution

The recommended public distribution model is GitHub as the source repository and Packagist as the Composer index. Your Telegram Core website can link to both and can expose the latest published SDK metadata. The Core admin release screen can manage repository/package configuration and compare the local SDK version with GitHub/Packagist; publishing actions should use narrowly scoped credentials and explicit administrator confirmation.

## License

MIT.
