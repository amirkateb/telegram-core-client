# Contributing

Thank you for improving `amirkateb/telegram-core-client`.

## Compatibility contract

SDK `1.x` supports Laravel **8 through 13**. Keep package source syntax compatible with the SDK PHP floor (`^7.3|^8.0`) unless a future major release intentionally raises it. The effective runtime PHP requirement is still the minimum required by the installed Laravel major.

When changing framework-facing code, consider all six supported Laravel lines. Avoid helpers, signatures, language syntax, or Illuminate APIs that only exist in one recent major unless a compatibility layer is provided.

## Rules

- Keep public APIs backward compatible within the same SDK major version.
- Never add a direct fallback to `api.telegram.org`; Bot Tokens belong to TGCore.
- Never log or expose Consumer Secrets, Bot Tokens, signatures, or private payloads.
- Add tests for authentication, signing, transport, route, upload, or inbound-delivery changes.
- Update `README.md`, `README.fa.md`, docs, `CHANGELOG.md`, and `VERSION` when release behavior changes.
- Never commit `vendor/`, `composer.lock`, credentials, generated archives, or local environment files.

## Tests

```bash
composer install
composer test
```

The release matrix uses the official Testbench mapping:

| Laravel | Testbench |
|---|---:|
| 8 | 6.x |
| 9 | 7.x |
| 10 | 8.x |
| 11 | 9.x |
| 12 | 10.x |
| 13 | 11.x |

A compatibility change is not complete until the relevant matrix rows pass.
