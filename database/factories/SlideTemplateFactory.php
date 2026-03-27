<?php

namespace Partymeister\Slides\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Partymeister\Slides\Models\SlideTemplate;

class SlideTemplateFactory extends Factory
{
    protected $model = SlideTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'template_for' => $this->faker->randomElement(['competition', 'event', 'prizegiving']),
            'definitions' => '{}',
            'cached_html_preview' => '',
            'cached_html_final' => '',
        ];
    }
}
