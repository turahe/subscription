<?php

namespace Turahe\Subscription\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Turahe\Subscription\Tests\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->loadMigrationsFrom(__DIR__.'./../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Turahe\Subscription\SubscriptionServiceProvider::class,
            \Spatie\EloquentSortable\EloquentSortableServiceProvider::class,
            \Turahe\UserStamps\UserStampsServiceProvider::class,
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('userstamps.users_table_column_type', 'ulid');
        $app['config']->set('subscription', [
            'main_subscription_tag' => 'main',
            'fallback_plan_tag' => null,
            // Database Tables
            'tables' => [
                'plans' => 'plans',
                'combinations' => 'plan_combinations',
                'features' => 'plan_features',
                'subscriptions' => 'plan_subscriptions',
                'subscription_features' => 'plan_subscription_features',
                'subscription_schedules' => 'plan_subscription_schedules',
                'subscription_usage' => 'plan_subscription_usage',
            ],
            // Models
            'models' => [
                'plan' => \Turahe\Subscription\Models\Plan::class,
                'feature' => \Turahe\Subscription\Models\PlanFeature::class,
            ],
        ]);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', 'base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8=');
    }

    protected function setUpDatabase()
    {
        $this->app['config']->set('auth.providers.users.model', User::class);

        $this->app['db']->connection()->getSchemaBuilder()->create('dummies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('custom_column_sort');
            $table->integer('record_ordering');
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('organizations', function ($table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();
        });
    }
}
