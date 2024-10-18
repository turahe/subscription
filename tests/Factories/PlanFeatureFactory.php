<?php

namespace Turahe\Subscription\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Subscription\Tests\Models\PlanFeature;

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
