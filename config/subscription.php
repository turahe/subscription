<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PlanSubscription Tables
    |--------------------------------------------------------------------------
    |
    |
    */

    'tables' => [
        'plans' => 'plans',
        'features' => 'plan_features',
        'subscriptions' => 'plan_subscriptions',
        'subscription_usage' => 'plan_subscription_usage',
    ],

    /*
    |--------------------------------------------------------------------------
    | PlanSubscription Models
    |--------------------------------------------------------------------------
    |
    | Models used to manage subscriptions. You can replace to use your own models,
    | but make sure that you have the same functionalities or that your models
    | extend from each model that you are going to replace.
    |
    */

    'models' => [
        'plan' => \Turahe\Subscription\Models\Plan::class,
        'feature' => \Turahe\Subscription\Models\PlanFeature::class,
        'subscription' => \Turahe\Subscription\Models\PlanSubscription::class,
        'subscription_usage' => \Turahe\Subscription\Models\PlanSubscriptionUsage::class,
    ],
];
