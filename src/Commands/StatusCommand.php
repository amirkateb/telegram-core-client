<?php
namespace KatebSaber\TelegramCore\Commands;
use Illuminate\Console\Command;
use KatebSaber\TelegramCore\Http\TelegramCoreClient;
use Throwable;
final class StatusCommand extends Command
{
    protected $signature='tgcore:status'; protected $description='Check Telegram Core connectivity and capabilities';
    public function handle(TelegramCoreClient $client): int
    { try{$c=$client->capabilities();$this->info('Telegram Core connection: OK');$this->line('Contract: '.data_get($c,'contract_version',data_get($c,'meta.contract_version','?')));$this->line('Bot API: '.data_get($c,'telegram_bot_api_version','?'));return self::SUCCESS;}catch(Throwable $e){$this->error('Telegram Core connection failed: '.$e->getMessage());return self::FAILURE;} }
}
