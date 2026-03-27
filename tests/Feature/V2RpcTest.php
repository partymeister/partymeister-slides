<?php

use Illuminate\Support\Facades\Event;
use Motor\Admin\Models\User;
use Partymeister\Slides\Events\PlaylistNextRequest;
use Partymeister\Slides\Events\PlaylistPreviousRequest;
use Partymeister\Slides\Events\PlaylistRequest;
use Partymeister\Slides\Events\PlaylistSeekRequest;
use Partymeister\Slides\Events\PlayNowRequest;
use Partymeister\Slides\Events\SiegmeisterRequest;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\PlaylistItem;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Models\Transition;
use Spatie\Permission\Models\Role;

pest()->group('V2', 'Rpc');

beforeEach(function () {
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $user = User::factory()->create(['email' => 'admin@motor-cms.com', 'name' => 'Admin']);
    $user->assignRole($role);

    $transition = Transition::create(['name' => 'Fade', 'client_type' => 'slidemeister-web', 'identifier' => 'fade', 'default_duration' => 500]);
    $slide = Slide::create(['name' => 'Welcome', 'slide_type' => 'default', 'definitions' => '{}']);
    $playlist = Playlist::create(['name' => 'Main Show', 'type' => 'video', 'is_competition' => false]);
    PlaylistItem::create([
        'playlist_id' => $playlist->id,
        'type' => 'image', 'slide_type' => 'default', 'slide_id' => $slide->id,
        'duration' => 5, 'transition_id' => $transition->id, 'transition_duration' => 500,
        'is_advanced_manually' => false, 'is_muted' => false, 'midi_note' => 0,
        'callback_hash' => '', 'callback_delay' => 0, 'sort_position' => 1, 'metadata' => '{}',
    ]);
    SlideClient::create([
        'name' => 'Main Screen', 'type' => 'slidemeister-web',
        'ip_address' => '10.10.10.1', 'port' => '80', 'sort_position' => 1,
        'configuration' => [], 'playlist_id' => $playlist->id,
    ]);

    Event::fake();
});

describe('V2 RPC Slide Client Communication', function () {
    it('can send playlist to client', function () {
        $client = SlideClient::first();
        $playlist = Playlist::first();

        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/playlist', [
            'slide_client_id' => $client->id,
            'playlist_id' => $playlist->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
        Event::assertDispatched(PlaylistRequest::class);
    });

    it('validates slide_client_id is required for playlist', function () {
        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/playlist', []);
        $response->assertStatus(422)
            ->assertJsonPath('meta.api_version', 'v2');
    });

    it('can play a slide now', function () {
        $client = SlideClient::first();
        $slide = Slide::first();

        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/playnow', [
            'slide_client_id' => $client->id,
            'type' => 'slide',
            'item' => $slide->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
        Event::assertDispatched(PlayNowRequest::class);
    });

    it('can seek to beginning', function () {
        $client = SlideClient::first();
        $playlist = Playlist::first();

        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/seek', [
            'slide_client_id' => $client->id,
            'playlist_id' => $playlist->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
        Event::assertDispatched(PlaylistSeekRequest::class);
    });

    it('can seek with continue flag', function () {
        $client = SlideClient::first();
        $playlist = Playlist::first();

        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/seek', [
            'slide_client_id' => $client->id,
            'playlist_id' => $playlist->id,
            'continue' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
        Event::assertDispatched(PlaylistSeekRequest::class);
    });

    it('can skip to next', function () {
        $client = SlideClient::first();

        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/skip', [
            'slide_client_id' => $client->id,
            'direction' => 'next',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
        Event::assertDispatched(PlaylistNextRequest::class);
    });

    it('can skip to previous', function () {
        $client = SlideClient::first();

        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/skip', [
            'slide_client_id' => $client->id,
            'direction' => 'previous',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
        Event::assertDispatched(PlaylistPreviousRequest::class);
    });

    it('can trigger siegmeister', function () {
        $client = SlideClient::first();

        $response = $this->asAdmin()->postJson('/api/v2/rpc/slide-clients/siegmeister', [
            'slide_client_id' => $client->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
        Event::assertDispatched(SiegmeisterRequest::class);
    });
});
