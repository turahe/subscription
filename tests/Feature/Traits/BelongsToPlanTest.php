<?php

namespace Turahe\Subscription\Tests\Feature\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Factories\PlanFeatureFactory;
use Turahe\Subscription\Tests\TestCase;

class BelongsToPlanTest extends TestCase
{
    public function test_belongs_to_plan()
    {
        $plan = PlanFactory::new()->create();
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $plan->getKey(),
        ]);

        $this->assertInstanceOf(BelongsTo::class, $feature->plan());
    }

    public function test_scope_byp_lan_id()
    {
        $plan = PlanFactory::new()->create();
        PlanFeatureFactory::new()->count(3)->create([
            'plan_id' => $plan->getKey(),
        ]);

        $features = PlanFeature::byPlanId($plan->getKey())->get();

        $this->assertCount(3, $features);
        $this->assertInstanceOf(Collection::class, $features);

    }
}
