<?php

namespace KatebSaber\TelegramCore\Tests;

use KatebSaber\TelegramCore\TelegramCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [TelegramCoreServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('tgcore.url', 'https://core.example.test');
        $app['config']->set('tgcore.bot_uuid', '11111111-1111-4111-8111-111111111111');
        $app['config']->set('tgcore.secret', 'test-consumer-secret');
        $app['config']->set('tgcore.consumer_path', '/tgcore/webhook');
        $app['config']->set('cache.default', 'array');
    }
}
