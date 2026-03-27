<?php

namespace Partymeister\Slides\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Partymeister\Slides\Models\Playlist;

class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'type' => 'video',
            'is_competition' => false,
        ];
    }
}
