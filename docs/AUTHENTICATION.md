# Authentication and signatures

Outbound JSON requests use headers `X-TGCore-Bot-UUID`, `X-TGCore-Timestamp`, `X-TGCore-Signature`, and optionally `X-TGCore-Idempotency-Key`.

JSON signing material is `timestamp + "\n" + [idempotencyKey + "\n"] + exactRawBody`. Signature is `sha256=` plus HMAC-SHA256 using the Consumer Secret.

Multipart signing material is `timestamp + "\n" + [idempotencyKey + "\n"] + filesContentHash + "\n" + canonicalJson(nonFileFields)`. Each sorted file contributes `field:sha256(bytes):size:sha256(originalFilename);`; SHA-256 of the concatenated entries is `filesContentHash`. Canonical JSON recursively sorts object keys and preserves list order.

Inbound Core deliveries use `timestamp + "\n" + exactRawBody` and the same HMAC format. The SDK validates the timestamp window, signature, Bot UUID and stable update identity before dispatching the Laravel event.
