<?php

namespace KatebSaber\TelegramCore\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use KatebSaber\TelegramCore\Http\TelegramCoreClient;
use KatebSaber\TelegramCore\Tests\TestCase;

class ClientHttpTest extends TestCase
{
    public function test_send_message_uses_core_gateway_and_authentication_headers(): void
    {
        $history = [];
        $client = $this->client([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['ok' => true, 'result' => ['message_id' => 99]])),
        ], $history);

        $result = $client->sendMessage(123456, 'hello', [], 'idem-1');

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/tgcore/v2/consumer/bots/11111111-1111-4111-8111-111111111111/telegram/sendMessage', $request->getUri()->getPath());
        $this->assertSame('11111111-1111-4111-8111-111111111111', $request->getHeaderLine('X-TGCore-Bot-UUID'));
        $this->assertSame('idem-1', $request->getHeaderLine('X-TGCore-Idempotency-Key'));
        $this->assertStringStartsWith('sha256=', $request->getHeaderLine('X-TGCore-Signature'));
    }

    public function test_capabilities_endpoint_is_available(): void
    {
        $history = [];
        $client = $this->client([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'contract_version' => 2,
                'telegram_bot_api_version' => 'test',
            ])),
        ], $history);

        $result = $client->capabilities();

        $this->assertSame(2, $result['contract_version']);
        $this->assertSame('GET', $history[0]['request']->getMethod());
    }

    private function client(array $responses, array &$history): TelegramCoreClient
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $http = new Client(['handler' => $stack, 'base_uri' => 'https://core.example.test']);

        return new TelegramCoreClient([
            'url' => 'https://core.example.test',
            'bot_uuid' => '11111111-1111-4111-8111-111111111111',
            'secret' => 'test-consumer-secret',
            'timeout' => 30,
            'connect_timeout' => 7,
        ], $http);
    }
}
