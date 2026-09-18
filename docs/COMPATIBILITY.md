# Compatibility policy

SDK major versions track breaking client-contract changes, not every Telegram Bot API release. Telegram method availability is discovered from Core's capabilities endpoint. Within one SDK major version, public configuration keys, event semantics and method behavior should remain backward compatible.

A newer Core may expose new Telegram methods through generic `call()` without requiring an SDK release. A breaking Core contract requires a new contract/SDK compatibility path.
