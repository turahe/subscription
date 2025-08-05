<?php

namespace Turahe\Subscription\Tests\Unit;

use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Models\PlanSubscriptionUsage;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Factories\PlanFeatureFactory;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class PlanSubscriptionUsageTest extends TestCase
{
    protected User $user;
    protected PlanSubscription $subscription;
    protected PlanSubscriptionUsage $usage;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $plan = PlanFactory::new()->create();
        $this->subscription = $this->user->newPlanSubscription('main', $plan);
        
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $plan->id,
            'name' => 'test_feature',
        ]);
        
        $this->usage = PlanSubscriptionUsage::create([
            'plan_subscription_id' => $this->subscription->id,
            'plan_feature_id' => $feature->id,
            'used' => 5,
        ]);
    }

    public function test_can_create_usage_record()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->subscription->plan_id,
            'name' => 'new_feature',
        ]);
        
        $usage = PlanSubscriptionUsage::create([
            'plan_subscription_id' => $this->subscription->id,
            'plan_feature_id' => $feature->id,
            'used' => 10,
        ]);
        
        $this->assertInstanceOf(PlanSubscriptionUsage::class, $usage);
        $this->assertEquals($this->subscription->id, $usage->plan_subscription_id);
        $this->assertEquals($feature->id, $usage->plan_feature_id);
        $this->assertEquals(10, $usage->used);
    }

    public function test_can_update_usage_record()
    {
        $this->usage->update(['used' => 15]);
        
        $this->assertEquals(15, $this->usage->fresh()->used);
    }

    public function test_can_delete_usage_record()
    {
        $usageId = $this->usage->id;
        $deleted = $this->usage->delete();
        
        $this->assertTrue($deleted);
        $this->assertNull(PlanSubscriptionUsage::find($usageId));
    }

    public function test_usage_has_subscription_relationship()
    {
        $this->assertInstanceOf(PlanSubscription::class, $this->usage->subscription);
        $this->assertEquals($this->subscription->id, $this->usage->subscription->id);
    }

    public function test_usage_has_feature_relationship()
    {
        $this->assertInstanceOf(\Turahe\Subscription\Models\PlanFeature::class, $this->usage->feature);
    }

    public function test_can_get_usage_by_subscription()
    {
        $usages = PlanSubscriptionUsage::where('plan_subscription_id', $this->subscription->id)->get();
        
        $this->assertCount(1, $usages);
        $this->assertEquals($this->usage->id, $usages->first()->id);
    }

    public function test_can_get_usage_by_feature()
    {
        $usages = PlanSubscriptionUsage::where('plan_feature_id', $this->usage->plan_feature_id)->get();
        
        $this->assertCount(1, $usages);
        $this->assertEquals($this->usage->id, $usages->first()->id);
    }

    public function test_usage_table_name()
    {
        $this->assertEquals('plan_subscription_usage', $this->usage->getTable());
    }

    public function test_usage_fillable_fields()
    {
        $data = [
            'plan_subscription_id' => $this->subscription->id,
            'plan_feature_id' => $this->usage->plan_feature_id,
            'used' => 20,
        ];
        
        $usage = PlanSubscriptionUsage::create($data);
        
        $this->assertEquals($data['used'], $usage->used);
    }

    public function test_usage_casts()
    {
        $this->assertIsInt($this->usage->used);
    }

    public function test_can_increment_usage()
    {
        $originalUsed = $this->usage->used;
        $this->usage->increment('used', 3);
        
        $this->assertEquals($originalUsed + 3, $this->usage->fresh()->used);
    }

    public function test_can_decrement_usage()
    {
        $originalUsed = $this->usage->used;
        $this->usage->decrement('used', 2);
        
        $this->assertEquals($originalUsed - 2, $this->usage->fresh()->used);
    }

    public function test_usage_soft_deletes()
    {
        $usageId = $this->usage->id;
        $this->usage->delete();
        
        $this->assertSoftDeleted('plan_subscription_usage', ['id' => $usageId]);
    }

    public function test_can_restore_usage()
    {
        $this->usage->delete();
        $this->usage->restore();
        
        $this->assertNotSoftDeleted('plan_subscription_usage', ['id' => $this->usage->id]);
    }

    public function test_usage_with_zero_usage()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->subscription->plan_id,
            'name' => 'zero_feature',
        ]);
        
        $usage = PlanSubscriptionUsage::create([
            'plan_subscription_id' => $this->subscription->id,
            'plan_feature_id' => $feature->id,
            'used' => 0,
        ]);
        
        $this->assertEquals(0, $usage->used);
    }

    public function test_usage_with_negative_usage()
    {
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $this->subscription->plan_id,
            'name' => 'negative_feature',
        ]);
        
        $usage = PlanSubscriptionUsage::create([
            'plan_subscription_id' => $this->subscription->id,
            'plan_feature_id' => $feature->id,
            'used' => -5,
        ]);
        
        $this->assertEquals(-5, $usage->used);
    }

    public function test_usage_timestamps()
    {
        $this->assertNotNull($this->usage->created_at);
        $this->assertNotNull($this->usage->updated_at);
    }

    public function test_usage_user_stamps()
    {
        // Test that the HasUserStamps trait is loaded
        $this->assertTrue(method_exists($this->usage, 'bootHasUserStamps'));
        $this->assertTrue(method_exists($this->usage, 'author'));
        $this->assertTrue(method_exists($this->usage, 'editor'));
        $this->assertTrue(method_exists($this->usage, 'destroyer'));
    }
} 