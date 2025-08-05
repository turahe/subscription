<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
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
    use HasUlids;
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

    public function active(): bool
    {
        return $this->onTrial() || $this->ends_at->isFuture();
    }

    public function inactive(): bool
    {
        return ! $this->active();
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function canceled(): bool
    {
        return $this->canceled_at !== null;
    }

    public function ended(): bool
    {
        return $this->ends_at->isPast();
    }

    public function cancel(bool $immediately = false): self
    {
        $this->canceled_at = Carbon::now();

        if ($immediately) {
            $this->ends_at = Carbon::now();
        }

        $this->save();

        return $this;
    }

    public function changePlan(Plan $plan): self
    {
        $this->plan_id = $plan->getKey();
        $this->setNewPeriod(
            invoice_interval: $plan->invoice_interval,
            invoice_period: $plan->invoice_period,
            start: Carbon::now()
        );
        $this->save();

        return $this;
    }

    public function renew(): self
    {
        if ($this->canceled() && $this->ended()) {
            throw new \Exception('Cannot renew a canceled subscription that has ended.');
        }

        $this->setNewPeriod();
        $this->usage()->delete();
        $this->save();

        return $this;
    }

    public function scopeOfSubscriber(Builder $builder, Model $subscriber): Builder
    {
        return $builder->where('subscriber_type', $subscriber->getMorphClass())
            ->where('subscriber_id', $subscriber->getKey());
    }

    public function scopeByPlanId(Builder $builder, int|string $planId): Builder
    {
        return $builder->where('plan_id', $planId);
    }

    public function scopeFindEndingTrial(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('trial_ends_at', [$from, $to]);
    }

    public function scopeFindEndedTrial(Builder $builder): Builder
    {
        return $builder->where('trial_ends_at', '<=', Carbon::now());
    }

    public function scopeFindEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('ends_at', [$from, $to]);
    }

    public function scopeFindEndedPeriod(Builder $builder): Builder
    {
        return $builder->where('ends_at', '<=', Carbon::now());
    }

    public function scopeFindActive(Builder $builder): Builder
    {
        return $builder->where(function (Builder $query): void {
            $query->where('trial_ends_at', '>', Carbon::now())
                ->orWhere('ends_at', '>', Carbon::now());
        });
    }

    protected function setNewPeriod(string $invoice_interval = '', ?int $invoice_period = null, ?Carbon $start = null): self
    {
        $plan = $this->plan;
        $start = $start ?? Carbon::now();

        $period = new Period(
            interval: $invoice_interval ?: $plan->invoice_interval,
            count: $invoice_period ?: $plan->invoice_period,
            start: $start
        );

        $this->starts_at = $period->getStartDate();
        $this->ends_at = $period->getEndDate();

        return $this;
    }

    public function recordFeatureUsage(string $featureSlug, int $uses = 1, bool $incremental = true): PlanSubscriptionUsage
    {
        $feature = $this->plan->getFeatureBySlug($featureSlug);

        if (! $feature) {
            throw new \Exception("Feature {$featureSlug} not found.");
        }

        $usage = $this->usage()->where('feature_id', $feature->getKey())->first();

        if (! $usage) {
            $usage = $this->usage()->create([
                'feature_id' => $feature->getKey(),
                'used' => 0,
            ]);
        }

        if ($incremental) {
            $usage->increment('used', $uses);
        } else {
            $usage->update(['used' => $uses]);
        }

        return $usage;
    }

    public function reduceFeatureUsage(string $featureSlug, int $uses = 1): ?PlanSubscriptionUsage
    {
        $feature = $this->plan->getFeatureBySlug($featureSlug);

        if (! $feature) {
            return null;
        }

        $usage = $this->usage()->where('feature_id', $feature->getKey())->first();

        if (! $usage) {
            return null;
        }

        $usage->decrement('used', $uses);

        return $usage;
    }

    public function canUseFeature(string $featureSlug): bool
    {
        $feature = $this->plan->getFeatureBySlug($featureSlug);

        if (! $feature) {
            return false;
        }

        if ($feature->value === 0) {
            return false;
        }

        $usage = $this->getFeatureUsage($featureSlug);

        return $usage < $feature->value;
    }

    public function getFeatureUsage(string $featureSlug): int
    {
        $feature = $this->plan->getFeatureBySlug($featureSlug);

        if (! $feature) {
            return 0;
        }

        $usage = $this->usage()->where('feature_id', $feature->getKey())->first();

        return $usage ? $usage->used : 0;
    }

    public function getFeatureRemainings(string $featureSlug): int
    {
        $feature = $this->plan->getFeatureBySlug($featureSlug);

        if (! $feature) {
            return 0;
        }

        $usage = $this->getFeatureUsage($featureSlug);

        return max(0, $feature->value - $usage);
    }

    public function getFeatureValue(string $featureSlug): ?string
    {
        $feature = $this->plan->getFeatureBySlug($featureSlug);

        return $feature?->value;
    }
}
