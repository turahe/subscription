<?php

namespace Turahe\Subscription\Tests\Unit;

use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class SubscriptionTest extends TestCase
{
    protected $user;

    protected $plan;

    protected $planSubscription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([]);
        $this->plan = PlanFactory::new()->create();

    }

    public function test_can_create_the_plan_subscription()
    {
        $planSubscription = $this->user->newPlanSubscription('test-1', $this->plan, now());

        $this->assertInstanceOf(PlanSubscription::class, $planSubscription);
        $this->assertTrue($this->user->subscribedTo($this->plan->getKey()));
        $this->assertTrue($planSubscription->active());
    }

    public function test_can_update_the_plan_subscription()
    {
        $planSubscription = $this->user->newPlanSubscription('test-1', $this->plan, now());

        $this->assertInstanceOf(PlanSubscription::class, $planSubscription);
        $this->assertTrue($planSubscription->active());
    }

    public function test_can_model_has_relation_with_subscription_without_plan(): void
    {
        $planSubscription = $this->user->newPlanSubscription('test-1', $this->plan, now());
        $this->assertInstanceOf(PlanSubscription::class, $planSubscription);
    }
}
