<?php

namespace Turahe\Subscription\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Subscription\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
