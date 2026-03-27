<?php

namespace Partymeister\Slides\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Partymeister\Slides\Models\Slide;

class SlideFactory extends Factory
{
    protected $model = Slide::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'slide_type' => 'default',
            'definitions' => '{}',
        ];
    }
}
