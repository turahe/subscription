<?php

namespace Turahe\Subscription\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Tests\Models\Plan;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class SubscriptionTest extends TestCase
{
    protected $user;

    protected $plan;

    protected $planSubscription;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->plan = Plan::factory()->create();

    }

    #[Test]
    public function it_can_create_the_plan_subscription()
    {
        $planSubscription = $this->user->newPlanSubscription('test-1', $this->plan, now());

        $this->assertInstanceOf(PlanSubscription::class, $planSubscription);
        $this->assertTrue($this->user->subscribedTo($this->plan->getKey()));
        $this->assertTrue($planSubscription->active());
    }

    #[Test]
    public function it_can_update_the_plan_subscription()
    {
        $planSubscription = $this->user->newPlanSubscription('test-1', $this->plan, now());

        $this->assertInstanceOf(PlanSubscription::class, $planSubscription);
        $this->assertTrue($planSubscription->active());
    }

    #[Test]
    public function it_can_model_has_relation_with_subscription_without_plan(): void
    {
        $planSubscription = $this->user->newPlanSubscription('test-1', $this->plan, now());
        $this->assertInstanceOf(PlanSubscription::class, $planSubscription);
    }
}
