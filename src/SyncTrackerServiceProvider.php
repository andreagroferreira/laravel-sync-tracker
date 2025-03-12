<?php

namespace WizardingCode\FlowNetwork\SyncTracker;

use Illuminate\Support\ServiceProvider;

class SyncTrackerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/sync-tracker.php' => config_path('sync-tracker.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sync-tracker.php', 'sync-tracker'
        );

        // Register the main class to use with the facade
        $this->app->singleton('sync-tracker', function () {
            return new SyncTracker();
        });
    }
}