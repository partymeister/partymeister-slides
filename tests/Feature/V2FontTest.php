<?php

pest()->group('V2', 'Font');

describe('V2 Fonts API', function () {
    it('returns fonts without auth (public endpoint)', function () {
        $response = $this->getJson('/api/v2/fonts');
        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
    });

    it('returns font data from config', function () {
        config(['partymeister-slides-fonts.fonts' => [
            ['name' => 'Exo 2', 'path' => '/css/exo2.css', 'family' => "'Exo 2'"],
        ]]);

        $response = $this->getJson('/api/v2/fonts');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Exo 2');
    });
});
