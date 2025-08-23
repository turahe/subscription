<?php

declare(strict_types=1);

namespace Turahe\Subscription\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanSubscription;

class UserSubscribed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly PlanSubscription $subscription,
        public readonly Plan $plan,
        public readonly mixed $user
    ) {
    }

    public function getSubscriptionId(): string
    {
        return $this->subscription->getKey();
    }

    public function getPlanId(): string
    {
        return $this->plan->getKey();
    }

    public function getUserId(): mixed
    {
        return $this->user->getKey();
    }
}
