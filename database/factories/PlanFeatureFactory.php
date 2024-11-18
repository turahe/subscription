<?php

namespace Turahe\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Subscription\Models\PlanFeature;

class PlanFeatureFactory extends Factory
{
    protected $model = PlanFeature::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'value' => $this->faker->randomFloat(),
        ];
    }
}
