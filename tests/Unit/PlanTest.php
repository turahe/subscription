<?php

namespace Turahe\Subscription\Tests\Unit;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Factories\PlanFeatureFactory;
use Turahe\Subscription\Tests\TestCase;

class PlanTest extends TestCase
{
    public function test_can_create_the_plan()
    {
        $data = [
            'name' => 'plan 1',
            'slug' => 'plan-1',
            'description' => $this->faker->sentence,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->randomDigitNotNull(),
            'currency' => $this->faker->currencyCode,
        ];

        $plan = Plan::create($data);

        $this->assertEquals($data['name'], $plan->name);
        $this->assertEquals($data['description'], $plan->description);
        $this->assertEquals($data['is_active'], $plan->is_active);
        $this->assertEquals($data['currency'], $plan->currency);
    }

    public function test_can_delete_a_plan()
    {
        $plan = PlanFactory::new()->create();
        $deleted = $plan->delete();

        $this->assertTrue($deleted);
        $this->assertSoftDeleted(config('subscription.tables.plans'), [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'description' => $plan->description,
        ]);
    }

    public function test_errors_when_updating_the_plan()
    {
        $plan = PlanFactory::new()->create();
        $this->expectException(QueryException::class);

        $plan->update(['name' => null]);
    }

    public function test_can_update_the_plan()
    {
        $plan = PlanFactory::new()->create();

        $update = ['name' => 'name'];
        $updated = $plan->update($update);

        $plan = $plan->where('name', $update['name'])->first();

        $this->assertTrue($updated);
        $this->assertEquals($update['name'], $plan->name);
    }

    public function test_can_find_the_plan()
    {
        $plan = PlanFactory::new()->create();

        $found = Plan::find($plan->id);

        $this->assertInstanceOf(Plan::class, $found);
        $this->assertEquals($plan->username, $found->username);
    }

    public function test_can_list_all_plans()
    {
        $plans = PlanFactory::new()->count(3)->create();

        $this->assertInstanceOf(Collection::class, $plans);
        $this->assertCount(3, $plans->all());
    }

    public function test_can_check_is_plan_is_free()
    {
        $plan = PlanFactory::new()->create(['price' => 0]);

        $this->assertTrue($plan->isFree());
    }

    public function test_can_check_is_plan_is_trial()
    {
        $plan = PlanFactory::new()->create(['trial_period' => 3, 'trial_interval' => Interval::Day]);

        $this->assertTrue($plan->hasTrial());
    }

    public function test_can_check_is_plan_is_grace_period()
    {
        $plan = PlanFactory::new()->create(['grace_period' => 3, 'grace_interval' => Interval::Day]);

        $this->assertTrue($plan->hasTrial());
    }

    public function test_can_get_features_by_slug()
    {
        $plan = PlanFactory::new()->create();
        $feature = PlanFeatureFactory::new()->create([
            'plan_id' => $plan->getKey(),
            'slug' => 'test-1',
        ]);

        $this->assertSame($feature->slug, $plan->getFeatureBySlug('test-1')->slug);
    }
}
