<?php
namespace KatebSaber\TelegramCore;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use KatebSaber\TelegramCore\Commands\{DoctorCommand,InstallCommand,StatusCommand};
use KatebSaber\TelegramCore\Http\{TelegramCoreClient,TelegramCoreWebhookController,VerifyTelegramCoreWebhook};
final class TelegramCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    { $this->mergeConfigFrom(__DIR__.'/../config/tgcore.php','tgcore'); $this->app->singleton(TelegramCoreClient::class,fn()=>new TelegramCoreClient((array)config('tgcore'))); }
    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/tgcore.php'=>config_path('tgcore.php')],'tgcore-config');
        if($this->app->runningInConsole())$this->commands([InstallCommand::class,StatusCommand::class,DoctorCommand::class]);
        Route::middleware(['api',VerifyTelegramCoreWebhook::class])->post((string)config('tgcore.consumer_path','/tgcore/webhook'),TelegramCoreWebhookController::class)->name('tgcore.client.webhook');
    }
}
