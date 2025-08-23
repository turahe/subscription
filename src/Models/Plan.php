<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\Subscription\Enums\Interval;
use Turahe\UserStamps\Concerns\HasUserStamps;

class Plan extends Model implements Sortable
{
    use HasSlug;
    use HasConfigurablePrimaryKey;
    use HasUserStamps;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var array<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'price',
        'signup_fee',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
        'prorate_day',
        'prorate_period',
        'prorate_extend_due',
        'active_subscribers_limit',
        'record_ordering',
    ];

    protected function casts(): array
    {
        return [
            'slug' => 'string',
            'is_active' => 'boolean',
            'price' => 'float',
            'signup_fee' => 'float',
            'currency' => 'string',
            'trial_period' => 'integer',
            'trial_interval' => Interval::class,
            'invoice_period' => 'integer',
            'invoice_interval' => Interval::class,
            'grace_period' => 'integer',
            'grace_interval' => 'string',
            'prorate_day' => 'integer',
            'prorate_period' => 'integer',
            'prorate_extend_due' => 'integer',
            'active_subscribers_limit' => 'integer',
            'record_ordering' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    protected $dateFormat = 'U';

    public readonly array $sortable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->sortable = [
            'order_column_name' => 'record_ordering',
            'sort_when_creating' => true,
        ];
    }

    public function getTable(): string
    {
        return config('subscription.tables.plans', 'plans');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (Plan $plan): void {
            $plan->features()->delete();
            $plan->subscriptions()->delete();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function features(): HasMany
    {
        return $this->hasMany(config('subscription.models.feature'));
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('subscription.models.subscription'));
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    public function hasTrial(): bool
    {
        return $this->trial_period > 0;
    }

    public function hasGrace(): bool
    {
        return $this->grace_period > 0;
    }

    public function getFeatureBySlug(string $featureSlug): ?PlanFeature
    {
        return $this->features()->where('slug', $featureSlug)->first();
    }

    public function activate(): self
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    public function deactivate(): self
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
