<?php
namespace KatebSaber\TelegramCore\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use KatebSaber\TelegramCore\Exceptions\TelegramCoreException;
use KatebSaber\TelegramCore\Exceptions\UpstreamException;
use KatebSaber\TelegramCore\Support\Signer;
final class TelegramCoreClient
{
    public function __construct(private readonly array $config) {}
    public function capabilities(?string $botUuid=null): array { return $this->json('GET', $this->path($botUuid).'/capabilities', [], null, $botUuid); }
    public function callForBot(string $botUuid, string $method, array $parameters=[], ?string $idempotencyKey=null): array { return $this->call($method,$parameters,$idempotencyKey,$botUuid); }
    public function call(string $method, array $parameters=[], ?string $idempotencyKey=null, ?string $botUuid=null): array
    { return $this->json('POST', $this->path($botUuid).'/telegram/'.rawurlencode($method), $parameters, $idempotencyKey, $botUuid); }
    public function sendMessage(int|string $chatId, string $text, array $options=[], ?string $idempotencyKey=null): array { return $this->call('sendMessage', ['chat_id'=>$chatId,'text'=>$text]+$options, $idempotencyKey); }
    public function sendPhoto(int|string $chatId, string $photo, array $options=[], ?string $idempotencyKey=null): array { return $this->call('sendPhoto', ['chat_id'=>$chatId,'photo'=>$photo]+$options, $idempotencyKey); }
    public function sendLocation(int|string $chatId, float $latitude, float $longitude, array $options=[], ?string $idempotencyKey=null): array { return $this->call('sendLocation', ['chat_id'=>$chatId,'latitude'=>$latitude,'longitude'=>$longitude]+$options, $idempotencyKey); }
    public function stageUpload(string $path, ?string $fieldName=null, ?string $idempotencyKey=null, ?string $botUuid=null): array
    {
        if (!is_file($path)) throw new TelegramCoreException('Upload file not found: '.$path);
        // Core's staged-upload contract accepts exactly one multipart field named `file`.
        // Keep the legacy argument for source compatibility but reject a conflicting value.
        if ($fieldName !== null && $fieldName !== '' && $fieldName !== 'file') throw new TelegramCoreException('Staged upload field must be `file`.');
        $bot=$this->bot($botUuid); $timestamp=time(); $field='file'; $fields=[]; $files=[$field=>['path'=>$path,'name'=>basename($path)]];
        $signature=Signer::multipart($this->secret(),$timestamp,$fields,$files,$idempotencyKey);
        $handle=fopen($path,'rb'); if($handle===false) throw new TelegramCoreException('Upload file is not readable: '.$path);
        try { $req=$this->request()->withHeaders($this->headers($bot,$timestamp,$signature,$idempotencyKey))->attach($field,$handle,basename($path)); $res=$req->post($this->base().$this->path($bot).'/uploads'); } finally { if(is_resource($handle)) fclose($handle); }
        return $this->decode($res->status(),$res->json() ?: []);
    }
    public function downloadFile(string $fileId, ?string $botUuid=null): string
    {
        $bot=$this->bot($botUuid); $body=json_encode(['file_id'=>$fileId],JSON_UNESCAPED_SLASHES); $ts=time(); $sig=Signer::json($this->secret(),$ts,$body);
        $res=$this->request()->withHeaders($this->headers($bot,$ts,$sig))->withBody($body,'application/json')->post($this->base().$this->path($bot).'/files/download');
        if(!$res->successful()) throw new UpstreamException($res->status(),$res->json() ?: ['error'=>$res->body()]); return $res->body();
    }
    private function json(string $verb,string $path,array $payload,?string $key,?string $botUuid): array
    { $bot=$this->bot($botUuid); $body=$verb==='GET'?'':json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); $ts=time(); $sig=Signer::json($this->secret(),$ts,$body,$key); $req=$this->request()->withHeaders($this->headers($bot,$ts,$sig,$key)); $res=$verb==='GET'?$req->get($this->base().$path):$req->withBody($body,'application/json')->post($this->base().$path); return $this->decode($res->status(),$res->json() ?: []); }
    private function decode(int $status,array $body): array { if($status>=400) throw new UpstreamException($status,$body); return $body; }
    private function request(): PendingRequest { return Http::acceptJson()->timeout(max(1,(int)($this->config['timeout']??30)))->connectTimeout(max(1,(int)($this->config['connect_timeout']??7))); }
    private function headers(string $bot,int $ts,string $sig,?string $key=null): array { return array_filter(['X-TGCore-Bot-UUID'=>$bot,'X-TGCore-Timestamp'=>(string)$ts,'X-TGCore-Signature'=>$sig,'X-TGCore-Idempotency-Key'=>$key]); }
    private function path(?string $bot=null): string { return '/api/tgcore/v2/consumer/bots/'.rawurlencode($this->bot($bot)); }
    private function bot(?string $bot=null): string { $v=$bot ?: (string)($this->config['bot_uuid']??''); if($v==='')throw new TelegramCoreException('TGCORE_BOT_UUID is not configured.'); return $v; }
    private function secret(): string { $v=(string)($this->config['secret']??''); if($v==='')throw new TelegramCoreException('TGCORE_CONSUMER_SECRET is not configured.'); return $v; }
    private function base(): string { $v=rtrim((string)($this->config['url']??''),'/'); if($v==='')throw new TelegramCoreException('TGCORE_URL is not configured.'); if(!filter_var($v,FILTER_VALIDATE_URL)||strtolower((string)parse_url($v,PHP_URL_SCHEME))!=='https')throw new TelegramCoreException('TGCORE_URL must be a valid HTTPS URL.'); return $v; }
}
