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
        <img src="https://img.shields.io/packagist/l/turahe/subscription.svg?label=License" alt="Packagist" />
    </a>
</p>

# Subscription

**Subscriptions** is a flexible plans and subscription management system for Module, with the required tools to run your
SAAS like services efficiently.
It's simple architecture, accompanied by powerful underlying to afford solid platform for your business.

## Considerations

- Payments are out of scope for this package.
- You may want to extend some of the core models, in case you need to override the logic behind some helper methods like
  `renew()`, `cancel()` etc. E.g.: when cancelling a subscription you may want to also cancel the recurring payment
  attached.

## Installation

1. Install the package via composer:

    ```shell
    composer require turahe/subscription
    ```

2. Publish resources (migrations and config files):

    ```shell
    php artisan vendor:publish --provider="Turahe\Subscription\SubscriptionServiceProvider"
    ```

3. Execute migrations via the following command:

    ```shell
    php artisan migrate
    ```

4. Done!

## Usage

### Add Subscriptions to User model

**Module Subscriptions** has been specially made for Eloquent and simplicity has been taken very serious as in any other
Module related aspect. To add Subscription functionality to your User model just use the
`\Rinvex\Subscriptions\Traits\HasPlanSubscriptions` trait like this:

```php
namespace App\Models;

use Turahe\Subscription\Traits\HasPlanSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasPlanSubscriptions;
}
```

That's it, we only have to use that trait in our User model! Now your users may subscribe to plans.

> **Note:** you can use `HasPlanSubscriptions` trait on any subscriber model, it doesn't have to be the user model, in
> fact any model will do.

### Create a Plan

```php
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Interval;

$plan = Plan::create([
    'name' => 'Pro',
    'description' => 'Pro plan',
    'price' => 9.99,
    'signup_fee' => 1.99,
    'invoice_period' => 1,
    'invoice_interval' => Interval::MONTH->value,
    'trial_period' => 15,
    'trial_interval' => Interval::DAY->value,
    'sort_order' => 1,
    'currency' => 'USD',
]);

// Create multiple plan features at once
$plan->features()->saveMany([
    new PlanFeature(['name' => 'listings', 'value' => 50, 'sort_order' => 1]),
    new PlanFeature(['name' => 'pictures_per_listing', 'value' => 10, 'sort_order' => 5]),
    new PlanFeature(['name' => 'listing_duration_days', 'value' => 30, 'sort_order' => 10, 'resettable_period' => 1, 'resettable_interval' => 'month']),
    new PlanFeature(['name' => 'listing_title_bold', 'value' => 'Y', 'sort_order' => 15])
]);
```

### Get Plan Details

You can query the plan for further details, using the intuitive API as follows:

```php
use Turahe\Subscription\Models\Plan;

$plan = Plan::find(1);

// Get all plan features
$plan->features;

// Get all plan subscriptions
$plan->subscriptions;

// Check if the plan is free
$plan->isFree();

// Check if the plan has trial period
$plan->hasTrial();

// Check if the plan has grace period
$plan->hasGrace();
```

Both `$plan->features` and `$plan->subscriptions` are collections, driven from relationships, and thus you can query
these relations as any normal Eloquent relationship. E.g.
`$plan->features()->where('name', 'listing_title_bold')->first()`.

### Get Feature Value

Say you want to show the value of the feature _pictures_per_listing_ from above. You can do so in many ways:

```php
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Models\PlanSubscription;

// Use the plan instance to get feature's value
$amountOfPictures = $plan->getFeatureBySlug('pictures_per_listing')->value;

// Query the feature itself directly
$amountOfPictures = PlanFeature::where('slug', 'pictures_per_listing')->first()->value;

// Get feature value through the subscription instance
$amountOfPictures = PlanSubscription::find(1)->getFeatureValue('pictures_per_listing');
```

### Create a Subscription

You can subscribe a user to a plan by using the `newSubscription()` function available in the `HasPlanSubscriptions`
trait. First, retrieve an instance of your subscriber model, which typically will be your user model and an instance of
the plan your user is subscribing to. Once you have retrieved the model instance, you may use the `newSubscription`
method to create the model's subscription.

```php
use Turahe\Auth\Models\User;use Turahe\Subscription\Models\Plan;

$user = User::find(1);
$plan = Plan::find(1);

$user->newPlanSubscription('main', $plan);
```

The first argument passed to `newSubscription` method should be the title of the subscription. If your application offer
a single subscription, you might call this `main` or `primary`, while the second argument is the plan instance your user
is subscribing to, and there's an optional third parameter to specify custom start date as an instance of
`Carbon\Carbon` (by default if not provided, it will start now).

### Change the Plan

You can change subscription plan easily as follows:

```php
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanSubscription;

$plan = Plan::find(2);
$subscription = PlanSubscription::find(1);

// Change subscription plan
$subscription->changePlan($plan);
```

If both plans (current and new plan) have the same billing frequency (e.g., `invoice_period` and `invoice_interval`) the
subscription will retain the same billing dates. If the plans don't have the same billing frequency, the subscription
will have the new plan billing frequency, starting on the day of the change and _the subscription usage data will be
cleared_. Also, if the new plan has a trial period, and it's a new subscription, the trial period will be applied.

### Feature Options

Plan features are great for fine-tuning subscriptions, you can top-up certain feature for X times of usage, so users may
then use it only for that amount. Features also have the ability to be resettable and then it's usage could be expired
too. See the following examples:

```php
use Turahe\Subscription\Models\PlanFeature;

// Find plan feature
$feature = PlanFeature::where('name', 'listing_duration_days')->first();

// Get feature reset date
$feature->getResetDate(new \Carbon\Carbon());
```

### Subscription Feature Usage

There's multiple ways to determine the usage and ability of a particular feature in the user subscription, the most
common one is `canUseFeature`:

The `canUseFeature` method returns `true` or `false` depending on multiple factors:

- Feature _is enabled_.
- Feature value isn't `0`/`false`/`NULL`.
- Or feature has remaining uses available.

```php
$user->planSubscription('main')->canUseFeature('listings');
```

Other feature methods on the user subscription instance are:

- `getFeatureUsage`: returns how many times the user has used a particular feature.
- `getFeatureRemainings`: returns available uses for a particular feature.
- `getFeatureValue`: returns the feature value.

> All methods share the same signature: e.g. `$user->planSubscription('main')->getFeatureUsage('listings');`.

### Record Feature Usage

In order to effectively use the ability methods you will need to keep track of every usage of each feature (or at least
those that require it). You may use the `recordFeatureUsage` method available through the user `subscription()` method:

```php
$user->planSubscription('main')->recordFeatureUsage('listings');
```

The `recordFeatureUsage` method accept 3 parameters: the first one is the feature's name, the second one is the quantity
of uses to add (default is `1`), and the third one indicates if the addition should be incremental (default behavior),
when disabled the usage will be override by the quantity provided. E.g.:

```php
// Increment by 2
$user->planSubscription('main')->recordFeatureUsage('listings', 2);

// Override with 9
$user->planSubscription('main')->recordFeatureUsage('listings', 9, false);
```

### Reduce Feature Usage

Reducing the feature usage is _almost_ the same as incrementing it. Here we only _substract_ a given quantity (default
is `1`) to the actual usage:

```php
$user->planSubscription('main')->reduceFeatureUsage('listings', 2);
```

### Clear The Subscription Usage Data

```php
$user->planSubscription('main')->usage()->delete();
```

### Check Subscription Status

For a subscription to be considered active _one of the following must be `true`_:

- Subscription has an active trial.
- Subscription `ends_at` is in the future.

```php
$user->subscribedTo($planId);
```

Alternatively you can use the following methods available in the subscription model:

```php
$user->planSubscription('main')->active();
$user->planSubscription('main')->canceled();
$user->planSubscription('main')->ended();
$user->planSubscription('main')->onTrial();
```

> Canceled subscriptions with an active trial or `ends_at` in the future are considered active.

### Renew a Subscription

To renew a subscription you may use the `renew` method available in the subscription model. This will set a new
`ends_at` date based on the selected plan and _will clear the usage data_ of the subscription.

```php
$user->planSubscription('main')->renew();
```

_Canceled subscriptions with an ended period can't be renewed._

### Cancel a Subscription

To cancel a subscription, simply use the `cancel` method on the user's subscription:

```php
$user->planSubscription('main')->cancel();
```

By default the subscription will remain active until the end of the period, you may pass `true` to end the subscription
_immediately_:

```php
$user->planSubscription('main')->cancel(true);
```

### Scopes

#### Subscription Model

```php
use Turahe\Auth\Models\User;use Turahe\Subscription\Models\PlanSubscription;

// Get subscriptions by plan
$subscriptions = PlanSubscription::byPlanId($plan_id)->get();

// Get bookings of the given user
$user = User::find(1);
$bookingsOfSubscriber = PlanSubscription::ofSubscriber($user)->get();

// Get subscriptions with trial ending in 3 days
$subscriptions = PlanSubscription::findEndingTrial(3)->get();

// Get subscriptions with ended trial
$subscriptions = PlanSubscription::findEndedTrial()->get();

// Get subscriptions with period ending in 3 days
$subscriptions = PlanSubscription::findEndingPeriod(3)->get();

// Get subscriptions with ended period
$subscriptions = PlanSubscription::findEndedPeriod()->get();
```

### Models

**Module Subscriptions** uses 4 models:

```php
Turahe\Subscription\Models\Plan;
Turahe\Subscription\Models\Feature;
Turahe\Subscription\Models\Subscription;
Turahe\Subscription\Models\SubscriptionUsage;
```

