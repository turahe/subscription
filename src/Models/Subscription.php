<?php

declare(strict_types=1);

namespace Turahe\Subscription\Models;

use ALajusticia\Expirable\Traits\Expirable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\Sluggable\SlugOptions;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Traits\BelongsToPlan;
use Turahe\Subscription\Traits\HasSlug;
use Turahe\UserStamps\Concerns\HasUserStamps;

class Subscription extends Model
{
    use BelongsToPlan;
    use HasSlug;
    use HasUlids;
    use SoftDeletes;
    use HasUserStamps;
    use Expirable;
    const EXPIRES_AT = 'ends_at';



    /**
     * @var string[]
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
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * @var string[]
     */
    protected $casts = [
        'subscriber_id' => 'integer',
        'subscriber_type' => 'string',
        'plan_id' => 'integer',
        'slug' => 'string',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancels_at' => 'datetime',
        'canceled_at' => 'datetime',
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

    /**
     * @return string
     */
    public function getTable(): string
    {
        return config('subscription.tables.subscriptions');
    }

    /**
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (! $model->starts_at || ! $model->ends_at) {
                $model->setNewPeriod();
            }
        });

        static::deleted(function (self $subscription): void {
            $subscription->usage()->delete();
        });
    }

    /**
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * @return MorphTo
     */
    public function subscriber(): MorphTo
    {
        return $this->morphTo('subscriber', 'subscriber_type', 'subscriber_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function usage(): HasMany
    {
        return $this->hasMany(config('subscription.models.subscription_usage'));
    }

    /**
     * @return bool
     */
    public function active(): bool
    {
        return ! $this->ended() || $this->onTrial();
    }

    /**
     * @return bool
     */
    public function inactive(): bool
    {
        return ! $this->active();
    }

    /**
     * @return bool
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && Carbon::now()->lt($this->trial_ends_at);
    }

    /**
     * @return bool
     */
    public function canceled(): bool
    {
        return $this->canceled_at && Carbon::now()->gte($this->canceled_at);
    }

    /**
     * @return bool
     */
    public function ended(): bool
    {
        return $this->ends_at && Carbon::now()->gte($this->ends_at);
    }

    /**
     * @param bool $immediately
     * @return $this
     */
    public function cancel(bool $immediately = false): self
    {
        $this->canceled_at = Carbon::now();

        if ($immediately) {
            $this->ends_at = $this->canceled_at;
        }

        $this->save();

        return $this;
    }

    /**
     * @param Plan $plan
     * @return $this
     */
    public function changePlan(Plan $plan): self
    {
        // If plans does not have the same billing frequency
        // (e.g., invoice_interval and invoice_period) we will update
        // the billing dates starting today, and since we are basically creating
        // a new billing cycle, the usage data will be cleared.
        if ($this->plan->invoice_interval !== $plan->invoice_interval || $this->plan->invoice_period !== $plan->invoice_period) {
            $this->setNewPeriod($plan->invoice_interval, $plan->invoice_period);
            $this->usage()->delete();
        }

        // Attach new plan to subscription
        $this->plan_id = $plan->getKey();
        $this->save();

        return $this;
    }

    /**
     * Renew subscription period.
     *
     * @return $this
     *
     * @throws LogicException
     */
    public function renew(): self
    {
        if ($this->ended() && $this->canceled()) {
            throw new LogicException('Unable to renew canceled ended subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription): void {
            // Clear usage data
            $subscription->usage()->delete();

            // Renew period
            $subscription->setNewPeriod();
            $subscription->canceled_at = null;
            $subscription->save();
        });

        return $this;
    }

    /**
     * Get bookings of the given subscriber.
     */
    public function scopeOfSubscriber(Builder $builder, Model $subscriber): Builder
    {
        return $builder->where('subscriber_type', $subscriber->getMorphClass())
            ->where('subscriber_id', $subscriber->getKey());
    }

    /**
     * Scope subscriptions with ending trial.
     */
    public function scopeFindEndingTrial(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('trial_ends_at', [$from, $to]);
    }

    /**
     * Scope subscriptions with ended trial.
     */
    public function scopeFindEndedTrial(Builder $builder): Builder
    {
        return $builder->where('trial_ends_at', '<=', Carbon::now());
    }

    /**
     * Scope subscriptions with ending periods.
     */
    public function scopeFindEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('ends_at', [$from, $to]);
    }

    /**
     * Scope subscriptions with ended periods.
     */
    public function scopeFindEndedPeriod(Builder $builder): Builder
    {
        return $builder->where('ends_at', '<=', Carbon::now());
    }

    /**
     * Scope all active subscriptions for a user.
     */
    public function scopeFindActive(Builder $builder): Builder
    {
        return $builder->where('ends_at', '>', Carbon::now());
    }

    /**
     * Set new subscription period.
     *
     * @return $this
     */
    protected function setNewPeriod(string $invoice_interval = '', ?int $invoice_period = null, ?Carbon $start = null): self
    {
        if (empty($invoice_interval)) {
            $invoice_interval = $this->plan->invoice_interval;
        }

        if (empty($invoice_period)) {
            $invoice_period = $this->plan->invoice_period;
        }

        $period = new Period(
            interval: $invoice_interval,
            count: $invoice_period,
            start: $start ?? Carbon::now()
        );

        $this->starts_at = $period->getStartDate();
        $this->ends_at = $period->getEndDate();

        return $this;
    }

    /**
     * @param string $featureSlug
     * @param int $uses
     * @param bool $incremental
     * @return SubscriptionUsage
     */
    public function recordFeatureUsage(string $featureSlug, int $uses = 1, bool $incremental = true): SubscriptionUsage
    {
        $feature = $this->plan->features()->where('slug', $featureSlug)->first();

        $usage = $this->usage()->firstOrNew([
            'subscription_id' => $this->getKey(),
            'feature_id' => $feature->getKey(),
        ]);

        if ($feature->resettable_period) {
            // Set expiration date when the usage record is new or doesn't have one.
            if ($usage->valid_until === null) {
                // Set date from subscription creation date so the reset
                // period match the period specified by the subscription's plan.
                $usage->valid_until = $feature->getResetDate($this->created_at);
            } elseif ($usage->expired()) {
                // If the usage record has been expired, let's assign
                // a new expiration date and reset the uses to zero.
                $usage->valid_until = $feature->getResetDate($usage->valid_until);
                $usage->used = 0;
            }
        }

        $usage->used = $incremental ? $usage->used + $uses : $uses;

        $usage->save();

        return $usage;
    }

    /**
     * @param string $featureSlug
     * @param int $uses
     * @return SubscriptionUsage|null
     */
    public function reduceFeatureUsage(string $featureSlug, int $uses = 1): ?SubscriptionUsage
    {
        $usage = $this->usage()->byFeatureSlug($featureSlug)->first();

        if ($usage === null) {
            return null;
        }

        $usage->used = max($usage->used - $uses, 0);

        $usage->save();

        return $usage;
    }

    /**
     * Determine if the feature can be used.
     */
    public function canUseFeature(string $featureSlug): bool
    {
        $featureValue = $this->getFeatureValue($featureSlug);
        $usage = $this->usage()->byFeatureSlug($featureSlug)->first();

        if ($featureValue === 'true') {
            return true;
        }

        // If the feature value is zero, let's return false since
        // there's no uses available. (useful to disable countable features)
        if (! $usage || $usage->expired() || $featureValue === null || $featureValue === '0' || $featureValue === 'false') {
            return false;
        }

        // Check for available uses
        return $this->getFeatureRemainings($featureSlug) > 0;
    }

    /**
     * Get how many times the feature has been used.
     */
    public function getFeatureUsage(string $featureSlug): int
    {
        $usage = $this->usage()->byFeatureSlug($featureSlug)->first();

        return (! $usage || $usage->expired()) ? 0 : $usage->used;
    }

    /**
     * Get the available uses.
     */
    public function getFeatureRemainings(string $featureSlug): int
    {
        return $this->getFeatureValue($featureSlug) - $this->getFeatureUsage($featureSlug);
    }

    /**
     * @param string $featureSlug
     * @return string|null
     */
    public function getFeatureValue(string $featureSlug): ?string
    {
        $feature = $this->plan->features()->where('slug', $featureSlug)->first();

        return $feature->value ?? null;
    }
}
