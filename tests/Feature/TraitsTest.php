<?php

namespace Turahe\Subscription\Tests\Unit;

use Turahe\Subscription\Traits\HasPlanSubscriptions;
use Turahe\Subscription\Traits\BelongsToPlan;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class TraitsTest extends TestCase
{
    protected User $user;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $this->plan = PlanFactory::new()->create();
    }

    public function test_has_plan_subscriptions_trait()
    {
        $this->assertTrue(in_array(HasPlanSubscriptions::class, class_uses_recursive($this->user)));
    }

    public function test_has_plan_subscriptions_plan_subscriptions_relationship()
    {
        $this->assertTrue(method_exists($this->user, 'planSubscriptions'));
        
        $subscriptions = $this->user->planSubscriptions();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $subscriptions);
    }

    public function test_has_plan_subscriptions_active_plan_subscriptions()
    {
        $this->assertTrue(method_exists($this->user, 'activePlanSubscriptions'));
        
        $activeSubscriptions = $this->user->activePlanSubscriptions();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $activeSubscriptions);
    }

    public function test_has_plan_subscriptions_plan_subscription()
    {
        $this->assertTrue(method_exists($this->user, 'planSubscription'));
        
        $subscription = $this->user->planSubscription('main');
        $this->assertNull($subscription); // No subscription exists yet
    }

    public function test_has_plan_subscriptions_subscribed_plans()
    {
        $this->assertTrue(method_exists($this->user, 'subscribedPlans'));
        
        $subscribedPlans = $this->user->subscribedPlans();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $subscribedPlans);
    }

    public function test_has_plan_subscriptions_subscribed_to()
    {
        $this->assertTrue(method_exists($this->user, 'subscribedTo'));
        
        $subscribed = $this->user->subscribedTo($this->plan->id);
        $this->assertFalse($subscribed); // No subscription exists yet
    }

    public function test_has_plan_subscriptions_new_plan_subscription()
    {
        $this->assertTrue(method_exists($this->user, 'newPlanSubscription'));
        
        $subscription = $this->user->newPlanSubscription('main', $this->plan);
        $this->assertInstanceOf(PlanSubscription::class, $subscription);
        $this->assertEquals('main', $subscription->name);
        $this->assertEquals($this->plan->id, $subscription->plan_id);
        $this->assertEquals($this->user->id, $subscription->subscriber_id);
    }

    public function test_has_plan_subscriptions_new_plan_subscription_with_start_date()
    {
        $startDate = now()->addDays(7);
        $subscription = $this->user->newPlanSubscription('main', $this->plan, $startDate);
        
        $this->assertInstanceOf(PlanSubscription::class, $subscription);
        $this->assertEquals($startDate->format('Y-m-d'), $subscription->starts_at->format('Y-m-d'));
    }

    public function test_has_plan_subscriptions_multiple_subscriptions()
    {
        $subscription1 = $this->user->newPlanSubscription('main', $this->plan);
        $subscription2 = $this->user->newPlanSubscription('secondary', $this->plan);
        
        $this->assertInstanceOf(PlanSubscription::class, $subscription1);
        $this->assertInstanceOf(PlanSubscription::class, $subscription2);
        $this->assertEquals('main', $subscription1->name);
        $this->assertEquals('secondary', $subscription2->name);
    }

    public function test_has_plan_subscriptions_subscription_after_creation()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        
        $subscription = $this->user->planSubscription('main');
        $this->assertInstanceOf(PlanSubscription::class, $subscription);
        $this->assertEquals('main', $subscription->name);
    }

    public function test_has_plan_subscriptions_subscribed_to_after_creation()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        
        $subscribed = $this->user->subscribedTo($this->plan->id);
        $this->assertTrue($subscribed);
    }

    public function test_has_plan_subscriptions_active_subscriptions_after_creation()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        
        $activeSubscriptions = $this->user->activePlanSubscriptions();
        $this->assertCount(1, $activeSubscriptions);
        $this->assertEquals('main', $activeSubscriptions->first()->name);
    }

    public function test_has_plan_subscriptions_subscribed_plans_after_creation()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        
        $subscribedPlans = $this->user->subscribedPlans();
        $this->assertCount(1, $subscribedPlans);
        $this->assertEquals($this->plan->id, $subscribedPlans->first()->id);
    }

    public function test_belongs_to_plan_trait()
    {
        $feature = \Turahe\Subscription\Models\PlanFeature::create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->assertTrue(in_array(BelongsToPlan::class, class_uses_recursive($feature)));
    }

    public function test_belongs_to_plan_plan_relationship()
    {
        $feature = \Turahe\Subscription\Models\PlanFeature::create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->assertTrue(method_exists($feature, 'plan'));
        
        $plan = $feature->plan;
        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertEquals($this->plan->id, $plan->id);
    }

    public function test_belongs_to_plan_plan_id_access()
    {
        $feature = \Turahe\Subscription\Models\PlanFeature::create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->assertEquals($this->plan->id, $feature->plan_id);
    }

    public function test_traits_serialization()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        
        $serialized = serialize($this->user);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(User::class, $unserialized);
        $this->assertTrue(method_exists($unserialized, 'planSubscriptions'));
    }

    public function test_traits_json_serialization()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        
        $json = json_encode($this->user);
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
    }

    public function test_traits_to_string()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        
        $this->assertIsString((string) $this->user);
    }

    public function test_traits_equality()
    {
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => 'password',
        ]);
        
        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => 'password',
        ]);
        
        $this->assertNotEquals($user1->id, $user2->id);
    }

    public function test_traits_methods_exist()
    {
        $this->assertTrue(method_exists($this->user, 'planSubscriptions'));
        $this->assertTrue(method_exists($this->user, 'activePlanSubscriptions'));
        $this->assertTrue(method_exists($this->user, 'planSubscription'));
        $this->assertTrue(method_exists($this->user, 'subscribedPlans'));
        $this->assertTrue(method_exists($this->user, 'subscribedTo'));
        $this->assertTrue(method_exists($this->user, 'newPlanSubscription'));
    }

    public function test_traits_are_callable()
    {
        $this->assertTrue(is_callable($this->user));
    }

    public function test_traits_with_deleted_user()
    {
        $this->user->newPlanSubscription('main', $this->plan);
        $subscriptionId = $this->user->planSubscription('main')->id;
        
        $this->user->delete();
        
        // Test that subscription is also deleted
        $this->assertNull(PlanSubscription::find($subscriptionId));
    }

    public function test_traits_with_multiple_plans()
    {
        $plan2 = PlanFactory::new()->create();
        
        $this->user->newPlanSubscription('main', $this->plan);
        $this->user->newPlanSubscription('secondary', $plan2);
        
        $subscribedPlans = $this->user->subscribedPlans();
        $this->assertCount(2, $subscribedPlans);
        
        $planIds = $subscribedPlans->pluck('id')->toArray();
        $this->assertContains($this->plan->id, $planIds);
        $this->assertContains($plan2->id, $planIds);
    }

    public function test_traits_with_canceled_subscription()
    {
        $subscription = $this->user->newPlanSubscription('main', $this->plan);
        $subscription->cancel();
        
        $activeSubscriptions = $this->user->activePlanSubscriptions();
        $this->assertCount(0, $activeSubscriptions);
        
        $subscribed = $this->user->subscribedTo($this->plan->id);
        $this->assertFalse($subscribed);
    }
} 