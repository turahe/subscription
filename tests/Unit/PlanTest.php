<?php

namespace Turahe\Subscription\Tests\Unit;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Turahe\Subscription\Tests\Models\Plan;
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
        ];

        $plan = Plan::create($data);

        $this->assertEquals($data['name'], $plan->name);
        $this->assertEquals($data['description'], $plan->description);
        $this->assertEquals($data['is_active'], $plan->is_active);
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
}
