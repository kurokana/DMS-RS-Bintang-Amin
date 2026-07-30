<?php

namespace App\Providers;

use App\Contracts\SimrsQueueServiceInterface;
use App\Services\Simrs\SimrsQueueManager;
use Illuminate\Support\ServiceProvider;

class SimrsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SimrsQueueServiceInterface::class, SimrsQueueManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
