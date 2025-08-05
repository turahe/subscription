<?php

namespace Turahe\Subscription\Tests\Unit;

use Carbon\Carbon;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Models\PlanSubscriptionUsage;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Factories\PlanFeatureFactory;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class PlanSubscriptionTest extends TestCase
{
    protected User $user;
    protected Plan $plan;
    protected PlanSubscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $this->plan = PlanFactory::new()->create([
            'trial_period' => 14,
            'trial_interval' => Interval::Day,
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
        ]);
        
        $this->subscription = $this->user->newPlanSubscription('main', $this->plan);
    }

    public function test_can_create_subscription()
    {
        $subscription = $this->user->newPlanSubscription('test', $this->plan);
        
        $this->assertInstanceOf(PlanSubscription::class, $subscription);
        $this->assertEquals('test', $subscription->name);
        $this->assertEquals($this->plan->id, $subscription->plan_id);
        $this->assertEquals($this->user->id, $subscription->subscriber_id);
        $this->assertEquals(User::class, $subscription->subscriber_type);
    }

    public function test_subscription_is_active()
    {
        $this->assertTrue($this->subscription->active());
    }

    public function test_subscription_on_trial()
    {
        $this->assertTrue($this->subscription->onTrial());
    }

    public function test_subscription_not_canceled()
    {
        $this->assertFalse($this->subscription->canceled());
    }

    public function test_subscription_not_ended()
    {
        $this->assertFalse($this->subscription->ended());
    }

    public function test_subscription_not_inactive()
    {
        $this->assertFalse($this->subscription->inactive());
    }

    public function test_can_cancel_subscription()
    {
        $this->subscription->cancel();
        
        $this->assertTrue($this->subscription->canceled());
        $this->assertTrue($this->subscription->active()); // Still active until period ends
    }

    public function test_can_cancel_subscription_immediately()
    {
        $this->subscription->cancel(true);
        
        $this->assertTrue($this->subscription->canceled());
        $this->assertFalse($this->subscription->active());
    }

    public function test_can_renew_subscription()
    {
        $originalEndDate = $this->subscription->ends_at;
        
        $this->subscription->renew();
        
        $this->assertTrue($this->subscription->ends_at->gt($originalEndDate));
        $this->assertTrue($this->subscription->active());
    }

    public function test_cannot_renew_canceled_subscription_after_period_ends()
    {
        $this->subscription->cancel(true);
        
        $this->expectException(\Exception::class);
        $this->subscription->renew();
    }

    public function test_can_change_plan()
    {
        $newPlan = PlanFactory::new()->create([
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
        ]);
        
        $this->subscription->changePlan($newPlan);
        
        $this->assertEquals($newPlan->id, $this->subscription->plan_id);
    }

    public function test_can_get_feature_value()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $value = $this->subscription->getFeatureValue('test_feature');
        
        $this->assertEquals(100, $value);
    }

    public function test_can_check_feature_usage()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $usage = $this->subscription->getFeatureUsage('test_feature');
        
        $this->assertEquals(0, $usage);
    }

    public function test_can_check_feature_remainings()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $remainings = $this->subscription->getFeatureRemainings('test_feature');
        
        $this->assertEquals(100, $remainings);
    }

    public function test_can_use_feature()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $canUse = $this->subscription->canUseFeature('test_feature');
        
        $this->assertTrue($canUse);
    }

    public function test_cannot_use_feature_when_limit_reached()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 1,
        ]);
        
        // Record usage to reach limit
        $this->subscription->recordFeatureUsage('test_feature', 1);
        
        $canUse = $this->subscription->canUseFeature('test_feature');
        
        $this->assertFalse($canUse);
    }

    public function test_can_record_feature_usage()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->subscription->recordFeatureUsage('test_feature', 5);
        
        $usage = $this->subscription->getFeatureUsage('test_feature');
        $this->assertEquals(5, $usage);
    }

    public function test_can_record_feature_usage_incremental()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->subscription->recordFeatureUsage('test_feature', 3);
        $this->subscription->recordFeatureUsage('test_feature', 2);
        
        $usage = $this->subscription->getFeatureUsage('test_feature');
        $this->assertEquals(5, $usage);
    }

    public function test_can_record_feature_usage_non_incremental()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->subscription->recordFeatureUsage('test_feature', 3);
        $this->subscription->recordFeatureUsage('test_feature', 7, false);
        
        $usage = $this->subscription->getFeatureUsage('test_feature');
        $this->assertEquals(7, $usage);
    }

    public function test_can_reduce_feature_usage()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->subscription->recordFeatureUsage('test_feature', 10);
        $this->subscription->reduceFeatureUsage('test_feature', 3);
        
        $usage = $this->subscription->getFeatureUsage('test_feature');
        $this->assertEquals(7, $usage);
    }

    public function test_can_clear_usage_data()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->plan->id,
            'name' => 'test_feature',
            'value' => 100,
        ]);
        
        $this->subscription->recordFeatureUsage('test_feature', 5);
        $this->subscription->usage()->delete();
        
        $usage = $this->subscription->getFeatureUsage('test_feature');
        $this->assertEquals(0, $usage);
    }

    public function test_subscription_scopes()
    {
        // Test byPlanId scope
        $subscriptions = PlanSubscription::byPlanId($this->plan->id)->get();
        $this->assertCount(1, $subscriptions);
        
        // Test ofSubscriber scope
        $userSubscriptions = PlanSubscription::ofSubscriber($this->user)->get();
        $this->assertCount(1, $userSubscriptions);
    }

    public function test_subscription_with_trial_ending()
    {
        $subscription = PlanSubscription::findEndingTrial(15)->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $subscription);
    }

    public function test_subscription_with_ended_trial()
    {
        $subscription = PlanSubscription::findEndedTrial()->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $subscription);
    }

    public function test_subscription_with_period_ending()
    {
        $subscription = PlanSubscription::findEndingPeriod(15)->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $subscription);
    }

    public function test_subscription_with_ended_period()
    {
        $subscription = PlanSubscription::findEndedPeriod()->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $subscription);
    }

    public function test_subscription_relationships()
    {
        $this->assertInstanceOf(Plan::class, $this->subscription->plan);
        $this->assertInstanceOf(User::class, $this->subscription->subscriber);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->subscription->usage);
    }
} 