<?php

namespace Turahe\Subscription\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        $name = $this->faker->word;

        return [
            'name' => $name,
            'description' => $this->faker->sentence,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->randomDigitNotNull(),
            'signup_fee' => 1.99,
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
            'trial_period' => 15,
            'trial_interval' => Interval::Day,
            'currency' => 'USD',
        ];
    }
}
