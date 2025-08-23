<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Turahe\Subscription\Concerns\HasConfigurablePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Traits\BelongsToPlan;
use Turahe\UserStamps\Concerns\HasUserStamps;

class PlanSubscription extends Model
{
    use BelongsToPlan;
    use HasSlug;
    use HasConfigurablePrimaryKey;
    use HasUserStamps;
    use SoftDeletes;

    public const EXPIRES_AT = 'ends_at';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'slug',
        'name',
        'description',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancels_at',
        'canceled_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $casts = [
        'subscriber_id' => 'string',
        'subscriber_type' => 'string',
        'plan_id' => 'string',
        'slug' => 'string',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancels_at' => 'datetime',
        'canceled_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('subscription.tables.subscriptions', 'plan_subscriptions');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PlanSubscription $model): void {
            if (! $model->starts_at || ! $model->ends_at) {
                $model->setNewPeriod();
            }
        });

        static::deleted(function (PlanSubscription $subscription): void {
            $subscription->usage()->delete();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function subscriber(): MorphTo
    {
        return $this->morphTo('subscriber', 'subscriber_type', 'subscriber_id', 'id');
    }

    public function usage(): HasMany
    {
        return $this->hasMany(config('subscription.models.subscription_usage'));
    }

    public function setNewPeriod(): void
    {
        $this->starts_at = $this->starts_at ?: Carbon::now();
        $this->ends_at = $this->ends_at ?: $this->plan->getNextBillingDate($this->starts_at);
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function canceled(): bool
    {
        return $this->canceled_at !== null;
    }

    public function active(): bool
    {
        return ! $this->canceled() && $this->ends_at->isFuture();
    }

    public function expired(): bool
    {
        return $this->ends_at->isPast();
    }

    public function cancel(?Carbon $date = null): void
    {
        $this->update([
            'canceled_at' => $date ?: Carbon::now(),
            'ends_at' => $date ?: Carbon::now(),
        ]);
    }

    public function uncancel(): void
    {
        $this->update([
            'canceled_at' => null,
            'ends_at' => $this->plan->getNextBillingDate($this->starts_at),
        ]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('ends_at', '>', Carbon::now())
            ->whereNull('canceled_at');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('ends_at', '<=', Carbon::now());
    }

    public function scopeCanceled(Builder $query): Builder
    {
        return $query->whereNotNull('canceled_at');
    }

    public function scopeOnTrial(Builder $query): Builder
    {
        return $query->where('trial_ends_at', '>', Carbon::now());
    }
}
