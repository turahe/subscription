<?php

namespace Turahe\Subscription\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
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
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('subby', [
            'main_subscription_tag' => 'main',
            'fallback_plan_tag' => null,
            // Database Tables
            'tables' => [
                'plans' => 'plans',
                'plan_combinations' => 'plan_combinations',
                'plan_features' => 'plan_features',
                'plan_subscriptions' => 'plan_subscriptions',
                'plan_subscription_features' => 'plan_subscription_features',
                'plan_subscription_schedules' => 'plan_subscription_schedules',
                'plan_subscription_usage' => 'plan_subscription_usage',
            ],
            // Models
            'models' => [
                'plan' => \Turahe\Subscription\Models\Plan::class,
                //                'plan_combination' => \Turahe\PlanSubscription\Models\PlanCombination::class,
                'plan_feature' => \Turahe\Subscription\Models\PlanFeature::class,
                'plan_subscription' => \Turahe\Subscription\Models\PlanSubscription::class,
                //                'plan_subscription_feature' => \Turahe\PlanSubscription\Models\PlanSubscriptionFeature::class,
                //                'plan_subscription_schedule' => \Turahe\PlanSubscription\Models\PlanSubscriptionSchedule::class,
                'plan_subscription_usage' => \Turahe\Subscription\Models\PlanSubscriptionUsage::class,
            ],
            'services' => [
                'payment_methods' => [
                    //                    'success' => \Turahe\PlanSubscription\Tests\Services\PaymentMethods\SucceededPaymentMethod::class,
                    //                    'fail' => \Turahe\PlanSubscription\Tests\Services\PaymentMethods\FailedPaymentMethod::class
                ],
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
        Config::set('auth.providers.users.model', User::class);

        $this->app['db']->connection()->getSchemaBuilder()->create('dummies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('custom_column_sort');
            $table->integer('record_ordering');
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }
}
