<?php

declare(strict_types=1);

namespace Turahe\Subscription;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public readonly string $packageName;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->packageName = 'subscription';
    }

    public function boot(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'subscription');

        if ($this->app instanceof Application) {
            $this->loadMigrationsFrom($this->getMigrationPath());

            $this->publishes(
                [
                    $this->getConfigPath() => config_path('subscription.php'),
                ],
                'config'
            );
        }
    }

    public function getConfigPath(): string
    {
        return __DIR__.'/../config/subscription.php';
    }

    public function getMigrationPath(): string
    {
        return __DIR__.'/../database/migrations';
    }
}
