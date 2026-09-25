<?php

namespace KatebSaber\TelegramCore\Events;

final class TelegramUpdateReceived
{
    /** @var array<string,mixed> */
    public $tgcore;

    /** @var array<string,mixed> */
    public $payload;

    public function __construct(array $tgcore, array $payload)
    {
        $this->tgcore = $tgcore;
        $this->payload = $payload;
    }

    public function botUuid(): string
    {
        return (string) ($this->tgcore['bot_uuid'] ?? '');
    }

    /** @return int|string|null */
    public function updateId()
    {
        return $this->tgcore['update_id'] ?? null;
    }

    /** @return int|string|null */
    public function updateDbId()
    {
        return $this->tgcore['update_db_id'] ?? null;
    }

    public function type(): ?string
    {
        return isset($this->tgcore['type']) ? (string) $this->tgcore['type'] : null;
    }
}
