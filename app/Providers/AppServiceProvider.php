<?php

namespace App\Providers;

use App\Services\Google\Contracts\DistanceMatrixClientInterface;
use App\Services\Google\Contracts\PlacesClientInterface;
use App\Services\Google\Implementations\HttpDistanceMatrixClient;
use App\Services\Google\Implementations\HttpPlacesClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PlacesClientInterface::class, HttpPlacesClient::class);
        $this->app->bind(DistanceMatrixClientInterface::class, HttpDistanceMatrixClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
