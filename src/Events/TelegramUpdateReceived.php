<?php
namespace KatebSaber\TelegramCore\Events;
use Illuminate\Foundation\Events\Dispatchable;
final class TelegramUpdateReceived
{
    use Dispatchable;
    public function __construct(public readonly array $tgcore, public readonly array $payload) {}
    public function botUuid(): string { return (string)($this->tgcore['bot_uuid'] ?? ''); }
    public function updateId(): int|string|null { return $this->tgcore['update_id'] ?? null; }
    public function updateDbId(): int|string|null { return $this->tgcore['update_db_id'] ?? null; }
    public function type(): ?string { return $this->tgcore['type'] ?? null; }
}
