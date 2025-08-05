<?php

declare(strict_types=1);

namespace Turahe\Subscription\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Services\Period;

trait HasPlanSubscriptions
{
    protected static function bootHasSubscriptions(): void
    {
        static::deleted(function (self $model): void {
            $model->planSubscriptions()->delete();
        });
    }

    /**
     * The subscriber may have many plan subscriptions.
     */
    public function planSubscriptions(): MorphMany
    {
        return $this->morphMany(
            related: config('subscription.models.subscription'),
            name: 'subscriber',
            type: 'subscriber_type',
            id: 'subscriber_id'
        );
    }

    public function activePlanSubscriptions(): Collection
    {
        return $this->planSubscriptions->reject->inactive();
    }

    public function planSubscription(string $subscriptionSlug): ?PlanSubscription
    {
        return $this->planSubscriptions()->where('slug', $subscriptionSlug)->first();
    }

    public function subscribedPlans(): Collection
    {
        $planIds = $this->planSubscriptions->reject
            ->inactive()
            ->pluck('plan_id')
            ->unique();

        if ($planIds->isEmpty()) {
            return new Collection();
        }

        $planClass = config('subscription.models.plan');
        return $planClass::whereIn('id', $planIds)->get();
    }

    public function subscribedTo(int|string $planId): bool
    {
        $subscription = $this->planSubscriptions()
            ->where('plan_id', $planId)
            ->first();

        return $subscription?->active() ?? false;
    }

    public function newPlanSubscription(string $subscription, Plan $plan, ?Carbon $startDate = null): PlanSubscription
    {
        $start = $startDate ?? Carbon::now();
        
        // Handle trial period
        if ($plan->trial_period > 0) {
            $trial = new Period(
                interval: $plan->trial_interval,
                count: $plan->trial_period,
                start: $start
            );
            $trialEndDate = $trial->getEndDate();
            $subscriptionStart = $trialEndDate;
        } else {
            $trialEndDate = null;
            $subscriptionStart = $start;
        }

        // Create subscription period
        $period = new Period(
            interval: $plan->invoice_interval,
            count: $plan->invoice_period,
            start: $subscriptionStart
        );

        return $this->planSubscriptions()->create([
            'name' => $subscription,
            'plan_id' => $plan->getKey(),
            'trial_ends_at' => $trialEndDate,
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate(),
        ]);
    }
}
