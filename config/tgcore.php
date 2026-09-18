<?php
return [
    'url' => rtrim((string) env('TGCORE_URL', ''), '/'),
    'bot_uuid' => (string) env('TGCORE_BOT_UUID', ''),
    'secret' => (string) env('TGCORE_CONSUMER_SECRET', ''),
    'consumer_path' => env('TGCORE_CONSUMER_PATH', '/tgcore/webhook'),
    'timeout' => (int) env('TGCORE_TIMEOUT', 30),
    'connect_timeout' => (int) env('TGCORE_CONNECT_TIMEOUT', 7),
    'signature_tolerance' => (int) env('TGCORE_SIGNATURE_TOLERANCE', 300),
    'contract_version' => 2,
];
