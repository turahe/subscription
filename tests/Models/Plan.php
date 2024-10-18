<?php

namespace Turahe\Subscription\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Turahe\Subscription\Tests\Factories\PlanFactory;

class Plan extends \Turahe\Subscription\Models\Plan
{
    use HasFactory;

    protected static function newFactory()
    {
        return PlanFactory::new();
    }
}
