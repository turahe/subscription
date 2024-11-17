<?php

namespace Turahe\Subscription\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Ledger\Tests\Models\Organization;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
        ];
    }
}
