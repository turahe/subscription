<?php

declare(strict_types=1);

namespace Turahe\Subscription\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Turahe\Subscription\Events\UserSubscribed;
use Turahe\Subscription\Models\PlanSubscription;

class UpdateActiveSubscription implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(UserSubscribed $event): void
    {
        try {
            $subscription = $event->subscription;
            
            // Update any existing active subscriptions to inactive
            $subscription->user->planSubscriptions()
                ->where('id', '!=', $subscription->getKey())
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Mark the new subscription as active
            $subscription->update(['is_active' => true]);
            
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            logger()->error('Failed to update active subscription', [
                'subscription_id' => $event->subscription->getKey(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
