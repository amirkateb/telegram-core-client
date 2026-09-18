<?php
namespace KatebSaber\TelegramCore\Commands;
use Illuminate\Console\Command;
final class DoctorCommand extends Command
{
    protected $signature='tgcore:doctor'; protected $description='Diagnose Telegram Core client configuration';
    public function handle(): int
    { $checks=['TGCORE_URL'=>config('tgcore.url'),'TGCORE_BOT_UUID'=>config('tgcore.bot_uuid'),'TGCORE_CONSUMER_SECRET'=>config('tgcore.secret')];$ok=true;foreach($checks as $k=>$v){$set=is_string($v)&&$v!=='';$this->line(($set?'[OK] ':'[MISSING] ').$k);$ok=$ok&&$set;}if($ok)$this->call('tgcore:status');return $ok?self::SUCCESS:self::FAILURE; }
}
