<?php

namespace Turahe\Subscription\Tests\Feature\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Tests\TestCase;

class BelongsToPlanTest extends TestCase
{
    public function testBelongsToPlan()
    {
        $plan = Plan::factory()->create();
        $feature = PlanFeature::factory()->create([
            'plan_id' => $plan->getKey(),
        ]);

        $this->assertInstanceOf(BelongsTo::class, $feature->plan());
    }

    public function testScopeByPlanId()
    {
        $plan = Plan::factory()->create();
        PlanFeature::factory(3)->create([
            'plan_id' => $plan->getKey(),
        ]);

        $features = PlanFeature::byPlanId($plan->getKey())->get();

        $this->assertCount(3, $features);
        $this->assertInstanceOf(Collection::class, $features);

    }
}
