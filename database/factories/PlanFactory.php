<?php

namespace Turahe\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
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
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->randomDigitNotNull(),
            'signup_fee' => 1.99,
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month->value,
            'trial_period' => 15,
            'trial_interval' => Interval::Day->value,
            'currency' => 'USD',
        ];
    }
}
