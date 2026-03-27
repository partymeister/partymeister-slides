<?php

namespace Partymeister\Slides\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Partymeister\Slides\Models\Transition;

class TransitionFactory extends Factory
{
    protected $model = Transition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'client_type' => 'slidemeister-web',
            'identifier' => $this->faker->unique()->slug(1),
            'default_duration' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
