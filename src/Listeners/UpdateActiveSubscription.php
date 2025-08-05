<?php

declare(strict_types=1);

namespace Turahe\Subscription\Listeners;

use Turahe\Subscription\Events\SubscriptionCancelled;
use Turahe\Subscription\Events\SubscriptionUpdated;
use Turahe\Subscription\Events\UserSubscribed;

class UpdateActiveSubscription
{
    public function handle(UserSubscribed|SubscriptionUpdated|SubscriptionCancelled $event): void
    {
        $currentPlan = match (true) {
            $event instanceof SubscriptionCancelled => null,
            default => $event->user->subscription()?->provider_plan,
        };

        $event->user->forceFill([
            'current_billing_plan' => $currentPlan,
        ])->save();
    }
}
