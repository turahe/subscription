<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use ALajusticia\Expirable\Traits\Expirable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Turahe\UserStamps\Concerns\HasUserStamps;

class PlanSubscriptionUsage extends Model
{
    use Expirable;
    use HasUlids;
    use HasUserStamps;
    use SoftDeletes;

    const EXPIRES_AT = 'valid_until';

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
