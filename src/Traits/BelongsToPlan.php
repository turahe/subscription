<?php

declare(strict_types=1);

namespace Turahe\Subscription\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToPlan
{
    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subscription.models.plan'));
    }

    public function getPlanIdAttribute(): mixed
    {
        return $this->attributes['plan_id'] ?? null;
    }

    public function setPlanIdAttribute(mixed $value): void
    {
        $this->attributes['plan_id'] = $value;
    }
}
