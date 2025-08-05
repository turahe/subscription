<?php

declare(strict_types=1);

namespace Turahe\Subscription\Tests\Feature\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Factories\PlanSubscriptionFactory;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class HasPlanSubscriptionsTest extends TestCase
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
        
        $this->plan = PlanFactory::new()->create([
            'name' => 'Premium Plan',
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
            'trial_period' => 15,
            'trial_interval' => Interval::Day,
        ]);
    }

    public function test_it_can_get_plan_subscriptions_relation(): void
    {
        $this->assertInstanceOf(Collection::class, $this->user->planSubscriptions);
        $this->assertTrue($this->user->planSubscriptions->isEmpty());
    }

    public function test_it_can_get_active_plan_subscriptions(): void
    {
        // Create active subscription
        $activeSubscription = PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->addDays(30),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Create inactive subscription (ended)
        $inactiveSubscription = PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->subDays(30),
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        $activeSubscriptions = $this->user->activePlanSubscriptions();

        $this->assertInstanceOf(Collection::class, $activeSubscriptions);
        $this->assertCount(1, $activeSubscriptions);
        $this->assertTrue($activeSubscriptions->contains($activeSubscription));
        $this->assertFalse($activeSubscriptions->contains($inactiveSubscription));
    }

    public function test_it_can_get_plan_subscription_by_slug(): void
    {
        $subscription = PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'name' => 'test-subscription',
            'slug' => 'test-subscription',
        ]);

        $foundSubscription = $this->user->planSubscription('test-subscription');

        $this->assertInstanceOf(PlanSubscription::class, $foundSubscription);
        $this->assertEquals($subscription->id, $foundSubscription->id);
    }

    public function test_it_returns_null_for_non_existent_subscription_slug(): void
    {
        $foundSubscription = $this->user->planSubscription('non-existent');

        $this->assertNull($foundSubscription);
    }

    public function test_it_can_get_subscribed_plans(): void
    {
        $plan2 = PlanFactory::new()->create(['name' => 'Basic Plan']);
        
        // Create active subscriptions for different plans
        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->addDays(30),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $plan2->id,
            'trial_ends_at' => now()->addDays(30),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Create inactive subscription (should be excluded)
        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->subDays(30),
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        $subscribedPlans = $this->user->subscribedPlans();

        $this->assertInstanceOf(Collection::class, $subscribedPlans);
        $this->assertCount(2, $subscribedPlans);
        $this->assertTrue($subscribedPlans->contains($this->plan));
        $this->assertTrue($subscribedPlans->contains($plan2));
    }

    public function test_it_returns_empty_collection_when_no_active_subscriptions(): void
    {
        $subscribedPlans = $this->user->subscribedPlans();

        $this->assertInstanceOf(Collection::class, $subscribedPlans);
        $this->assertTrue($subscribedPlans->isEmpty());
    }

    public function test_it_can_check_if_subscribed_to_plan_by_id(): void
    {
        // Create active subscription
        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->addDays(30),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertTrue($this->user->subscribedTo($this->plan->id));
        $this->assertTrue($this->user->subscribedTo((string) $this->plan->id));
    }

    public function test_it_returns_false_for_non_subscribed_plan(): void
    {
        $this->assertFalse($this->user->subscribedTo(999));
        $this->assertFalse($this->user->subscribedTo('999'));
    }

    public function test_it_returns_false_for_inactive_subscription(): void
    {
        // Create inactive subscription
        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->subDays(30),
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        $this->assertFalse($this->user->subscribedTo($this->plan->id));
    }

    public function test_it_can_create_new_plan_subscription(): void
    {
        $subscription = $this->user->newPlanSubscription('premium', $this->plan);

        $this->assertInstanceOf(PlanSubscription::class, $subscription);
        $this->assertEquals('premium', $subscription->name);
        $this->assertEquals($this->plan->id, $subscription->plan_id);
        $this->assertEquals($this->user->id, $subscription->subscriber_id);
        $this->assertEquals(User::class, $subscription->subscriber_type);
        $this->assertNotNull($subscription->trial_ends_at);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->ends_at);

        $this->assertDatabaseHas(config('subscription.tables.subscriptions'), [
            'name' => 'premium',
            'plan_id' => $this->plan->id,
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
        ]);
    }

    public function test_it_can_create_new_plan_subscription_with_custom_start_date(): void
    {
        $startDate = Carbon::parse('2024-01-01 10:00:00');
        $subscription = $this->user->newPlanSubscription('premium', $this->plan, $startDate);

        $this->assertInstanceOf(PlanSubscription::class, $subscription);
        
        // Check that trial ends at the expected date (15 days after start)
        $expectedTrialEnd = $startDate->copy()->addDays(15);
        $this->assertEquals($expectedTrialEnd->format('Y-m-d'), $subscription->trial_ends_at->format('Y-m-d'));
        
        // Check that the subscription period starts after trial ends
        $this->assertTrue($subscription->starts_at->gt($subscription->trial_ends_at), 
            'Subscription start date should be after trial end date');
        $this->assertTrue($subscription->ends_at->gt($subscription->starts_at));
    }

    public function test_it_calculates_trial_period_correctly(): void
    {
        $subscription = $this->user->newPlanSubscription('premium', $this->plan);

        // Plan has 15 days trial period
        $expectedTrialEnd = Carbon::now()->addDays(15);
        
        $this->assertEquals(
            $expectedTrialEnd->format('Y-m-d'),
            $subscription->trial_ends_at->format('Y-m-d')
        );
    }

    public function test_it_calculates_subscription_period_correctly(): void
    {
        $subscription = $this->user->newPlanSubscription('premium', $this->plan);

        // Plan has 1 month invoice period
        $expectedStart = Carbon::now()->addDays(15); // After trial ends
        $expectedEnd = $expectedStart->copy()->addMonth();

        $this->assertEquals(
            $expectedStart->format('Y-m-d'),
            $subscription->starts_at->format('Y-m-d')
        );
        
        $this->assertEquals(
            $expectedEnd->format('Y-m-d'),
            $subscription->ends_at->format('Y-m-d')
        );
    }

    public function test_it_deletes_subscriptions_when_model_is_deleted(): void
    {
        // Create subscriptions for the user
        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
        ]);

        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
        ]);

        $this->assertCount(2, $this->user->planSubscriptions);

        // Delete the user
        $this->user->delete();

        // Check that subscriptions are deleted
        $this->assertDatabaseMissing(config('subscription.tables.subscriptions'), [
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
        ]);
    }

    public function test_it_handles_plan_without_trial_period(): void
    {
        $planWithoutTrial = PlanFactory::new()->create([
            'trial_period' => 0,
            'trial_interval' => Interval::Day,
        ]);

        $subscription = $this->user->newPlanSubscription('no-trial', $planWithoutTrial);

        $this->assertNull($subscription->trial_ends_at);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->ends_at);
    }

    public function test_it_handles_multiple_active_subscriptions_to_same_plan(): void
    {
        // Create multiple active subscriptions to the same plan
        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->addDays(30),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        PlanSubscriptionFactory::new()->create([
            'subscriber_id' => $this->user->id,
            'subscriber_type' => User::class,
            'plan_id' => $this->plan->id,
            'trial_ends_at' => now()->addDays(30),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $subscribedPlans = $this->user->subscribedPlans();

        // Should only return unique plans
        $this->assertCount(1, $subscribedPlans);
        $this->assertTrue($subscribedPlans->contains($this->plan));
    }
}
