<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EspayServiceProvider extends ServiceProvider
{
public function register()
{
    $this->app->singleton(EspayService::class, function ($app) {
        return new EspayService();
    });
}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
