<?php
namespace KatebSaber\TelegramCore\Tests\Unit;
use KatebSaber\TelegramCore\Support\CanonicalJson;
use KatebSaber\TelegramCore\Support\Signer;
use PHPUnit\Framework\TestCase;
final class SignerTest extends TestCase
{
    public function test_json_signature_matches_contract(): void
    { $this->assertSame('sha256='.hash_hmac('sha256',"1700000000\nkey-1\n{\"a\":1}",'secret'),Signer::json('secret',1700000000,'{"a":1}','key-1')); }
    public function test_canonical_json_sorts_objects_and_preserves_lists(): void
    { $this->assertSame('{"a":{"x":1,"y":2},"b":[2,1]}',CanonicalJson::encode(['b'=>[2,1],'a'=>['y'=>2,'x'=>1]])); }
    public function test_inbound_signature_matches_core_contract(): void
    { $this->assertSame('sha256='.hash_hmac('sha256',"1700000000\n{}",'secret'),Signer::inbound('secret',1700000000,'{}')); }
}
