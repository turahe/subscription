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
use Spatie\Sluggable\SlugOptions;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Traits\BelongsToPlan;
use Turahe\Subscription\Traits\HasSlug;

/**
 * Turahe\Subscription\Models\PlanFeature.
 *
 * @property int $id
 * @property int $plan_id
 * @property string $slug
 * @property array $title
 * @property array $description
 * @property string $value
 * @property int $resettable_period
 * @property string $resettable_interval
 * @property int $record_ordering
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Plan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Turahe\Subscription\Models\SubscriptionUsage[] $usage
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature byPlanId($planId)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature ordered($direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereResettableInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereResettablePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Turahe\Subscription\Models\Feature whereValue($value)
 *
 * @property string $name
 * @property int $sort_order
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature withoutTrashed()
 *
 * @property-read int|null $usage_count
 *
 * @mixin \Eloquent
 */
class Feature extends Model implements Sortable
{
    use BelongsToPlan;
    use HasSlug;
    use HasUlids;
    use SoftDeletes;
    use SortableTrait;

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

    protected $dateFormat = 'U';

    protected $casts = [
        'plan_id' => 'integer',
        'slug' => 'string',
        'value' => 'string',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'record_ordering' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    public array $sortable = [
        'order_column_name' => 'record_ordering',
    ];

    public function getTable(): string
    {
        return config('subscription.tables.features');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (Feature $feature): void {
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
