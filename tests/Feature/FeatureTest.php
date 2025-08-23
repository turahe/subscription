<?php

namespace Turahe\Subscription\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Factories\PlanFeatureFactory;
use Turahe\Subscription\Tests\TestCase;

class FeatureTest extends TestCase
{
    public function test_can_create_the_plan_feature()
    {
        $data = [
            'name' => 'plan feature 1',
            'slug' => 'plan-feature-1',
            'description' => $this->faker->sentence,
            'value' => 10,
        ];

        $plan = PlanFactory::new()->create();
        $feature = $plan->features()->create($data);

        $this->assertEquals($data['name'], $feature->name);
        $this->assertEquals($data['slug'], $feature->slug);
        $this->assertEquals($data['description'], $feature->description);
        $this->assertEquals($data['value'], $feature->value);
    }

    public function test_can_delete_a_plan_feature()
    {
        PlanFactory::new()->create()->each(function (Plan $plan) {
            $plan->features()->saveMany(PlanFeatureFactory::new()->count(3)->make());
        });

        $feature = PlanFeature::first();
        $deleted = $feature->delete();

        $this->assertTrue($deleted);
    }

    public function test_errors_when_updating_the_plan()
    {
        PlanFactory::new()->create()->each(function (Plan $plan) {
            $plan->features()->saveMany(PlanFeatureFactory::new()->count(3)->make());
        });

        $feature = PlanFeature::first();
        $this->expectException(QueryException::class);

        $feature->update(['name' => null]);
    }

    public function test_can_update_the_plan_feature()
    {
        PlanFactory::new()->create()->each(function (Plan $plan) {
            $plan->features()->saveMany(PlanFeatureFactory::new()->count(3)->make());
        });

        $feature = PlanFeature::first();
        $update = ['name' => 'name'];
        $updated = $feature->update($update);

        $plan = $feature->where('name', $update['name'])->first();

        $this->assertTrue($updated);
        $this->assertEquals($update['name'], $plan->name);
    }

    public function test_can_list_all_plans()
    {
        PlanFactory::new()->create()->each(function (Plan $plan) {
            $plan->features()->saveMany(PlanFeatureFactory::new()->count(3)->make());
        });

        $this->assertCount(3, PlanFeature::all());
    }

    public function test_can_feature_reset()
    {
        $data = [
            'resettable_period' => 1,
            'resettable_interval' => Interval::Month,
        ];

        $plan = PlanFactory::new()->create();
        $feature = $plan->features()->save(PlanFeatureFactory::new()->make($data));

        $this->assertEquals($data['resettable_period'], $feature->resettable_period);
        $this->assertEquals($data['resettable_interval'], $feature->resettable_interval);

        $this->assertInstanceOf(Carbon::class, $feature->getResetDate());
        $this->assertEquals(Carbon::now()->addMonth()->format('Y-m-d H:i:s'), $feature->getResetDate()->format('Y-m-d H:i:s'));
    }
}
