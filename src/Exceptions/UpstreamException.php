<?php
namespace KatebSaber\TelegramCore\Exceptions;
class UpstreamException extends TelegramCoreException
{
    public function __construct(public readonly int $status, public readonly array $response)
    { parent::__construct((string) ($response['error'] ?? $response['telegram']['description'] ?? 'Telegram Core request failed'), $status); }
}
