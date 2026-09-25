<?php

namespace KatebSaber\TelegramCore\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use KatebSaber\TelegramCore\Http\TelegramCoreClient;
use KatebSaber\TelegramCore\Support\Version;
use KatebSaber\TelegramCore\Tests\TestCase;

class PackageBootTest extends TestCase
{
    public function test_package_registers_client_and_webhook_route(): void
    {
        $this->assertInstanceOf(TelegramCoreClient::class, $this->app->make(TelegramCoreClient::class));
        $this->assertSame('1.0.0', Version::SDK);

        $route = $this->app['router']->getRoutes()->getByName('tgcore.client.webhook');
        $this->assertNotNull($route);
        $this->assertSame('tgcore/webhook', $route->uri());
        $this->assertContains('POST', $route->methods());
    }

    public function test_console_commands_are_registered(): void
    {
        $this->assertSame(0, Artisan::call('list', ['--raw' => true]));
        $output = Artisan::output();

        $this->assertStringContainsString('tgcore:install', $output);
        $this->assertStringContainsString('tgcore:status', $output);
        $this->assertStringContainsString('tgcore:doctor', $output);
    }}
