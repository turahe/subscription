<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Turahe\Subscription\Concerns\HasConfigurablePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Turahe\UserStamps\Concerns\HasUserStamps;

class PlanSubscriptionUsage extends Model
{
    use HasConfigurablePrimaryKey;
    use HasUserStamps;
    use SoftDeletes;

    /**
     * @var array<string>
     */
    protected $fillable = [
        'plan_subscription_id',
        'plan_feature_id',
        'used',
        'valid_until',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $casts = [
        'plan_subscription_id' => 'string',
        'plan_feature_id' => 'string',
        'used' => 'integer',
        'valid_until' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('subscription.tables.subscription_usage', 'plan_subscription_usage');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(config('subscription.models.feature'), 'plan_feature_id', 'id', 'feature');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('subscription.models.subscription'), 'plan_subscription_id', 'id', 'subscription');
    }

    public function scopeByFeatureSlug(Builder $builder, string $featureSlug): Builder
    {
        $model = config('subscription.models.feature');
        $feature = tap(new $model)->where('slug', $featureSlug)->first();

        return $builder->where('plan_feature_id', $feature?->getKey());
    }

    public function expired(): bool
    {
        return $this->valid_until?->isPast() ?? false;
    }
}
