<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Traits\BelongsToPlan;
use Turahe\UserStamps\Concerns\HasUserStamps;

/**
 * @property string $id
 * @property int $plan_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $value
 * @property int $resettable_period
 * @property string $resettable_interval
 * @property int|null $record_ordering
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\User|null $destroyer
 * @property-read \App\Models\User|null $editor
 * @property-read \Turahe\Subscription\Models\Plan|null $plan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Turahe\Subscription\Models\PlanSubscriptionUsage> $usage
 * @property-read int|null $usage_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature byPlanId(int $planId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereRecordOrdering($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereResettableInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereResettablePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature withoutTrashed()
 *
 * @mixin \Eloquent
 */
class PlanFeature extends Model implements Sortable
{
    use BelongsToPlan;
    use HasSlug;
    use HasUlids;
    use HasUserStamps;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'plan_id',
        'slug',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'record_ordering',
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * @var string[]
     */
    protected $casts = [
        'plan_id' => 'integer',
        'slug' => 'string',
        'value' => 'integer',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'record_ordering' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * @var array|string[]
     */
    public array $sortable = [
        'order_column_name' => 'record_ordering',
    ];

    public function getTable(): string
    {
        return config('subscription.tables.features', 'plan_features');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (PlanFeature $feature): void {
            $feature->usage()->delete();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function usage(): HasMany
    {
        return $this->hasMany(config('subscription.models.subscription_usage'));
    }

    public function getResetDate(?Carbon $dateFrom = null): Carbon
    {
        $period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? Carbon::now());

        return $period->getEndDate();
    }
}
