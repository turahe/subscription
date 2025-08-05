<?php

declare(strict_types=1);

namespace Turahe\Subscription\Events;

use Turahe\Subscription\Models\PlanSubscription;

class SubscriptionUpdated
{
    public function __construct(
        public readonly PlanSubscription $subscription,
    ) {
    }
}
