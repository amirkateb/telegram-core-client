<?php
namespace KatebSaber\TelegramCore\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KatebSaber\TelegramCore\Events\TelegramUpdateReceived;
final class TelegramCoreWebhookController
{
    public function __invoke(Request $request): JsonResponse
    { $event=$request->attributes->get('tgcore_event'); if($event instanceof TelegramUpdateReceived) event($event); return response()->json(['ok'=>true]); }
}
