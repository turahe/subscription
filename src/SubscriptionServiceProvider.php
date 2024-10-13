<?php

namespace Turahe\Subscription;

use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/subscription.php', 'subscription');

        if ($this->app instanceof \Illuminate\Foundation\Application) {
            $databasePath = __DIR__.'/../database/migrations';
            $this->loadMigrationsFrom($databasePath);

            $this->publishes(
                [
                    __DIR__.'/../config/subscription.php' => config_path('subscription.php'),
                ],
                'config'
            );
        }
    }
}
