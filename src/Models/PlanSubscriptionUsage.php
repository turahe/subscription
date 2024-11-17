<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Turahe\UserStamps\Concerns\HasUserStamps;

/**
 * @property string $id
 * @property string $plan_subscription_id
 * @property string $plan_feature_id
 * @property int $used
 * @property string|null $timezone
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\User|null $destroyer
 * @property-read \App\Models\User|null $editor
 * @property-read \Turahe\Subscription\Models\PlanFeature|null $feature
 * @property-read \Turahe\Subscription\Models\PlanSubscription|null $subscription
 *
 * @method static Builder<static>|PlanSubscriptionUsage byFeatureSlug(string $featureSlug)
 * @method static Builder<static>|PlanSubscriptionUsage newModelQuery()
 * @method static Builder<static>|PlanSubscriptionUsage newQuery()
 * @method static Builder<static>|PlanSubscriptionUsage onlyTrashed()
 * @method static Builder<static>|PlanSubscriptionUsage query()
 * @method static Builder<static>|PlanSubscriptionUsage whereCreatedAt($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereCreatedBy($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereDeletedAt($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereDeletedBy($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereId($value)
 * @method static Builder<static>|PlanSubscriptionUsage wherePlanFeatureId($value)
 * @method static Builder<static>|PlanSubscriptionUsage wherePlanSubscriptionId($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereTimezone($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereUpdatedAt($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereUpdatedBy($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereUsed($value)
 * @method static Builder<static>|PlanSubscriptionUsage whereValidUntil($value)
 * @method static Builder<static>|PlanSubscriptionUsage withTrashed()
 * @method static Builder<static>|PlanSubscriptionUsage withoutTrashed()
 *
 * @mixin \Eloquent
 */
class PlanSubscriptionUsage extends Model
{
    use HasUlids;
    use HasUserStamps;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'subscription_id',
        'feature_id',
        'used',
        'valid_until',
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * @var string[]
     */
    protected $casts = [
        'subscription_id' => 'integer',
        'feature_id' => 'integer',
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
        return $this->belongsTo(config('subscription.models.feature'), 'feature_id', 'id', 'feature');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('subscription.models.subscription'), 'subscription_id', 'id', 'subscription');
    }

    public function scopeByFeatureSlug(Builder $builder, string $featureSlug): Builder
    {
        $model = config('subscription.models.feature', PlanFeature::class);
        $feature = tap(new $model)->where('slug', $featureSlug)->first();

        return $builder->where('feature_id', $feature ? $feature->getKey() : null);
    }

    public function expired(): bool
    {
        if (! $this->valid_until) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
}
