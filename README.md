<p align="center">
    <a href="https://laravel.com">
        <img alt="Laravel v11.x" src="https://img.shields.io/badge/Laravel-v11.x-FF2D20">
    </a>
    <a href="https://github.com/turahe/subscription/actions/workflows/php.yml">
        <img src="https://github.com/turahe/subscription/actions/workflows/php.yml/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/turahe/subscription">
        <img src="https://img.shields.io/packagist/dt/turahe/subscription" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/turahe/subscription">
        <img src="https://img.shields.io/packagist/v/turahe/subscription.svg?label=Packagist" alt="Packagist" />
    </a>
    <a href="https://github.com/turahe/subscription/blob/main/LICENSE">
        <img src="https://img.shields.io/packagist/l/turahe/subscription.svg?label=License" alt="License" />
    </a>
</p>

# Laravel Subscription Package

A flexible and feature-rich subscription management system for Laravel applications. This package provides everything you need to implement subscription-based services, SaaS applications, or any business model that requires recurring billing and plan management.

## Features

- **Plan Management**: Create and manage subscription plans with flexible pricing
- **Feature System**: Define features with usage limits and resettable periods
- **Trial Periods**: Built-in trial period support for new subscriptions
- **Grace Periods**: Configure grace periods for failed payments
- **Usage Tracking**: Track feature usage with automatic limits enforcement
- **Multiple Subscriptions**: Support for multiple subscription types per user
- **Subscription Lifecycle**: Complete subscription lifecycle management (create, renew, cancel, change plans)
- **Eloquent Integration**: Seamless integration with Laravel's Eloquent ORM
- **Soft Deletes**: Built-in soft delete support for data integrity
- **User Stamps**: Automatic tracking of who created/modified records
- **Sortable**: Built-in sorting capabilities for plans and features

## Requirements

- PHP 8.1+
- Laravel 11.x
- MySQL/PostgreSQL/SQLite

## Installation

1. **Install the package via Composer:**

    ```bash
    composer require turahe/subscription
    ```

2. **Publish the configuration and migration files:**

    ```bash
    php artisan vendor:publish --provider="Turahe\Subscription\SubscriptionServiceProvider"
    ```

3. **Run the migrations:**

    ```bash
    php artisan migrate
    ```

4. **Done!** The package is now ready to use.

## Quick Start

### 1. Add Subscription Support to Your User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Turahe\Subscription\Traits\HasPlanSubscriptions;

class User extends Authenticatable
{
    use HasPlanSubscriptions;
    
    // ... your existing code
}
```

### 2. Create a Plan

```php
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Enums\Interval;

// Create a plan
$plan = Plan::create([
    'name' => 'Pro Plan',
    'description' => 'Professional plan with advanced features',
    'price' => 29.99,
    'signup_fee' => 0,
    'invoice_period' => 1,
    'invoice_interval' => Interval::MONTH,
    'trial_period' => 14,
    'trial_interval' => Interval::DAY,
    'currency' => 'USD',
    'is_active' => true,
]);

// Add features to the plan
$plan->features()->saveMany([
    new PlanFeature([
        'name' => 'api_calls',
        'value' => 1000,
        'sort_order' => 1,
        'resettable_period' => 1,
        'resettable_interval' => 'month'
    ]),
    new PlanFeature([
        'name' => 'storage_gb',
        'value' => 10,
        'sort_order' => 2
    ]),
    new PlanFeature([
        'name' => 'priority_support',
        'value' => 'Y',
        'sort_order' => 3
    ])
]);
```

### 3. Subscribe a User

```php
$user = User::find(1);
$plan = Plan::find(1);

// Create a new subscription
$subscription = $user->newPlanSubscription('main', $plan);
```

### 4. Check Subscription Status

```php
// Check if user is subscribed to a plan
$user->subscribedTo($plan->id);

// Check if subscription is active
$user->planSubscription('main')->active();

// Check if on trial
$user->planSubscription('main')->onTrial();
```

## Detailed Usage

### Plan Management

#### Creating Plans

```php
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Enums\Interval;

$plan = Plan::create([
    'name' => 'Enterprise',
    'description' => 'Enterprise plan with unlimited features',
    'price' => 99.99,
    'signup_fee' => 50.00,
    'invoice_period' => 1,
    'invoice_interval' => Interval::MONTH,
    'trial_period' => 30,
    'trial_interval' => Interval::DAY,
    'grace_period' => 7,
    'grace_interval' => 'day',
    'currency' => 'USD',
    'is_active' => true,
]);
```

#### Plan Features

```php
// Add features to a plan
$plan->features()->saveMany([
    new PlanFeature([
        'name' => 'users',
        'value' => 10,
        'sort_order' => 1
    ]),
    new PlanFeature([
        'name' => 'projects',
        'value' => 50,
        'sort_order' => 2
    ]),
    new PlanFeature([
        'name' => 'api_requests',
        'value' => 10000,
        'sort_order' => 3,
        'resettable_period' => 1,
        'resettable_interval' => 'month'
    ])
]);

// Get plan features
$features = $plan->features;

// Get specific feature
$feature = $plan->getFeatureBySlug('users');
```

### Subscription Management

#### Creating Subscriptions

```php
$user = User::find(1);
$plan = Plan::find(1);

// Create subscription with custom start date
$subscription = $user->newPlanSubscription('main', $plan, Carbon::now()->addDays(7));
```

#### Changing Plans

```php
$newPlan = Plan::find(2);
$subscription = $user->planSubscription('main');

// Change to new plan
$subscription->changePlan($newPlan);
```

#### Canceling Subscriptions

```php
// Cancel at period end (default)
$user->planSubscription('main')->cancel();

// Cancel immediately
$user->planSubscription('main')->cancel(true);
```

#### Renewing Subscriptions

```php
// Renew subscription
$user->planSubscription('main')->renew();
```

### Feature Usage

#### Checking Feature Availability

```php
// Check if user can use a feature
$canUse = $user->planSubscription('main')->canUseFeature('api_calls');

// Get remaining uses
$remaining = $user->planSubscription('main')->getFeatureRemainings('api_calls');

// Get current usage
$usage = $user->planSubscription('main')->getFeatureUsage('api_calls');
```

#### Recording Feature Usage

```php
// Record single usage
$user->planSubscription('main')->recordFeatureUsage('api_calls');

// Record multiple uses
$user->planSubscription('main')->recordFeatureUsage('api_calls', 5);

// Override usage (non-incremental)
$user->planSubscription('main')->recordFeatureUsage('api_calls', 10, false);
```

#### Reducing Feature Usage

```php
// Reduce usage by 1
$user->planSubscription('main')->reduceFeatureUsage('api_calls');

// Reduce usage by specific amount
$user->planSubscription('main')->reduceFeatureUsage('api_calls', 3);
```

### Subscription Queries

#### Active Subscriptions

```php
// Get all active subscriptions for a user
$activeSubscriptions = $user->activePlanSubscriptions();

// Get subscribed plans
$subscribedPlans = $user->subscribedPlans();
```

#### Subscription Scopes

```php
use Turahe\Subscription\Models\PlanSubscription;

// Get subscriptions by plan
$subscriptions = PlanSubscription::byPlanId($planId)->get();

// Get user's subscriptions
$userSubscriptions = PlanSubscription::ofSubscriber($user)->get();

// Get subscriptions ending trial in 3 days
$endingTrials = PlanSubscription::findEndingTrial(3)->get();

// Get subscriptions ending period in 7 days
$endingPeriods = PlanSubscription::findEndingPeriod(7)->get();
```

## Configuration

The package configuration is located in `config/subscription.php`:

```php
return [
    'tables' => [
        'plans' => 'plans',
        'features' => 'plan_features',
        'subscriptions' => 'plan_subscriptions',
        'subscription_usage' => 'plan_subscription_usage',
    ],
    
    'models' => [
        'plan' => \Turahe\Subscription\Models\Plan::class,
        'feature' => \Turahe\Subscription\Models\PlanFeature::class,
        'subscription' => \Turahe\Subscription\Models\PlanSubscription::class,
        'subscription_usage' => \Turahe\Subscription\Models\PlanSubscriptionUsage::class,
    ],
];
```

## Models

The package includes four main models:

- **Plan**: Manages subscription plans and their features
- **PlanFeature**: Defines features available in plans
- **PlanSubscription**: Handles user subscriptions to plans
- **PlanSubscriptionUsage**: Tracks feature usage for subscriptions

## Events

The package fires several events that you can listen to:

- `Turahe\Subscription\Events\UserSubscribed`: Fired when a user subscribes to a plan
- `Turahe\Subscription\Events\SubscriptionUpdated`: Fired when a subscription is updated
- `Turahe\Subscription\Events\SubscriptionCancelled`: Fired when a subscription is cancelled

## Testing

The package includes comprehensive tests. Run them with:

```bash
composer test
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

If you encounter any issues or have questions, please open an issue on GitHub or contact the maintainers.

---

**Note**: This package handles subscription logic and plan management. Payment processing is not included and should be handled by your preferred payment gateway (Stripe, PayPal, etc.).

