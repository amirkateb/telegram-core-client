# Installation and deployment

1. Install with Composer: `composer require amirkateb/telegram-core-client`.
2. Run `php artisan tgcore:install`.
3. Set `TGCORE_URL`, `TGCORE_BOT_UUID`, `TGCORE_CONSUMER_SECRET` in environment configuration.
4. Keep the Consumer Secret server-side. Never expose it to JavaScript or a mobile app.
5. Run `php artisan tgcore:doctor` and `php artisan tgcore:status`.
6. Configure the client's public HTTPS `/tgcore/webhook` URL as the bot Consumer URL in Core.
7. Add listeners for `TelegramUpdateReceived` and keep business side effects idempotent.
8. In production rebuild Laravel configuration/route caches after environment changes.

`TGCORE_URL` is environment-specific. The SDK never hard-codes the Core domain.
