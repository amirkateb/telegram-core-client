# Public release workflow

Recommended distribution: public GitHub repository + Packagist Composer package.

Release checklist: run package tests/static checks; update CHANGELOG; choose semantic version; commit/tag signed release; push branch and tag to GitHub; create GitHub Release; ensure Packagist webhook/update sees the tag; verify `composer require amirkateb/telegram-core-client:^X.Y`; compare Core capabilities against documented SDK contract.

Publishing credentials must be narrowly scoped, encrypted at rest in Core admin configuration, never shown after save, and never written to logs/audit payloads. Admin push/release actions require explicit confirmation and should report the resulting commit/tag/release URL. Do not auto-publish on ordinary application deployment.
