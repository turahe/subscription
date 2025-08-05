<?php

declare(strict_types=1);

namespace Turahe\Subscription\Listeners;

use Turahe\Subscription\Events\SubscriptionCancelled;
use Turahe\Subscription\Events\SubscriptionUpdated;
use Turahe\Subscription\Events\UserSubscribed;

class UpdateTrialEndingDate
{
    public function handle(UserSubscribed|SubscriptionUpdated|SubscriptionCancelled $event): void
    {
        $subscription = match (true) {
            $event instanceof SubscriptionCancelled => $event->subscription,
            default => $event->user->subscription(),
        };

        if ($subscription?->onTrial()) {
            $event->user->forceFill([
                'trial_ends_at' => $subscription->trial_ends_at,
            ])->save();
        }
    }
}
