<?php

namespace KatebSaber\TelegramCore\Tests\Feature;

use Illuminate\Support\Facades\Event;
use KatebSaber\TelegramCore\Events\TelegramUpdateReceived;
use KatebSaber\TelegramCore\Support\Signer;
use KatebSaber\TelegramCore\Tests\TestCase;

class InboundWebhookTest extends TestCase
{
    public function test_valid_signed_update_dispatches_laravel_event_and_duplicate_is_suppressed(): void
    {
        Event::fake([TelegramUpdateReceived::class]);

        $payload = [
            'tgcore' => [
                'bot_uuid' => '11111111-1111-4111-8111-111111111111',
                'update_db_id' => '501',
                'update_id' => 9001,
                'type' => 'message',
            ],
            'payload' => [
                'update_id' => 9001,
                'message' => ['text' => 'hello'],
            ],
        ];
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $signature = Signer::inbound('test-consumer-secret', $timestamp, $body);
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TGCORE_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_TGCORE_SIGNATURE' => $signature,
            'HTTP_X_TGCORE_BOT_UUID' => '11111111-1111-4111-8111-111111111111',
        ];

        $first = $this->call('POST', '/tgcore/webhook', [], [], [], $headers, $body);
        $first->assertOk()->assertJson(['ok' => true]);
        Event::assertDispatched(TelegramUpdateReceived::class, 1);

        $second = $this->call('POST', '/tgcore/webhook', [], [], [], $headers, $body);
        $second->assertOk()->assertJson(['ok' => true, 'duplicate' => true]);
        Event::assertDispatched(TelegramUpdateReceived::class, 1);
    }
}
