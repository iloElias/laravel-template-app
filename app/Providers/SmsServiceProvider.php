<?php

namespace App\Providers;

use App\Services\SmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register the SmsService class as a singleton.
     */
    public function register(): void
    {
        $this->app->singleton('sms', function () {
            return new SmsService();
        });
    }

    public function boot(): void
    {
    }
}
