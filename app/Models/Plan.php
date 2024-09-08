<?php

declare(strict_types=1);

namespace Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Subscription\Traits\HasSlug;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\SlugOptions;

/**
 * Modules\Subscription\Models\Plan.
 *
 * @property int $id
 * @property string $slug
 * @property array $name
 * @property array $description
 * @property bool $is_active
 * @property float $price
 * @property float $signup_fee
 * @property string $currency
 * @property int $trial_period
 * @property string $trial_interval
 * @property int $invoice_period
 * @property string $invoice_interval
 * @property int $grace_period
 * @property string $grace_interval
 * @property int $prorate_day
 * @property int $prorate_period
 * @property int $prorate_extend_due
 * @property int $active_subscribers_limit
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Subscription\Models\Feature[] $features
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Subscription\Models\Subscription[] $subscriptions
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan ordered($direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereActiveSubscribersLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereGraceInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereGracePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereInvoiceInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereInvoicePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereProrateDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereProrateExtendDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereProratePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereSignupFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereTrialInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereTrialPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Subscription\Models\Plan whereUpdatedAt($value)
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan withoutTrashed()
 * @property-read int|null $features_count
 * @property-read int|null $subscriptions_count
 * @mixin \Eloquent
 */
class Plan extends Model implements Sortable
{
    use HasFactory;
    use HasSlug;
    use HasUlids;
    use SoftDeletes;
    use SortableTrait;

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
        'sort_order',
    ];

    protected $casts = [
        'slug' => 'string',
        'is_active' => 'boolean',
        'price' => 'float',
        'signup_fee' => 'float',
        'currency' => 'string',
        'trial_period' => 'integer',
        'trial_interval' => 'string',
        'invoice_period' => 'integer',
        'invoice_interval' => 'string',
        'grace_period' => 'integer',
        'grace_interval' => 'string',
        'prorate_day' => 'integer',
        'prorate_period' => 'integer',
        'prorate_extend_due' => 'integer',
        'active_subscribers_limit' => 'integer',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected $dateFormat = 'U';

    public array $sortable = [
        'order_column_name' => 'record_ordering',
    ];

    public function getTable(): string
    {
        return config('subscription.tables.plans');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function ($plan): void {
            $plan->features()->delete();
            $plan->subscriptions()->delete();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
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
        return $this->price <= 0.00;
    }

    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    public function hasGrace(): bool
    {
        return $this->grace_period && $this->grace_interval;
    }

    public function getFeatureBySlug(string $featureSlug): ?Feature
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
