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
        return config('subscription.tables.features');
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
