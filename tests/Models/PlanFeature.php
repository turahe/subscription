<?php

namespace Turahe\Subscription\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Turahe\Subscription\Tests\Factories\PlanFeatureFactory;

class PlanFeature extends \Turahe\Subscription\Models\PlanFeature
{
    use HasFactory;

    protected static function newFactory()
    {
        return PlanFeatureFactory::new();
    }
}
