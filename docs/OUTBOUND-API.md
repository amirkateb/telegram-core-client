# Outbound Telegram API

`TelegramCoreClient::call($method, $parameters, $idempotencyKey)` targets contract-v2 `/telegram/{method}`. Core's runtime method registry is authoritative. The SDK intentionally keeps a generic call so all server-allowed Telegram features are immediately usable without waiting for a wrapper release.

Use an idempotency key for mutations when a caller may retry after timeout/network failure. Core persists durable idempotency state and will not blindly repeat an ambiguous Telegram side effect.

HTTP 4xx/5xx responses throw `UpstreamException`, which exposes the HTTP status and sanitized Core response. Telegram validation/rate-limit descriptions remain in the response envelope where provided by Core.
