<?php

namespace KatebSaber\TelegramCore;

use Illuminate\Support\ServiceProvider;
use KatebSaber\TelegramCore\Commands\DoctorCommand;
use KatebSaber\TelegramCore\Commands\InstallCommand;
use KatebSaber\TelegramCore\Commands\StatusCommand;
use KatebSaber\TelegramCore\Http\TelegramCoreClient;

final class TelegramCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tgcore.php', 'tgcore');

        $this->app->singleton(TelegramCoreClient::class, function () {
            return new TelegramCoreClient((array) config('tgcore'));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/tgcore.php' => config_path('tgcore.php'),
        ], 'tgcore-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                StatusCommand::class,
                DoctorCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/tgcore.php');
    }
}
