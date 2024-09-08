<?php

declare(strict_types=1);

namespace Modules\Subscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules\Subscription\Models\SubscriptionUsage.
 *
 * @property int $id
 * @property int $subscription_id
 * @property int $feature_id
 * @property int $used
 * @property Carbon|null $valid_until
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Feature $feature
 * @property-read Subscription $subscription
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage byFeatureSlug($featureSlug)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\SubscriptionUsage whereValidUntil($value)
 * @property string|null $timezone
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @method static Builder|SubscriptionUsage newModelQuery()
 * @method static Builder|SubscriptionUsage newQuery()
 * @method static Builder|SubscriptionUsage onlyTrashed()
 * @method static Builder|SubscriptionUsage query()
 * @method static Builder|SubscriptionUsage whereCreatedBy($value)
 * @method static Builder|SubscriptionUsage whereDeletedBy($value)
 * @method static Builder|SubscriptionUsage whereTimezone($value)
 * @method static Builder|SubscriptionUsage whereUpdatedBy($value)
 * @method static Builder|SubscriptionUsage withTrashed()
 * @method static Builder|SubscriptionUsage withoutTrashed()
 * @mixin \Eloquent
 */
class SubscriptionUsage extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'subscription_id',
        'feature_id',
        'used',
        'valid_until',
    ];

    protected $dateFormat = 'U';

    protected $casts = [
        'subscription_id' => 'integer',
        'feature_id' => 'integer',
        'used' => 'integer',
        'valid_until' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('subscription.tables.subscription_usage');
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
        $model = config('subscription.models.feature', Feature::class);
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
