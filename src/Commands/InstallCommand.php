<?php
namespace KatebSaber\TelegramCore\Commands;
use Illuminate\Console\Command;
final class InstallCommand extends Command
{
    protected $signature='tgcore:install'; protected $description='Publish Telegram Core client configuration and show required environment keys';
    public function handle(): int
    { $this->call('vendor:publish',['--tag'=>'tgcore-config']); $this->newLine(); $this->info('Add TGCORE_URL, TGCORE_BOT_UUID and TGCORE_CONSUMER_SECRET to .env.'); $this->line('Consumer endpoint: '.url((string)config('tgcore.consumer_path','/tgcore/webhook'))); return self::SUCCESS; }
}
