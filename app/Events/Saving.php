<?php

namespace Modules\Subscription\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Subscription\Models\Plan;

class Saving
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Plan $subscriptionPlan)
    {
        if ($subscriptionPlan->featured) {
            Plan::where('featured', 1)->update(['featured' => 0]);
        }
    }
}
