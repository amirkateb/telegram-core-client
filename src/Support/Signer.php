<?php
namespace KatebSaber\TelegramCore\Support;
final class Signer
{
    public static function json(string $secret, int $timestamp, string $body, ?string $idempotencyKey = null): string
    { return 'sha256='.hash_hmac('sha256', $timestamp."\n".($idempotencyKey ? $idempotencyKey."\n" : '').$body, $secret); }
    public static function inbound(string $secret, int $timestamp, string $body): string
    { return 'sha256='.hash_hmac('sha256', $timestamp."\n".$body, $secret); }
    public static function multipart(string $secret, int $timestamp, array $fields, array $files, ?string $idempotencyKey = null): string
    {
        ksort($files); $buf='';
        foreach ($files as $field=>$file) {
            $path=$file['path']; $buf.=$field.':'.hash_file('sha256',$path).':'.filesize($path).':'.hash('sha256',(string)$file['name']).';';
        }
        $contentHash=hash('sha256',$buf);
        $material=$timestamp."\n".($idempotencyKey ? $idempotencyKey."\n" : '').$contentHash."\n".CanonicalJson::encode($fields);
        return 'sha256='.hash_hmac('sha256',$material,$secret);
    }
}
