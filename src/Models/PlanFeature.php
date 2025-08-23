<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use Carbon\Carbon;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Traits\BelongsToPlan;
use Turahe\UserStamps\Concerns\HasUserStamps;

class PlanFeature extends Model implements Sortable
{
    use BelongsToPlan;
    use HasSlug;
    use HasConfigurablePrimaryKey;
    use HasUserStamps;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var array<string>
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
     * @var array<string, mixed>
     */
    protected $casts = [
        'plan_id' => 'integer',
        'slug' => 'string',
        'value' => 'integer',
        'resettable_period' => 'integer',
        'resettable_interval' => Interval::class,
        'record_ordering' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public readonly array $sortable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->sortable = [
            'order_column_name' => 'record_ordering',
        ];
    }

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
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function usage(): HasMany
    {
        return $this->hasMany(config('subscription.models.subscription_usage'));
    }

    public function getResetDate(?Carbon $dateFrom = null): Carbon
    {
        $period = new Period(
            interval: $this->resettable_interval,
            count: $this->resettable_period,
            start: $dateFrom ?? Carbon::now()
        );

        return $period->getEndDate();
    }
}
