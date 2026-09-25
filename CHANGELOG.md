# Changelog

All notable changes to `amirkateb/telegram-core-client` are documented here.

## 1.0.0 — 2026-09-25

### Added
- Official stable `1.0.0` release marker.
- Laravel 8, 9, 10, 11, 12 and 13 compatibility.
- Guzzle-based outbound transport compatible across the supported Laravel matrix.
- Package boot tests, signed inbound webhook tests, duplicate suppression tests and outbound request tests.
- Bilingual English/Persian README documentation.
- Explicit SDK version constant at `KatebSaber\TelegramCore\Support\Version::SDK`.

### Changed
- Composer Illuminate constraints now cover Laravel `^8.0` through `^13.0`.
- PHP package floor is `^7.3|^8.0`, allowing Laravel 8 installations while newer Laravel versions naturally enforce their own higher PHP requirements.
- Removed PHP 8.1-only `readonly` syntax from public SDK classes.
- Removed the unnecessary `Illuminate\Foundation` event dependency so the package stays component-friendly.
- Outbound HTTP calls no longer depend on version-specific Laravel HTTP client internals.

### Verified
The same package suite passed independently on:

- Laravel 8.83.x / Testbench 6.x — 8 tests, 27 assertions
- Laravel 9.52.x / Testbench 7.x — 8 tests, 27 assertions
- Laravel 10.50.x / Testbench 8.x — 8 tests, 27 assertions
- Laravel 11.56.x / Testbench 9.x — 8 tests, 27 assertions
- Laravel 12.69.x / Testbench 10.x — 8 tests, 27 assertions
- Laravel 13.33.x / Testbench 11.x — 8 tests, 27 assertions

## 0.1.0

Initial development release.
