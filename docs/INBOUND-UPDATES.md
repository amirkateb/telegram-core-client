# Inbound updates

Core posts `{ "tgcore": {...}, "payload": {...} }` to the configured consumer endpoint. Metadata includes contract version, company/bot identity, Core update DB identity, Telegram update ID, detected type, receive time and redrive metadata.

Use `TelegramUpdateReceived`. `update_db_id` is the preferred stable Core deduplication identity; `(bot_uuid, update_id)` is the fallback. Delivery is at-least-once, so irreversible business operations must remain idempotent even when the SDK cache guard is present.

The raw Telegram payload remains available for advanced/new fields. Application code should prefer stable metadata/event helpers where possible.
