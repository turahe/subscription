<?php

declare(strict_types=1);

namespace Turahe\Subscription\Events;

use Turahe\Subscription\Models\Plan;

class Saving
{
    public function __construct(
        public readonly Plan $subscriptionPlan,
    ) {
    }
}
