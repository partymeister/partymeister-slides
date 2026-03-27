<?php

use Motor\Admin\Models\User;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\PlaylistItem;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\Transition;
use Spatie\Permission\Models\Role;

pest()->group('V2', 'Playlist');

beforeEach(function () {
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $user = User::factory()->create(['email' => 'admin@motor-cms.com', 'name' => 'Admin']);
    $user->assignRole($role);

    $transition = Transition::factory()->create(['name' => 'Fade', 'identifier' => 'fade', 'default_duration' => 500]);
    $slide = Slide::factory()->create(['name' => 'Welcome']);

    $playlist = Playlist::factory()->create(['name' => 'Main Show']);
    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'slide_id' => $slide->id,
        'transition_id' => $transition->id,
        'sort_position' => 1,
    ]);

    Playlist::factory()->create(['name' => 'Compo Playlist', 'is_competition' => true]);
});

describe('V2 Playlists API', function () {
    it('requires authentication', function () {
        assertV2RequiresAuth('/api/v2/playlists');
    });

    it('includes api_version v2 in response meta', function () {
        $response = $this->asAdmin()->getJson('/api/v2/playlists');
        $response->assertStatus(200)->assertJsonPath('meta.api_version', 'v2');
    });

    it('can get all playlists', function () {
        assertV2CrudIndex('/api/v2/playlists', 2, ['id', 'name', 'type', 'is_competition']);
    });

    it('can get a specific playlist with items', function () {
        $playlist = Playlist::where('name', 'Main Show')->first();
        $response = $this->asAdmin()->getJson('/api/v2/playlists/'.$playlist->id);
        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJsonStructure(['data' => ['id', 'name', 'items']]);
        expect($response->json('data.items'))->toHaveCount(1);
    });

    it('can get nested playlist items', function () {
        $playlist = Playlist::where('name', 'Main Show')->first();
        $response = $this->asAdmin()->getJson('/api/v2/playlists/'.$playlist->id.'/items');
        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJsonCount(1, 'data');
    });

    it('can create a playlist', function () {
        assertV2CrudCreate('/api/v2/playlists', [
            'name' => 'New Playlist',
            'type' => 'video',
            'is_competition' => false,
        ], Playlist::class);
    });

    it('validates required fields on create', function () {
        $countBefore = Playlist::count();
        $this->asAdmin()->postJson('/api/v2/playlists', [])->assertStatus(422);
        expect(Playlist::count())->toBe($countBefore);
    });

    it('can update a playlist', function () {
        assertV2CrudUpdate(
            '/api/v2/playlists/'.Playlist::first()->id,
            ['name' => 'Updated Show'],
            'name',
            'Updated Show'
        );
    });

    it('can delete a playlist with 204 No Content', function () {
        assertV2CrudDelete('/api/v2/playlists/'.Playlist::latest('id')->first()->id, Playlist::class);
    });
});
