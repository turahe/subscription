<?php

declare(strict_types=1);

namespace Turahe\Subscription\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Turahe\Subscription\Models\Plan;

trait BelongsToPlan
{
    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subscription.models.plan', Plan::class), 'plan_id');
    }

    public function scopeByPlanId(Builder $builder, int $planId): Builder
    {
        return $builder->where('plan_id', $planId);
    }
}
