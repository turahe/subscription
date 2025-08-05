<?php

declare(strict_types=1);

namespace Turahe\Subscription\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Turahe\Subscription\Models\Plan;

class UserSubscribed
{
    public function __construct(
        public readonly Authenticatable $user,
        public readonly Plan $plan,
        public readonly bool $fromRegistration,
    ) {
    }
}
