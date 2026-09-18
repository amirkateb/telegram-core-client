<?php
namespace KatebSaber\TelegramCore\Http;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use KatebSaber\TelegramCore\Events\TelegramUpdateReceived;
use KatebSaber\TelegramCore\Support\Signer;
use Symfony\Component\HttpFoundation\Response;
final class VerifyTelegramCoreWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret=(string)config('tgcore.secret',''); $ts=(string)$request->header('X-TGCore-Timestamp',''); $sig=(string)$request->header('X-TGCore-Signature',''); $bot=(string)$request->header('X-TGCore-Bot-UUID','');
        if($secret===''||$ts===''||$sig===''||!ctype_digit($ts)) return response()->json(['ok'=>false,'error'=>'tgcore_auth_missing'],401);
        if(abs(time()-(int)$ts)>max(1,(int)config('tgcore.signature_tolerance',300))) return response()->json(['ok'=>false,'error'=>'tgcore_timestamp_out_of_range'],401);
        $expected=Signer::inbound($secret,(int)$ts,(string)$request->getContent()); if(!hash_equals($expected,$sig)) return response()->json(['ok'=>false,'error'=>'tgcore_invalid_signature'],401);
        $payload=$request->json()->all(); $meta=(array)($payload['tgcore']??[]);
        if($bot===''||$bot!==(string)($meta['bot_uuid']??'')) return response()->json(['ok'=>false,'error'=>'tgcore_bot_mismatch'],401);
        $identity=(string)($meta['update_db_id']??''); if($identity==='') return response()->json(['ok'=>false,'error'=>'tgcore_update_identity_missing'],422);
        if(!Cache::add('tgcore:sdk:inbound:'.$bot.':'.$identity,1,86400*7)) return response()->json(['ok'=>true,'duplicate'=>true]);
        $request->attributes->set('tgcore_event',new TelegramUpdateReceived($meta,(array)($payload['payload']??[])));
        return $next($request);
    }
}
