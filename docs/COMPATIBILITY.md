# Compatibility

## SDK 1.0.0

`amirkateb/telegram-core-client` version `1.0.0` supports Laravel 8 through Laravel 13.

| Laravel | Illuminate constraint | Testbench line | Laravel PHP floor | Verified |
|---|---|---|---:|---|
| 8 | `^8.0` | 6.x | 7.3 | ✅ |
| 9 | `^9.0` | 7.x | 8.0.2 | ✅ |
| 10 | `^10.0` | 8.x | 8.1 | ✅ |
| 11 | `^11.0` | 9.x | 8.2 | ✅ |
| 12 | `^12.0` | 10.x | 8.2 | ✅ |
| 13 | `^13.0` | 11.x | 8.3 | ✅ |

The SDK's own PHP constraint is `^7.3|^8.0`. Composer will apply the stricter PHP requirement imposed by the Laravel version installed in the Consumer application.

## Runtime contract

SDK `1.0.0` targets TGCore Consumer contract version `2` and expects:

- HMAC SHA-256 authentication;
- `X-TGCore-Bot-UUID`;
- `X-TGCore-Timestamp`;
- `X-TGCore-Signature`;
- optional `X-TGCore-Idempotency-Key` for retry-safe writes;
- signed inbound deliveries to the configured Consumer endpoint.

## Compatibility policy

- Minor SDK releases may add helpers without changing the Core contract.
- Breaking SDK API changes require a new major SDK version.
- New Laravel major versions are added only after dependency resolution and the package test suite pass on that major line.
- Consumer applications should use `TelegramCore::capabilities()` for runtime feature discovery when the Core and SDK are upgraded independently.

## Release verification

For `1.0.0`, isolated dependency environments were created for all six supported Laravel majors. Each environment executed the same suite successfully: 8 tests / 27 assertions.
