<?php

namespace Turahe\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Subscription\Models\PlanSubscription;

class PlanSubscriptionFactory extends Factory
{
    protected $model = PlanSubscription::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'timezone' => $this->faker->timezone(),
            'trial_ends_at' => now()->addDays(14),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ];
    }
}
