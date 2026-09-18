<?php
namespace KatebSaber\TelegramCore\Contracts;
use KatebSaber\TelegramCore\Events\TelegramUpdateReceived;
interface UpdateHandler { public function handle(TelegramUpdateReceived $event): void; }
