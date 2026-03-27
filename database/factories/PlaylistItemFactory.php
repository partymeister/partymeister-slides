<?php

namespace Partymeister\Slides\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\PlaylistItem;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\Transition;

class PlaylistItemFactory extends Factory
{
    protected $model = PlaylistItem::class;

    public function definition(): array
    {
        return [
            'playlist_id' => Playlist::factory(),
            'type' => 'image',
            'slide_type' => 'default',
            'slide_id' => Slide::factory(),
            'duration' => 5,
            'transition_id' => Transition::factory(),
            'transition_duration' => 500,
            'is_advanced_manually' => false,
            'is_muted' => false,
            'midi_note' => 0,
            'metadata' => '{}',
            'callback_hash' => '',
            'callback_delay' => 0,
            'sort_position' => $this->faker->numberBetween(1, 100),
        ];
    }
}
