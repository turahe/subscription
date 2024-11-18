<?php

namespace Turahe\Subscription\Tests\Unit;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Tests\TestCase;

class PlanTest extends TestCase
{
    #[Test]
    public function it_can_create_the_plan()
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

    #[Test]
    public function it_can_delete_a_plan()
    {
        $plan = Plan::factory()->create();
        $deleted = $plan->delete();

        $this->assertTrue($deleted);
        $this->assertSoftDeleted(config('subscription.tables.plans'), [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'description' => $plan->description,
        ]);
    }

    #[Test]
    public function it_errors_when_updating_the_plan()
    {
        $plan = Plan::factory()->create();
        $this->expectException(QueryException::class);

        $plan->update(['name' => null]);
    }

    #[Test]
    public function it_can_update_the_plan()
    {
        $plan = Plan::factory()->create();

        $update = ['name' => 'name'];
        $updated = $plan->update($update);

        $plan = $plan->where('name', $update['name'])->first();

        $this->assertTrue($updated);
        $this->assertEquals($update['name'], $plan->name);
    }

    #[Test]
    public function it_can_find_the_plan()
    {
        $plan = Plan::factory()->create();

        $found = Plan::find($plan->id);

        $this->assertInstanceOf(Plan::class, $found);
        $this->assertEquals($plan->username, $found->username);
    }

    #[Test]
    public function it_can_list_all_plans()
    {
        $plans = Plan::factory(3)->create();

        $this->assertInstanceOf(Collection::class, $plans);
        $this->assertCount(3, $plans->all());
    }

    #[Test]
    public function it_can_check_is_plan_is_free()
    {
        $plan = Plan::factory()->create(['price' => 0]);

        $this->assertTrue($plan->isFree());
    }

    #[Test]
    public function it_can_check_is_plan_is_trial()
    {
        $plan = Plan::factory()->create(['trial_period' => 3, 'trial_interval' => 'day']);

        $this->assertTrue($plan->hasTrial());
    }

    #[Test]
    public function it_can_check_is_plan_is_grace_period()
    {
        $plan = Plan::factory()->create(['grace_period' => 3, 'grace_interval' => 'day']);

        $this->assertTrue($plan->hasTrial());
    }

    #[Test]
    public function it_can_get_features_by_slug()
    {
        $plan = Plan::factory()->create();
        $feature = PlanFeature::factory()->create([
            'plan_id' => $plan->getKey(),
            'slug' => 'test-1',
        ]);

        $this->assertSame($feature->slug, $plan->getFeatureBySlug('test-1')->slug);
    }
}
