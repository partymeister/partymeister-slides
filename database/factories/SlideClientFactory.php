<?php

namespace Partymeister\Slides\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Partymeister\Slides\Models\SlideClient;

class SlideClientFactory extends Factory
{
    protected $model = SlideClient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'type' => 'slidemeister-web',
            'ip_address' => $this->faker->ipv4(),
            'port' => '80',
            'sort_position' => $this->faker->numberBetween(1, 10),
            'configuration' => [],
        ];
    }
}
