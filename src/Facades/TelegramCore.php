<?php
namespace KatebSaber\TelegramCore\Facades;
use Illuminate\Support\Facades\Facade;
class TelegramCore extends Facade { protected static function getFacadeAccessor(): string { return \KatebSaber\TelegramCore\Http\TelegramCoreClient::class; } }
