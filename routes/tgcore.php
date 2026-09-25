<?php

use Illuminate\Support\Facades\Route;
use KatebSaber\TelegramCore\Http\TelegramCoreWebhookController;
use KatebSaber\TelegramCore\Http\VerifyTelegramCoreWebhook;

Route::middleware(['api', VerifyTelegramCoreWebhook::class])
    ->post((string) config('tgcore.consumer_path', '/tgcore/webhook'), TelegramCoreWebhookController::class)
    ->name('tgcore.client.webhook');
