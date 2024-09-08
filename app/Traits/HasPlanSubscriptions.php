<?php

declare(strict_types=1);

namespace Modules\Subscription\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\Subscription;
use Modules\Subscription\Services\Period;

/**
 *
 */
trait HasPlanSubscriptions
{
    /**
     * @return void
     */
    protected static function bootHasSubscriptions(): void
    {
        static::deleted(function ($plan): void {
            $plan->subscriptions()->delete();
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

    /**
     * @return Collection
     */
    public function activePlanSubscriptions(): Collection
    {
        return $this->planSubscriptions->reject->inactive();
    }

    /**
     * @param string $subscriptionSlug
     * @return Subscription|null
     */
    public function planSubscription(string $subscriptionSlug): ?Subscription
    {
        return $this->planSubscriptions()->where('slug', $subscriptionSlug)->first();
    }

    /**
     * @return Collection
     */
    public function subscribedPlans(): Collection
    {
        $planIds = $this->planSubscriptions->reject
            ->inactive()
            ->pluck('plan_id')
            ->unique();

        return tap(new (config('laravel-subscriptions.models.plan')))->whereIn('id', $planIds)->get();
    }

    /**
     * @param int $planId
     * @return bool
     */
    public function subscribedTo(int $planId): bool
    {
        $subscription = $this->planSubscriptions()
            ->where('plan_id', $planId)
            ->first();

        return $subscription && $subscription->active();
    }

    /**
     * @param string $subscription
     * @param Plan $plan
     * @param Carbon|null $startDate
     * @return Subscription
     */
    public function newPlanSubscription(string $subscription, Plan $plan, ?Carbon $startDate = null): Subscription
    {
        $trial = new Period(
            interval: $plan->trial_interval,
            count: $plan->trial_period,
            start: $startDate ?? Carbon::now()
        );
        $period = new Period(
            interval: $plan->invoice_interval,
            count: $plan->invoice_period,
            start: $trial->getEndDate()
        );

        return $this->planSubscriptions()->create([
            'name' => $subscription,
            'plan_id' => $plan->getKey(),
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate(),
        ]);
    }
}
