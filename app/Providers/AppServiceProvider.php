<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\BpjsWardAdapterInterface::class,
            \App\Adapters\OfficeBpjsWardAdapter::class
        );

        $this->app->bind(
            \App\Contracts\BpjsOperatingRoomAdapterInterface::class,
            \App\Adapters\OfficeBpjsOperatingRoomAdapter::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'ward_class' => \App\Models\WardClass::class,
            'operating_room' => \App\Models\OperatingRoom::class,
            'ward_summary' => \App\Models\WardClass::class, // Dummy map to prevent MorphTo crash on eager load
        ]);
    }
}
