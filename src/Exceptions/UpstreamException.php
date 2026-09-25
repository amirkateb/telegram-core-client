<?php

namespace KatebSaber\TelegramCore\Exceptions;

class UpstreamException extends TelegramCoreException
{
    /** @var int */
    public $status;

    /** @var array<string,mixed> */
    public $response;

    public function __construct(int $status, array $response)
    {
        $this->status = $status;
        $this->response = $response;

        parent::__construct((string) ($response['error'] ?? $response['telegram']['description'] ?? 'Telegram Core request failed'), $status);
    }
}
