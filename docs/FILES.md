# Files and media

Core supports ordinary multipart, staged uploads, Telegram `attach://` references and private file download. Runtime limits are discoverable from `capabilities()` and should be preferred over hard-coded limits.

`stageUpload()` signs file bytes, size, field name and original filename. It returns an opaque token; Core stores a hash and private file metadata. Tokens are bot-scoped and expire.

`downloadFile()` requests by Telegram `file_id`; Core resolves/downloads/caches the file without exposing a Bot Token or tokenized Telegram URL.
