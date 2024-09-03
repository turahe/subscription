<?php

declare(strict_types=1);

namespace Modules\Subscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Subscription\Traits\HasSlug;
use Modules\Subscription\Services\Period;
use Modules\Subscription\Traits\BelongsToPlan;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\SlugOptions;

/**
 * Modules\Subscription\Models\PlanFeature.
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Subscription\Models\SubscriptionUsage[] $usage
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature byPlanId($planId)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature ordered($direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereResettableInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereResettablePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Feature whereValue($value)
 */
class Feature extends Model implements Sortable
{
    use BelongsToPlan;
    use HasSlug;
    use SoftDeletes;
    use SortableTrait;
    use HasUlids;

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
        return config('laravel-subscriptions.tables.features');
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
        return $this->hasMany(config('laravel-subscriptions.models.subscription_usage'));
    }

    public function getResetDate(?Carbon $dateFrom = null): Carbon
    {
        $period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? Carbon::now());

        return $period->getEndDate();
    }
}
