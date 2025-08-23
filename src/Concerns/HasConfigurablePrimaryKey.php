<?php

declare(strict_types=1);

namespace Turahe\Subscription\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasConfigurablePrimaryKey
{
    /**
     * Boot the trait.
     */
    protected static function bootHasConfigurablePrimaryKey(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->getKey())) {
                $model->setAttribute($model->getKeyName(), (string) Str::ulid());
            }
        });
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the primary key type.
     */
    public function getPrimaryKeyType(): string
    {
        return 'ulid';
    }
} 