<?php

use Motor\Admin\Models\User;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\SlideClient;
use Spatie\Permission\Models\Role;

pest()->group('V2', 'SlideClient');

beforeEach(function () {
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $user = User::factory()->create(['email' => 'admin@motor-cms.com', 'name' => 'Admin']);
    $user->assignRole($role);

    $playlist = Playlist::create(['name' => 'Main Show', 'type' => 'video', 'is_competition' => false]);

    SlideClient::create([
        'name' => 'Main Screen',
        'type' => 'slidemeister-web',
        'ip_address' => '10.10.10.1',
        'port' => '80',
        'sort_position' => 1,
        'configuration' => [],
        'playlist_id' => $playlist->id,
    ]);
    SlideClient::create([
        'name' => 'Side Screen',
        'type' => 'slidemeister-web',
        'ip_address' => '10.10.10.2',
        'port' => '80',
        'sort_position' => 2,
        'configuration' => [],
    ]);
});

describe('V2 SlideClients API', function () {
    it('includes api_version v2 in response meta', function () {
        $response = $this->asAdmin()->getJson('/api/v2/slide-clients');
        $response->assertStatus(200)->assertJsonPath('meta.api_version', 'v2');
    });

    it('can get all slide clients', function () {
        assertV2CrudIndex('/api/v2/slide-clients', 2, ['id', 'name', 'type', 'ip_address', 'port']);
    });

    it('can get a specific slide client with websocket config', function () {
        $client = SlideClient::first();
        $response = $this->asAdmin()->getJson('/api/v2/slide-clients/'.$client->id);
        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJsonStructure(['data' => ['id', 'name', 'type', 'websocket', 'configuration']]);
    });

    it('includes playlist relationship when loaded', function () {
        $client = SlideClient::whereNotNull('playlist_id')->first();
        $response = $this->asAdmin()->getJson('/api/v2/slide-clients/'.$client->id);
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['playlist' => ['id', 'name']]]);
    });

    it('can create a slide client', function () {
        assertV2CrudCreate('/api/v2/slide-clients', [
            'name' => 'New Client',
            'type' => 'slidemeister-web',
            'ip_address' => '10.10.10.3',
            'port' => '80',
            'sort_position' => 3,
        ], SlideClient::class);
    });

    it('validates required fields on create', function () {
        $countBefore = SlideClient::count();
        $this->asAdmin()->postJson('/api/v2/slide-clients', [])->assertStatus(422);
        expect(SlideClient::count())->toBe($countBefore);
    });

    it('can update a slide client', function () {
        assertV2CrudUpdate(
            '/api/v2/slide-clients/'.SlideClient::first()->id,
            ['name' => 'Updated Screen'],
            'name',
            'Updated Screen'
        );
    });

    it('can delete a slide client with 204 No Content', function () {
        assertV2CrudDelete('/api/v2/slide-clients/'.SlideClient::latest('id')->first()->id, SlideClient::class);
    });
});
