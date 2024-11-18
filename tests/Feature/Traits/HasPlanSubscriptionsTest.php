<?php

namespace Turahe\Subscription\Tests\Feature\Traits;

use PHPUnit\Framework\Attributes\Test;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class HasPlanSubscriptionsTest extends TestCase
{
    #[Test]
    public function it_can_model_has_relation_with_subscription_without_plan(): void
    {
        $user = User::create([]);
        $this->assertEmpty($user->planSubscriptions);
    }

    #[Test]
    public function it_can_model_has_new_plan_subscription()
    {
        $user = User::create([]);
        $plan = Plan::factory()->create();
        $planSubscription = $user->newPlanSubscription('test-1', $plan, now());

        $this->assertInstanceOf(PlanSubscription::class, $planSubscription);

        $this->assertDatabaseHas(config('subscription.tables.subscriptions'), [
            'plan_id' => $plan->getKey(),
            'subscriber_id' => $user->getKey(),
            'subscriber_type' => $user->getMorphClass(),
            'name' => 'test-1',
        ]);

    }
}
