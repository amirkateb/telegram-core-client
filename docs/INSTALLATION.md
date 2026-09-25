# Installation and deployment

## Supported applications

SDK `1.0.0` supports Laravel **8, 9, 10, 11, 12 and 13**.

The package itself allows PHP `^7.3|^8.0`; Composer will apply the stricter PHP requirement of your Laravel major. For example, Laravel 13 requires PHP 8.3 or newer.

## Install

```bash
composer require amirkateb/telegram-core-client:^1.0
php artisan tgcore:install
```

Laravel auto-discovery registers the provider and facade automatically.

## Configure

Add the TGCore connection values supplied by the Core administrator:

```dotenv
TGCORE_URL=https://tg.example.com
TGCORE_BOT_UUID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
TGCORE_CONSUMER_SECRET=replace-with-private-consumer-secret
TGCORE_CONSUMER_PATH=/tgcore/webhook
TGCORE_TIMEOUT=30
TGCORE_CONNECT_TIMEOUT=7
TGCORE_SIGNATURE_TOLERANCE=300
```

`TGCORE_URL` is environment-specific and must use HTTPS. The SDK never hard-codes the Core domain and never needs the Telegram Bot Token.

## Validate

```bash
php artisan tgcore:doctor
php artisan tgcore:status
```

Then configure the application's public HTTPS Consumer URL in TGCore, usually:

```text
https://your-app.example.com/tgcore/webhook
```

Add listeners for `TelegramUpdateReceived` and keep irreversible business side effects idempotent because delivery is at-least-once.

## Production deployment

After changing environment configuration, rebuild the Laravel caches using the commands appropriate for your application version, for example:

```bash
php artisan optimize:clear
php artisan optimize
```

Keep `TGCORE_CONSUMER_SECRET` server-side. Never expose it to JavaScript, mobile clients, browser bundles, logs, debug pages, or public repositories.
